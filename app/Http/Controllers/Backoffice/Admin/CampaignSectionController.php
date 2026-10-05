<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Actions\Campaigns\ReorderCampaignSections;
use App\Actions\Campaigns\ToggleCampaignSection;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignSection;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use App\Services\Campaigns\CampaignSectionRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignSectionController extends Controller
{
    public function __construct(
        private readonly CampaignSectionRegistry $registry,
        private readonly ReorderCampaignSections $reorderAction,
        private readonly ToggleCampaignSection $toggleAction,
        private readonly CampaignCache $cache,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('campaign_section_list');

        $query = CampaignSection::query()->with('campaign:id,title,slug');
        $this->applyFilters($query, $request);
        $sections = $query->ordered()->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.campaign_sections.partials.table', [
                    'sections' => $sections,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.campaign_sections.index', [
            'sections' => $sections,
            'campaigns' => Campaign::query()->orderBy('title')->get(['id', 'title']),
            'types' => $this->registry->labels(),
            'title' => 'Campaign Sections Management',
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('campaign_section_list');

        $query = CampaignSection::query()->with('campaign:id,title');
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q->where('heading', 'like', "%{$search}%")
                ->orWhere('section_key', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get()->map(fn (CampaignSection $section) => [
                'id' => $section->id,
                'text' => ($section->campaign?->title ?? 'Campaign') . ' — ' . $section->label(),
            ]),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('campaign_section_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.campaign_sections.partials.form', [
                'campaigns' => Campaign::query()->orderBy('title')->get(['id', 'title']),
                'types' => $this->registry->labels(),
                'selectedCampaignId' => $request->integer('campaign_id') ?: null,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('campaign_section_create');

        $validated = $this->validateSection($request);
        $payload = $this->decodePayload($validated['payload_json'] ?? null);
        $payload = $this->registry->validate($validated['section_key'], $payload);

        $section = CampaignSection::query()->create([
            'campaign_id' => $validated['campaign_id'],
            'section_key' => $validated['section_key'],
            'heading' => $validated['heading'] ?? null,
            'subheading' => $validated['subheading'] ?? null,
            'payload' => $payload,
            'is_enabled' => $validated['is_enabled'],
            'sort_order' => $validated['sort_order'],
        ]);

        $this->cache->forget($section->campaign);
        $this->auditLogger->log('campaign_section.created', $section, null, $section->toArray());

        return response()->json(['success' => true, 'message' => 'Campaign section created successfully.']);
    }

    public function show(Request $request, CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_view');
        abort_unless($request->ajax(), 404);

        $campaignSection->load('campaign:id,title,slug');

        return response()->json([
            'html' => view('backoffice.admin.campaign_sections.partials.show', [
                'section' => $campaignSection,
            ])->render(),
        ]);
    }

    public function edit(Request $request, CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.campaign_sections.partials.form', [
                'section' => $campaignSection,
                'campaigns' => Campaign::query()->orderBy('title')->get(['id', 'title']),
                'types' => $this->registry->labels(),
                'selectedCampaignId' => $campaignSection->campaign_id,
            ])->render(),
        ]);
    }

    public function update(Request $request, CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_update');

        $validated = $this->validateSection($request, $campaignSection);
        $payload = $this->registry->validate(
            $validated['section_key'],
            $this->decodePayload($validated['payload_json'] ?? null)
        );

        $old = $campaignSection->toArray();
        $oldCampaign = $campaignSection->campaign;

        $campaignSection->update([
            'campaign_id' => $validated['campaign_id'],
            'section_key' => $validated['section_key'],
            'heading' => $validated['heading'] ?? null,
            'subheading' => $validated['subheading'] ?? null,
            'payload' => $payload,
            'is_enabled' => $validated['is_enabled'],
            'sort_order' => $validated['sort_order'],
        ]);

        $this->cache->forget($oldCampaign);
        $this->cache->forget($campaignSection->fresh()->campaign);
        $this->auditLogger->log('campaign_section.updated', $campaignSection, $old, $campaignSection->fresh()->toArray());

        return response()->json(['success' => true, 'message' => 'Campaign section updated successfully.']);
    }

    public function destroy(CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_delete');
        $campaign = $campaignSection->campaign;
        $campaignSection->delete();
        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign_section.trashed', $campaignSection);

        return response()->json(['success' => true, 'message' => 'Campaign section moved to trash.']);
    }

    public function toggle(CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_toggle');
        $section = $this->toggleAction->handle($campaignSection);

        return response()->json([
            'success' => true,
            'message' => $section->is_enabled ? 'Campaign section enabled.' : 'Campaign section disabled.',
        ]);
    }

    public function duplicate(CampaignSection $campaignSection)
    {
        $this->ensurePermission('campaign_section_create');

        $copy = DB::transaction(function () use ($campaignSection): CampaignSection {
            $maxOrder = CampaignSection::withTrashed()->where('campaign_id', $campaignSection->campaign_id)->max('sort_order') ?? 0;
            $copy = $campaignSection->replicate();
            $copy->heading = $campaignSection->heading ? $campaignSection->heading . ' Copy' : null;
            $copy->sort_order = (int) $maxOrder + 10;
            $copy->save();
            return $copy;
        });

        $this->cache->forget($copy->campaign);
        $this->auditLogger->log('campaign_section.duplicated', $copy, ['source_section_id' => $campaignSection->id], $copy->toArray());

        return response()->json(['success' => true, 'message' => 'Campaign section duplicated.']);
    }

    public function reorder(Request $request)
    {
        $this->ensurePermission('campaign_section_reorder');

        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')->whereNull('deleted_at')],
            'order' => ['required', 'array', 'min:1'],
            'order.*.id' => ['required', 'integer', 'distinct'],
            'order.*.sort_order' => ['required', 'integer', 'min:-100000', 'max:100000'],
        ]);

        $campaign = Campaign::query()->findOrFail((int) $validated['campaign_id']);
        $this->reorderAction->handle($campaign, $validated['order']);

        return response()->json(['success' => true, 'message' => 'Campaign section order updated.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['enable', 'disable', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'enable', 'disable' => 'campaign_section_toggle',
            'delete' => 'campaign_section_delete',
            'restore' => 'campaign_section_restore',
            'force_delete' => 'campaign_section_force_delete',
        };
        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $message = match ($validated['action']) {
            'enable' => $this->bulkEnabled($ids, true),
            'disable' => $this->bulkEnabled($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('campaign_section_trash');

        $query = CampaignSection::onlyTrashed()->with('campaign:id,title,slug');
        $this->applyFilters($query, $request);
        $sections = $query->latest('deleted_at')->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.campaign_sections.partials.table', [
                    'sections' => $sections,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.campaign_sections.trash', [
            'sections' => $sections,
            'campaigns' => Campaign::withTrashed()->orderBy('title')->get(['id', 'title']),
            'types' => $this->registry->labels(),
            'title' => 'Campaign Section Trash',
        ]);
    }

    public function restore(int $campaignSection)
    {
        $this->ensurePermission('campaign_section_restore');
        $section = CampaignSection::onlyTrashed()->findOrFail($campaignSection);

        if (! Campaign::query()->whereKey($section->campaign_id)->exists()) {
            throw ValidationException::withMessages(['campaign_id' => 'Restore the parent campaign before restoring this section.']);
        }

        $section->restore();
        $this->cache->forget($section->campaign);
        $this->auditLogger->log('campaign_section.restored', $section);

        return response()->json(['success' => true, 'message' => 'Campaign section restored.']);
    }

    public function forceDelete(int $campaignSection)
    {
        $this->ensurePermission('campaign_section_force_delete');
        $section = CampaignSection::onlyTrashed()->findOrFail($campaignSection);
        $campaign = Campaign::withTrashed()->find($section->campaign_id);
        $id = $section->id;
        $section->forceDelete();
        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign_section.force_deleted', CampaignSection::class, ['section_id' => $id], null);

        return response()->json(['success' => true, 'message' => 'Campaign section permanently deleted.']);
    }

    private function validateSection(Request $request, ?CampaignSection $section = null): array
    {
        return $request->validate([
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')->whereNull('deleted_at')],
            'section_key' => ['required', Rule::in(array_keys(CampaignSection::TYPES))],
            'heading' => ['nullable', 'string', 'max:190'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'payload_json' => ['nullable', 'string'],
            'is_enabled' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:-100000', 'max:100000'],
        ]);
    }

    private function decodePayload(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValidationException::withMessages(['payload_json' => 'Payload must contain valid JSON.']);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages(['payload_json' => 'Payload must be a JSON object.']);
        }

        return $decoded;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q->where('heading', 'like', "%{$search}%")
                ->orWhere('subheading', 'like', "%{$search}%")
                ->orWhere('section_key', 'like', "%{$search}%"));
        }
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }
        if ($request->filled('section_key')) {
            $query->where('section_key', $request->string('section_key'));
        }
        if ($request->input('enabled') !== null && $request->input('enabled') !== '') {
            $query->where('is_enabled', $request->boolean('enabled'));
        }
    }

    private function bulkEnabled(array $ids, bool $enabled): string
    {
        $campaignIds = CampaignSection::query()->whereIn('id', $ids)->pluck('campaign_id')->unique();
        CampaignSection::query()->whereIn('id', $ids)->update(['is_enabled' => $enabled]);
        Campaign::query()->whereIn('id', $campaignIds)->get()->each(fn (Campaign $campaign) => $this->cache->forget($campaign));
        return $enabled ? 'Selected campaign sections enabled.' : 'Selected campaign sections disabled.';
    }

    private function bulkDelete(array $ids): string
    {
        CampaignSection::query()->whereIn('id', $ids)->get()->each(fn (CampaignSection $section) => $this->destroy($section));
        return 'Selected campaign sections moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        CampaignSection::onlyTrashed()->whereIn('id', $ids)->get()->each(function (CampaignSection $section): void {
            if (Campaign::query()->whereKey($section->campaign_id)->exists()) {
                $section->restore();
                $this->cache->forget($section->campaign);
            }
        });
        return 'Eligible campaign sections restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        CampaignSection::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        $this->cache->forget();
        return 'Selected campaign sections permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
