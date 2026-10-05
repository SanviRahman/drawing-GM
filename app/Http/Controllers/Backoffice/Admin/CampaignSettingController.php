<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Actions\Campaigns\SetDefaultCampaign;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignSetting;
use App\Models\Page;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignSettingController extends Controller
{
    public function __construct(
        private readonly SetDefaultCampaign $setDefaultCampaign,
        private readonly CampaignCache $cache,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function edit()
    {
        $this->ensurePermission('campaign_setting_view');

        $settings = CampaignSetting::singleton();
        $campaigns = Campaign::query()->orderBy('title')->get();
        $pages = Page::query()->orderBy('title')->get(['id', 'title', 'status', 'is_homepage']);

        return view('backoffice.admin.campaign_settings.edit', [
            'settings' => $settings,
            'campaigns' => $campaigns,
            'pages' => $pages,
            'fallbackModes' => CampaignSetting::FALLBACK_MODES,
            'title' => 'Campaign Settings',
        ]);
    }

    public function update(Request $request)
    {
        $this->ensurePermission('campaign_setting_update');

        $validated = $request->validate([
            'default_campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')->whereNull('deleted_at')],
            'fallback_mode' => ['required', Rule::in(array_keys(CampaignSetting::FALLBACK_MODES))],
            'fallback_page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->whereNull('deleted_at')],
        ]);

        if ($validated['fallback_mode'] === 'page' && empty($validated['fallback_page_id'])) {
            throw ValidationException::withMessages([
                'fallback_page_id' => 'Choose a fallback page when fallback mode is Specific Page.',
            ]);
        }

        $settings = CampaignSetting::singleton();
        $old = $settings->only(['default_campaign_id', 'fallback_mode', 'fallback_page_id']);

        $campaign = ! empty($validated['default_campaign_id'])
            ? Campaign::query()->findOrFail((int) $validated['default_campaign_id'])
            : null;

        $this->setDefaultCampaign->handle($campaign);

        DB::transaction(function () use ($settings, $validated): void {
            $settings->refresh()->update([
                'fallback_mode' => $validated['fallback_mode'],
                'fallback_page_id' => $validated['fallback_mode'] === 'page'
                    ? ($validated['fallback_page_id'] ?? null)
                    : null,
                'updated_by' => auth('admin')->id(),
            ]);
        });

        $this->cache->forget($campaign);
        $fresh = $settings->fresh();
        $this->auditLogger->log('campaign_settings.updated', $fresh, $old, $fresh->only([
            'default_campaign_id', 'fallback_mode', 'fallback_page_id',
        ]));

        return response()->json(['success' => true, 'message' => 'Campaign settings updated successfully.']);
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
