<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Actions\Campaigns\PublishCampaign;
use App\Actions\Campaigns\SetDefaultCampaign;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignSetting;
use App\Models\Media;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use App\Services\Campaigns\CampaignHeroResolver;
use App\Services\Campaigns\CampaignMediaService;
use App\Services\Campaigns\CampaignRouteGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignMediaService $mediaService,
        private readonly CampaignRouteGuard $routeGuard,
        private readonly CampaignHeroResolver $heroResolver,
        private readonly CampaignCache $cache,
        private readonly AuditLogger $auditLogger,
        private readonly PublishCampaign $publishCampaign,
        private readonly SetDefaultCampaign $setDefaultCampaign,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('campaign_list');

        $query = Campaign::query()->withCount('sections')->with(['createdBy:id,name']);
        $this->applyFilters($query, $request);
        $campaigns = $query->ordered()->paginate(15)->withQueryString();
        $settings = CampaignSetting::singleton();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.campaigns.partials.table', [
                    'campaigns' => $campaigns,
                    'defaultCampaignId' => $settings->default_campaign_id,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.campaigns.index', [
            'campaigns' => $campaigns,
            'statuses' => Campaign::STATUSES,
            'defaultCampaignId' => $settings->default_campaign_id,
            'title' => 'Campaigns Management',
            'breadcrumb' => [
                ['text' => 'Campaign Management', 'url' => null],
                ['text' => 'Campaigns', 'url' => route('admin.campaigns.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('campaign_list');

        $query = Campaign::query()->select('id', 'title', 'slug', 'status', 'published_at', 'starts_at', 'ends_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->boolean('eligible_only')) {
            $query->publicEligible();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('title')->limit(100)->get()->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'text' => $campaign->title,
            ]),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('campaign_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.campaigns.partials.form', [
                'statuses' => Campaign::STATUSES,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('campaign_create');

        $validated = $this->validateCampaign($request);
        $attributes = $this->attributes($validated, null);

        $campaign = DB::transaction(function () use ($attributes): Campaign {
            $campaign = Campaign::query()->create($attributes + [
                'created_by' => auth('admin')->id(),
                'updated_by' => auth('admin')->id(),
            ]);

            return $campaign;
        });

        try {
            $this->mediaService->syncFromRequest($request, $campaign);
        } catch (\Throwable $e) {
            $campaign->forceDelete();
            throw $e;
        }

        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.created', $campaign, null, $campaign->only([
            'title', 'slug', 'custom_route', 'status', 'published_at', 'starts_at', 'ends_at',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'data' => ['id' => $campaign->id],
        ]);
    }

    public function show(Request $request, Campaign $campaign)
    {
        $this->ensurePermission('campaign_view');
        abort_unless($request->ajax(), 404);

        $campaign->load(['createdBy:id,name', 'updatedBy:id,name', 'sections']);
        $defaultCampaignId = CampaignSetting::singleton()->default_campaign_id;

        return response()->json([
            'html' => view('backoffice.admin.campaigns.partials.show', [
                'campaign' => $campaign,
                'hero' => $this->heroResolver->resolve($campaign),
                'isDefault' => (int) $defaultCampaignId === (int) $campaign->id,
            ])->render(),
        ]);
    }

    public function edit(Request $request, Campaign $campaign)
    {
        $this->ensurePermission('campaign_update');
        abort_unless($request->ajax(), 404);

        $campaign->load('media');

        return response()->json([
            'html' => view('backoffice.admin.campaigns.partials.form', [
                'campaign' => $campaign,
                'statuses' => Campaign::STATUSES,
            ])->render(),
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->ensurePermission('campaign_update');

        $validated = $this->validateCampaign($request, $campaign);
        $old = $campaign->only([
            'title', 'slug', 'custom_route', 'summary', 'status', 'hero_config',
            'published_at', 'starts_at', 'ends_at',
        ]);

        $campaign->update($this->attributes($validated, $campaign) + [
            'updated_by' => auth('admin')->id(),
        ]);

        $this->mediaService->syncFromRequest($request, $campaign);
        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.updated', $campaign, $old, $campaign->fresh()->only(array_keys($old)));

        if (! $campaign->fresh()->isPublicEligible() && (int) CampaignSetting::singleton()->default_campaign_id === (int) $campaign->id) {
            $this->setDefaultCampaign->handle(null);
        }

        return response()->json(['success' => true, 'message' => 'Campaign updated successfully.']);
    }

    public function destroy(Campaign $campaign)
    {
        $this->ensurePermission('campaign_delete');

        if ((int) CampaignSetting::singleton()->default_campaign_id === (int) $campaign->id) {
            $this->setDefaultCampaign->handle(null);
        }

        $campaign->update(['status' => 'inactive', 'updated_by' => auth('admin')->id()]);
        $campaign->delete();
        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.trashed', $campaign, null, ['deleted_at' => now()->toDateTimeString()]);

        return response()->json(['success' => true, 'message' => 'Campaign moved to trash.']);
    }

    public function publish(Campaign $campaign)
    {
        $this->ensurePermission('campaign_publish');
        $this->publishCampaign->handle($campaign);

        return response()->json(['success' => true, 'message' => 'Campaign published.']);
    }

    public function unpublish(Campaign $campaign)
    {
        $this->ensurePermission('campaign_publish');
        $old = $campaign->only(['status']);
        $campaign->update(['status' => 'draft', 'updated_by' => auth('admin')->id()]);

        if ((int) CampaignSetting::singleton()->default_campaign_id === (int) $campaign->id) {
            $this->setDefaultCampaign->handle(null);
        }

        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.unpublished', $campaign, $old, ['status' => 'draft']);

        return response()->json(['success' => true, 'message' => 'Campaign moved back to Draft.']);
    }

    public function setDefault(Campaign $campaign)
    {
        $this->ensurePermission('campaign_set_default');
        $this->setDefaultCampaign->handle($campaign);

        return response()->json(['success' => true, 'message' => 'Default campaign updated.']);
    }

    public function preview(Request $request, Campaign $campaign)
    {
        $this->ensurePermission('campaign_preview');
        abort_unless($request->ajax(), 404);

        $campaign->load(['sections' => fn ($q) => $q->ordered()]);

        return response()->json([
            'html' => view('backoffice.admin.campaigns.partials.preview', [
                'campaign' => $campaign,
                'hero' => $this->heroResolver->resolve($campaign),
            ])->render(),
        ]);
    }

    public function duplicate(Campaign $campaign)
    {
        $this->ensurePermission('campaign_create');

        $copy = DB::transaction(function () use ($campaign): Campaign {
            $copy = $campaign->replicate([
                'slug', 'custom_route', 'status', 'published_at', 'starts_at', 'ends_at', 'created_by', 'updated_by',
            ]);
            $copy->title = Str::limit($campaign->title . ' Copy', 190, '');
            $copy->slug = $this->uniqueSlug($campaign->slug . '-copy');
            $copy->custom_route = null;
            $copy->status = 'draft';
            $copy->published_at = null;
            $copy->starts_at = null;
            $copy->ends_at = null;
            $copy->created_by = auth('admin')->id();
            $copy->updated_by = auth('admin')->id();
            $copy->save();

            foreach ($campaign->sections()->get() as $section) {
                $clone = $section->replicate(['campaign_id']);
                $clone->campaign_id = $copy->id;
                $clone->save();
            }

            return $copy;
        });

        $this->mediaService->duplicate($campaign, $copy);
        $this->auditLogger->log('campaign.duplicated', $copy, ['source_campaign_id' => $campaign->id], ['campaign_id' => $copy->id]);

        return response()->json(['success' => true, 'message' => 'Campaign duplicated as Draft.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'inactive', 'archive', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'publish' => 'campaign_publish',
            'inactive', 'archive' => 'campaign_update',
            'delete' => 'campaign_delete',
            'restore' => 'campaign_restore',
            'force_delete' => 'campaign_force_delete',
        };
        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $message = match ($validated['action']) {
            'publish' => $this->bulkPublish($ids),
            'inactive' => $this->bulkStatus($ids, 'inactive'),
            'archive' => $this->bulkStatus($ids, 'archived'),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('campaign_trash');

        $query = Campaign::onlyTrashed()->withCount('sections');
        $this->applyFilters($query, $request);
        $campaigns = $query->latest('deleted_at')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.campaigns.partials.table', [
                    'campaigns' => $campaigns,
                    'defaultCampaignId' => CampaignSetting::singleton()->default_campaign_id,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.campaigns.trash', [
            'campaigns' => $campaigns,
            'statuses' => Campaign::STATUSES,
            'title' => 'Campaign Trash',
        ]);
    }

    public function restore(int $campaign)
    {
        $this->ensurePermission('campaign_restore');
        $model = Campaign::onlyTrashed()->findOrFail($campaign);
        $model->restore();
        $this->cache->forget($model);
        $this->auditLogger->log('campaign.restored', $model);

        return response()->json(['success' => true, 'message' => 'Campaign restored as non-default.']);
    }

    public function forceDelete(int $campaign)
    {
        $this->ensurePermission('campaign_force_delete');
        $model = Campaign::onlyTrashed()->findOrFail($campaign);

        if ((int) CampaignSetting::singleton()->default_campaign_id === (int) $model->id) {
            $this->setDefaultCampaign->handle(null);
        }

        $this->mediaService->purgeAll($model);
        $id = $model->id;
        $model->forceDelete();
        $this->cache->forget();
        $this->auditLogger->log('campaign.force_deleted', Campaign::class, ['campaign_id' => $id], null);

        return response()->json(['success' => true, 'message' => 'Campaign permanently deleted.']);
    }

    private function validateCampaign(Request $request, ?Campaign $campaign = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'custom_route' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(Campaign::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'hero_heading' => ['nullable', 'string', 'max:190'],
            'hero_subheading' => ['nullable', 'string', 'max:500'],
            'hero_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'hero_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'hero_cta_label' => ['nullable', 'string', 'max:120'],
            'hero_cta_url' => ['nullable', 'string', 'max:2048'],
            'hero_embed_provider' => ['nullable', Rule::in(['youtube', 'vimeo'])],
            'hero_embed_url' => ['nullable', 'url', 'max:2048'],
            'video_autoplay' => ['required', 'boolean'],
            'video_muted' => ['required', 'boolean'],
            'video_controls' => ['required', 'boolean'],
            'video_loop' => ['required', 'boolean'],
            'hero_images' => ['nullable', 'array', 'max:12'],
            'hero_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:8192'],
            'hero_images_media_ids' => ['nullable', 'string'],
            'hero_images_remove_ids' => ['nullable', 'array'],
            'hero_images_remove_ids.*' => ['integer', 'distinct'],
            'hero_images_clear' => ['nullable', 'boolean'],
            'hero_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:102400'],
            'hero_video_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableMediaRule('video')],
            'hero_video_remove' => ['nullable', 'boolean'],
            'hero_video_poster' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:8192'],
            'hero_video_poster_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableMediaRule('image')],
            'hero_video_poster_remove' => ['nullable', 'boolean'],
            'social_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:8192'],
            'social_image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableMediaRule('image')],
            'social_image_remove' => ['nullable', 'boolean'],
        ]);

        if ($validated['status'] === 'published' && ! auth('admin')->user()?->can('campaign_publish')) {
            abort(403);
        }

        $slug = trim((string) ($validated['slug'] ?? ''));
        $slug = $slug !== '' ? Str::slug($slug) : Str::slug((string) $validated['title']);
        $slug = $slug !== '' ? $slug : 'campaign';

        $slugConflict = Campaign::withTrashed()
            ->where('slug', $slug)
            ->when($campaign, fn ($q) => $q->where('id', '!=', $campaign->id))
            ->exists();

        if ($slugConflict) {
            if (($validated['slug'] ?? '') !== '') {
                throw ValidationException::withMessages(['slug' => 'This slug is already used, including by a campaign in Trash.']);
            }
            $slug = $this->uniqueSlug($slug, $campaign?->id);
        }

        $validated['slug'] = $slug;
        $validated['custom_route'] = $this->routeGuard->validate($validated['custom_route'] ?? null);

        if ($validated['custom_route']) {
            $routeConflict = Campaign::withTrashed()
                ->where('custom_route', $validated['custom_route'])
                ->when($campaign, fn ($q) => $q->where('id', '!=', $campaign->id))
                ->exists();
            if ($routeConflict) {
                throw ValidationException::withMessages(['custom_route' => 'This custom route is already used, including by a campaign in Trash.']);
            }
        }

        if (! empty($validated['hero_embed_provider']) xor ! empty($validated['hero_embed_url'])) {
            throw ValidationException::withMessages([
                'hero_embed_url' => 'Embed provider and embed URL must be provided together.',
            ]);
        }

        return $validated;
    }

    private function attributes(array $validated, ?Campaign $campaign): array
    {
        $autoplay = (bool) $validated['video_autoplay'];
        $muted = $autoplay ? true : (bool) $validated['video_muted'];
        $controls = $autoplay ? (bool) $validated['video_controls'] : true;

        return [
            'title' => trim((string) $validated['title']),
            'slug' => $validated['slug'],
            'custom_route' => $validated['custom_route'],
            'summary' => $this->sanitizeRichText($validated['summary'] ?? null),
            'status' => $validated['status'],
            'hero_config' => [
                'heading' => $validated['hero_heading'] ?? null,
                'subheading' => $validated['hero_subheading'] ?? null,
                'rating' => isset($validated['hero_rating']) ? (float) $validated['hero_rating'] : null,
                'overlay_opacity' => isset($validated['hero_overlay_opacity']) ? (int) $validated['hero_overlay_opacity'] : 35,
                'cta' => [
                    'label' => $validated['hero_cta_label'] ?? null,
                    'url' => $validated['hero_cta_url'] ?? null,
                ],
                'embed' => [
                    'provider' => $validated['hero_embed_provider'] ?? null,
                    'url' => $validated['hero_embed_url'] ?? null,
                ],
                'video' => [
                    'autoplay' => $autoplay,
                    'muted' => $muted,
                    'controls' => $controls,
                    'loop' => (bool) $validated['video_loop'],
                ],
            ],
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? $campaign?->published_at ?? now())
                : ($validated['published_at'] ?? null),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('custom_route', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->input('default') === 'yes') {
            $query->whereKey(CampaignSetting::singleton()->default_campaign_id ?? 0);
        }

        if ($request->input('schedule') === 'active') {
            $query->publicEligible();
        }
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $candidate = $base;
        $counter = 2;
        while (Campaign::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = Str::limit($base, 175, '') . '-' . $counter++;
        }
        return $candidate;
    }

    private function reusableMediaRule(string $type): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($type): void {
            if ($value === null || $value === '') {
                return;
            }
            $media = Media::query()->find((int) $value);
            if (! $media || ! $media->isPickerSafe()) {
                $fail('The selected media is unavailable or cannot be reused.');
                return;
            }
            if ($type === 'image' && ! $media->isImage()) {
                $fail('The selected media must be an image.');
            }
            if ($type === 'video' && ! $media->isVideo()) {
                $fail('The selected media must be a video.');
            }
        };
    }

    private function sanitizeRichText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strip_tags($value, '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote>');
    }

    private function bulkPublish(array $ids): string
    {
        Campaign::query()->whereIn('id', $ids)->get()->each(fn (Campaign $campaign) => $this->publishCampaign->handle($campaign));
        return 'Selected campaigns published.';
    }

    private function bulkStatus(array $ids, string $status): string
    {
        Campaign::query()->whereIn('id', $ids)->get()->each(function (Campaign $campaign) use ($status): void {
            $campaign->update(['status' => $status, 'updated_by' => auth('admin')->id()]);
            if ((int) CampaignSetting::singleton()->default_campaign_id === (int) $campaign->id) {
                $this->setDefaultCampaign->handle(null);
            }
            $this->cache->forget($campaign);
        });
        return "Selected campaigns marked {$status}.";
    }

    private function bulkDelete(array $ids): string
    {
        Campaign::query()->whereIn('id', $ids)->get()->each(fn (Campaign $campaign) => $this->destroy($campaign));
        return 'Selected campaigns moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Campaign::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Campaign $campaign): void {
            $campaign->restore();
            $this->cache->forget($campaign);
        });
        return 'Selected campaigns restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Campaign::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Campaign $campaign): void {
            $this->mediaService->purgeAll($campaign);
            $campaign->forceDelete();
        });
        $this->cache->forget();
        return 'Selected campaigns permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
