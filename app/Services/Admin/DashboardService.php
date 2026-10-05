<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\TrackingProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardService
{
    public function build(Admin $admin): array
    {
        return [
            'cards' => $this->cards($admin),
            'leadOverview' => $this->leadOverview($admin),
            'recentLeads' => $this->recentLeads($admin),
            'contentOverview' => $this->contentOverview($admin),
            'mediaHealth' => $this->mediaHealth($admin),
            'trackingHealth' => $this->trackingHealth($admin),
            'systemSnapshot' => $this->systemSnapshot($admin),
            'recentAuditLogs' => $this->recentAuditLogs($admin),
            'quickActions' => $this->quickActions($admin),
        ];
    }

    private function cards(Admin $admin): array
    {
        $cards = [];

        if ($admin->can('lead_list') && Schema::hasTable('leads')) {
            $cards[] = [
                'label' => 'New Leads Today',
                'value' => $this->activeQuery('leads')->whereDate('created_at', today())->count(),
                'hint' => 'New enquiries received today',
                'icon' => 'fas fa-user-plus',
                'class' => 'primary',
                'route' => 'admin.leads.index',
            ];
        }

        if ($admin->can('lead_list') && Schema::hasTable('leads')) {
            $cards[] = [
                'label' => 'Open Lead Pipeline',
                'value' => $this->activeQuery('leads')
                    ->whereIn('status', ['new', 'contacted', 'qualified', 'quoted'])
                    ->count(),
                'hint' => 'Leads still requiring action',
                'icon' => 'fas fa-filter',
                'class' => 'warning',
                'route' => 'admin.leads.index',
            ];
        }

        if ($this->canSeeAnyContent($admin)) {
            $published = 0;

            foreach ($this->publishableModules($admin) as $module) {
                $published += $this->publishedCount($module['table'], $module['campaign'] ?? false);
            }

            $cards[] = [
                'label' => 'Published Content',
                'value' => $published,
                'hint' => 'Live pages, services, locations, posts & campaigns you can manage',
                'icon' => 'fas fa-globe-asia',
                'class' => 'success',
                'route' => $this->firstAllowedRoute($admin, [
                    ['page_list', 'admin.pages.index'],
                    ['service_list', 'admin.services.index'],
                    ['blog_post_list', 'admin.posts.index'],
                    ['campaign_list', 'admin.campaigns.index'],
                ]),
            ];
        }

        if ($admin->can('media_list') && Schema::hasTable('media')) {
            $cards[] = [
                'label' => 'Media Assets',
                'value' => $this->activeQuery('media')->count(),
                'hint' => 'Reusable assets in the Media Library',
                'icon' => 'fas fa-photo-video',
                'class' => 'info',
                'route' => 'admin.media.index',
            ];
        }

        if ($admin->can('campaign_list') && Schema::hasTable('campaigns') && count($cards) < 4) {
            $cards[] = [
                'label' => 'Live Campaigns',
                'value' => Campaign::query()->publicEligible()->count(),
                'hint' => 'Published campaigns currently eligible for public display',
                'icon' => 'fas fa-bullhorn',
                'class' => 'danger',
                'route' => 'admin.campaigns.index',
            ];
        }

        if ($admin->can('admin_list') && Schema::hasTable('admins') && count($cards) < 4) {
            $cards[] = [
                'label' => 'Active Admins',
                'value' => $this->activeQuery('admins')->where('status', true)->count(),
                'hint' => 'Active backoffice accounts',
                'icon' => 'fas fa-user-shield',
                'class' => 'secondary',
                'route' => 'admin.admins.index',
            ];
        }

        return array_slice($cards, 0, 4);
    }

    private function leadOverview(Admin $admin): ?array
    {
        if (! $admin->can('lead_list') || ! Schema::hasTable('leads')) {
            return null;
        }

        $statusCounts = $this->activeQuery('leads')
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $statusCounts->sum();
        $pipeline = [];

        foreach (Lead::STATUSES as $status => $label) {
            $count = (int) ($statusCounts[$status] ?? 0);
            $pipeline[] = [
                'status' => $status,
                'label' => $label,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                'class' => $this->leadStatusClass($status),
            ];
        }

        $sourceCounts = Lead::query()
            ->latest('id')
            ->limit(500)
            ->get(['utm', 'source_page_url'])
            ->map(function (Lead $lead): string {
                $utm = $lead->utm ?? [];
                $source = trim((string) (data_get($utm, 'utm_source') ?? data_get($utm, 'source') ?? ''));

                if ($source !== '') {
                    return Str::title(str_replace(['-', '_'], ' ', $source));
                }

                $url = trim((string) ($lead->source_page_url ?? ''));

                if ($url !== '') {
                    $host = parse_url($url, PHP_URL_HOST);

                    if (is_string($host) && $host !== '') {
                        return $host;
                    }

                    return 'Website';
                }

                return 'Direct / Unknown';
            })
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->map(fn (int $count, string $source): array => [
                'source' => $source,
                'count' => $count,
            ])
            ->values()
            ->all();

        return [
            'pipeline' => $pipeline,
            'total' => $total,
            'today' => $this->activeQuery('leads')->whereDate('created_at', today())->count(),
            'last7' => $this->activeQuery('leads')->where('created_at', '>=', now()->subDays(7))->count(),
            'last30' => $this->activeQuery('leads')->where('created_at', '>=', now()->subDays(30))->count(),
            'unassigned' => $this->activeQuery('leads')->whereNull('assigned_to')->count(),
            'won30' => $this->activeQuery('leads')
                ->where('status', 'won')
                ->where('updated_at', '>=', now()->subDays(30))
                ->count(),
            'sources' => $sourceCounts,
        ];
    }

    private function recentLeads(Admin $admin): Collection
    {
        if (! $admin->can('lead_list') || ! Schema::hasTable('leads')) {
            return collect();
        }

        return Lead::query()
            ->with([
                'location:id,name',
                'assignee:id,name',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(7)
            ->get();
    }

    private function contentOverview(Admin $admin): array
    {
        $items = [];

        foreach ($this->publishableModules($admin) as $module) {
            $items[] = [
                'label' => $module['label'],
                'icon' => $module['icon'],
                'route' => $module['route'],
                'published' => $this->publishedCount($module['table'], $module['campaign'] ?? false),
                'draft' => $this->activeQuery($module['table'])->where('status', 'draft')->count(),
                'total' => $this->activeQuery($module['table'])->count(),
            ];
        }

        return $items;
    }

    private function mediaHealth(Admin $admin): ?array
    {
        if (! $admin->can('media_list') && ! $admin->can('video_list')) {
            return null;
        }

        $failedVideos = 0;
        $processingVideos = 0;

        if (Schema::hasTable('videos') && $admin->can('video_list')) {
            $failedVideos = $this->activeQuery('videos')->where('processing_status', 'failed')->count();
            $processingVideos = $this->activeQuery('videos')->whereIn('processing_status', ['pending', 'processing'])->count();
        }

        return [
            'assets' => Schema::hasTable('media') && $admin->can('media_list')
                ? $this->activeQuery('media')->count()
                : null,
            'failed' => $failedVideos,
            'processing' => $processingVideos,
        ];
    }

    private function trackingHealth(Admin $admin): ?array
    {
        if (! $admin->can('tracking_provider_list') || ! Schema::hasTable('tracking_providers')) {
            return null;
        }

        $providers = TrackingProvider::query()
            ->withCount(['eventRules' => fn ($query) => $query->where('is_enabled', true)])
            ->ordered()
            ->get()
            ->map(function (TrackingProvider $provider): array {
                try {
                    $configured = match ($provider->provider) {
                        'meta_pixel' => filled($provider->public_identifier)
                            || collect($provider->metaPixels())->contains(fn ($pixel) => filled($pixel['pixel_id'] ?? null)),
                        'meta_capi' => filled($provider->public_identifier) && filled($provider->secret),
                        default => filled($provider->public_identifier),
                    };
                } catch (\Throwable) {
                    // A malformed/encrypted provider configuration must never break the dashboard.
                    $configured = false;
                }

                return [
                    'label' => $provider->label(),
                    'enabled' => (bool) $provider->is_enabled,
                    'test_mode' => (bool) $provider->test_mode,
                    'configured' => $configured,
                    'rules' => (int) $provider->event_rules_count,
                ];
            });

        return [
            'providers' => $providers,
            'enabled' => $providers->where('enabled', true)->count(),
            'attention' => $providers->filter(fn (array $provider) => $provider['enabled'] && ! $provider['configured'])->count(),
        ];
    }

    private function systemSnapshot(Admin $admin): array
    {
        $items = [];

        $definitions = [
            ['permission' => 'contact_channel_list', 'table' => 'contact_channels', 'label' => 'Active Contact Channels', 'icon' => 'fab fa-whatsapp', 'where' => ['is_active' => true], 'route' => 'admin.contact_channels.index'],
            ['permission' => 'pricing_package_list', 'table' => 'pricing_packages', 'label' => 'Active Pricing Packages', 'icon' => 'fas fa-tags', 'where' => ['is_active' => true], 'route' => 'admin.pricing_packages.index'],
            ['permission' => 'seo_meta_list', 'table' => 'seo_metas', 'label' => 'SEO Metadata Records', 'icon' => 'fas fa-search', 'where' => [], 'route' => 'admin.seo.index'],
            ['permission' => 'redirect_list', 'table' => 'redirects', 'label' => 'Active Redirects', 'icon' => 'fas fa-random', 'where' => ['is_active' => true], 'route' => 'admin.redirects.index'],
            ['permission' => 'campaign_list', 'table' => 'campaigns', 'label' => 'Campaigns', 'icon' => 'fas fa-bullhorn', 'where' => [], 'route' => 'admin.campaigns.index'],
            ['permission' => 'admin_list', 'table' => 'admins', 'label' => 'Active Admins', 'icon' => 'fas fa-user-shield', 'where' => ['status' => true], 'route' => 'admin.admins.index'],
        ];

        foreach ($definitions as $definition) {
            if (! $admin->can($definition['permission']) || ! Schema::hasTable($definition['table'])) {
                continue;
            }

            $query = $this->activeQuery($definition['table']);

            foreach ($definition['where'] as $column => $value) {
                $query->where($column, $value);
            }

            $items[] = [
                'label' => $definition['label'],
                'value' => $query->count(),
                'icon' => $definition['icon'],
                'route' => $definition['route'],
            ];
        }

        if ($admin->can('system_tools_manage') && Schema::hasTable('failed_jobs')) {
            $items[] = [
                'label' => 'Failed Jobs',
                'value' => DB::table('failed_jobs')->count(),
                'icon' => 'fas fa-exclamation-triangle',
                'route' => 'command.index',
            ];
        }

        return $items;
    }

    private function recentAuditLogs(Admin $admin): Collection
    {
        if (! $admin->can('audit_log_list') || ! Schema::hasTable('audit_logs')) {
            return collect();
        }

        return AuditLog::query()
            ->with('actor')
            ->ordered()
            ->limit(7)
            ->get();
    }

    private function quickActions(Admin $admin): array
    {
        $definitions = [
            ['permission' => 'lead_list', 'route' => 'admin.leads.index', 'label' => 'Manage Leads', 'icon' => 'fas fa-user-tag', 'class' => 'primary'],
            ['permission' => 'page_list', 'route' => 'admin.pages.index', 'label' => 'Manage Pages', 'icon' => 'fas fa-layer-group', 'class' => 'info'],
            ['permission' => 'service_list', 'route' => 'admin.services.index', 'label' => 'Services', 'icon' => 'fas fa-tools', 'class' => 'success'],
            ['permission' => 'media_list', 'route' => 'admin.media.index', 'label' => 'Media Library', 'icon' => 'fas fa-photo-video', 'class' => 'secondary'],
            ['permission' => 'blog_post_list', 'route' => 'admin.posts.index', 'label' => 'Blog Posts', 'icon' => 'fas fa-newspaper', 'class' => 'warning'],
            ['permission' => 'seo_meta_list', 'route' => 'admin.seo.index', 'label' => 'SEO Metadata', 'icon' => 'fas fa-search', 'class' => 'dark'],
            ['permission' => 'campaign_list', 'route' => 'admin.campaigns.index', 'label' => 'Campaigns', 'icon' => 'fas fa-bullhorn', 'class' => 'danger'],
            ['permission' => 'audit_log_list', 'route' => 'admin.audit_logs.index', 'label' => 'Audit Logs', 'icon' => 'fas fa-history', 'class' => 'secondary'],
        ];

        return collect($definitions)
            ->filter(fn (array $item) => $admin->can($item['permission']))
            ->values()
            ->all();
    }

    private function publishableModules(Admin $admin): array
    {
        $modules = [
            ['permission' => 'page_list', 'table' => 'pages', 'label' => 'Pages', 'route' => 'admin.pages.index', 'icon' => 'fas fa-file-alt'],
            ['permission' => 'service_list', 'table' => 'services', 'label' => 'Services', 'route' => 'admin.services.index', 'icon' => 'fas fa-tools'],
            ['permission' => 'location_list', 'table' => 'locations', 'label' => 'Locations', 'route' => 'admin.locations.index', 'icon' => 'fas fa-map-marker-alt'],
            ['permission' => 'blog_post_list', 'table' => 'posts', 'label' => 'Posts', 'route' => 'admin.posts.index', 'icon' => 'fas fa-newspaper'],
            ['permission' => 'campaign_list', 'table' => 'campaigns', 'label' => 'Campaigns', 'route' => 'admin.campaigns.index', 'icon' => 'fas fa-bullhorn', 'campaign' => true],
        ];

        return collect($modules)
            ->filter(fn (array $module) => $admin->can($module['permission']) && Schema::hasTable($module['table']))
            ->values()
            ->all();
    }

    private function publishedCount(string $table, bool $campaign = false): int
    {
        $query = $this->activeQuery($table)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($campaign) {
            $query
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
        }

        return $query->count();
    }

    private function activeQuery(string $table)
    {
        $query = DB::table($table);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function canSeeAnyContent(Admin $admin): bool
    {
        return collect(['page_list', 'service_list', 'location_list', 'blog_post_list', 'campaign_list'])
            ->contains(fn (string $permission) => $admin->can($permission));
    }

    private function firstAllowedRoute(Admin $admin, array $items): ?string
    {
        foreach ($items as [$permission, $route]) {
            if ($admin->can($permission)) {
                return $route;
            }
        }

        return null;
    }

    private function leadStatusClass(string $status): string
    {
        return match ($status) {
            'new' => 'primary',
            'contacted' => 'info',
            'qualified', 'quoted' => 'warning',
            'won' => 'success',
            'lost', 'spam' => 'danger',
            default => 'secondary',
        };
    }
}
