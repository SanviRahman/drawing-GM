<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Location;
use App\Models\Page;
use App\Models\SectionDefinition;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProjectDefaultSeeder extends Seeder
{
    /** @var array<string, array<int, string>> */
    private array $columnCache = [];

    public function run(): void
    {
        // Roles, permissions and the initial Admin remain owned by the existing seeder.
        $this->call(RolePermissionSeeder::class);

        DB::transaction(function (): void {
            $adminId = $this->value('admins', ['email' => 'admin@gmail.com'], 'id');

            $this->seedSiteSettings();
            $this->seedMenus();
            $sectionDefinitions = $this->seedSectionDefinitions();

            $homeId = $this->seedHomePage($adminId, $sectionDefinitions['hero'] ?? null);
            $serviceId = $this->seedService();
            $locationId = $this->seedLocation();

            $this->seedServiceLocation($serviceId, $locationId);
            [$pricingPackageId, $pricingAddonId] = $this->seedPricing($serviceId, $locationId);

            $galleryId = $this->seedGallery($serviceId);
            $videoId = $this->seedVideo($serviceId);
            $testimonialId = $this->seedTestimonial($serviceId);
            $faqId = $this->seedFaq($serviceId);

            [$categoryId, $postId] = $this->seedBlog($adminId);
            [$contactChannelId] = $this->seedContact($homeId);

            $leadFormFieldId = $this->seedLeadFormField();

            if (app()->environment(['local', 'testing'])) {
                $this->seedDemoOperationalData(
                    adminId: $adminId,
                    locationId: $locationId,
                    serviceId: $serviceId,
                    leadFormFieldId: $leadFormFieldId,
                );
            }

            $this->seedSeo($homeId);
            $this->seedRedirect();
            $this->seedTracking();
            $this->seedCampaign($adminId, $homeId);

            // Runtime/binary-backed tables are intentionally not populated with fake data:
            // media, section_media, gallery_items, failed_jobs/jobs/sessions/cache.
            // Their owning records are seeded above so the Admin UI is immediately usable.
            unset(
                $pricingPackageId,
                $pricingAddonId,
                $galleryId,
                $videoId,
                $testimonialId,
                $faqId,
                $categoryId,
                $postId,
                $contactChannelId,
            );
        });
    }

    private function seedSiteSettings(): void
    {
        $settings = [
            ['branding', 'site.name', 'Painting Services', 'string', true],
            ['localization', 'site.timezone', 'Asia/Singapore', 'string', true],
            ['localization', 'site.currency', 'SGD', 'string', true],
            ['header', 'header.sticky', '1', 'boolean', true],
            ['footer', 'footer.description', 'Professional painting and home-service solutions.', 'string', true],
            ['widget', 'widget.position', 'bottom-right', 'string', true],
            ['widget', 'widget.scroll_threshold', '300', 'integer', true],
            ['registration', 'registration.enabled', '0', 'boolean', false],
            ['consent', 'consent.marketing_default', '0', 'boolean', true],
        ];

        foreach ($settings as [$group, $key, $value, $type, $public]) {
            $this->ensureRow('site_settings', ['setting_key' => $key], [
                'group_name' => $group,
                'setting_value' => $value,
                'value_type' => $type,
                'is_public' => $public,
            ]);
        }
    }

    private function seedMenus(): void
    {
        $menus = [
            'header-primary' => 'Header Primary',
            'header-top' => 'Header Top',
            'footer-services' => 'Footer Services',
            'footer-company' => 'Footer Company',
            'footer-legal' => 'Footer Legal',
        ];

        foreach ($menus as $location => $name) {
            $menuId = $this->ensureRow('menus', ['location' => $location], [
                'name' => $name,
                'is_active' => true,
            ]);

            if (! $menuId || ! Schema::hasTable('menu_items')) {
                continue;
            }

            if ($location === 'header-primary') {
                $this->ensureRow('menu_items', [
                    'menu_id' => $menuId,
                    'label' => 'Home',
                ], [
                    'parent_id' => null,
                    'link_type' => 'url',
                    'linkable_type' => null,
                    'linkable_id' => null,
                    'url' => '/',
                    'icon' => null,
                    'target' => '_self',
                    'css_class' => null,
                    'sort_order' => 0,
                    'is_active' => true,
                ]);
            }
        }
    }

    /** @return array<string, int> */
    private function seedSectionDefinitions(): array
    {
        $ids = [];

        foreach (SectionDefinition::ALLOWED_KEYS as $key => $label) {
            $id = $this->ensureRow('section_definitions', ['key' => $key], [
                'name' => $label,
                'description' => "Default {$label} section definition.",
                'schema_json' => $this->json([]),
                'is_active' => true,
            ]);

            if ($id) {
                $ids[$key] = $id;
            }
        }

        return $ids;
    }

    private function seedHomePage(?int $adminId, ?int $heroDefinitionId): ?int
    {
        if (! Schema::hasTable('pages')) {
            return null;
        }

        $existingHomepageId = DB::table('pages')
            ->whereNull('deleted_at')
            ->where('is_homepage', true)
            ->value('id');

        if ($existingHomepageId) {
            $homeId = (int) $existingHomepageId;
        } else {
            $homeId = $this->ensureRow('pages', ['slug' => 'home'], [
                'title' => 'Home',
                'excerpt' => 'Main website landing page.',
                'template' => 'default',
                'hero_config' => $this->json([
                    'heading' => 'Professional Painting Services',
                    'subheading' => 'Reliable painting solutions for homes and businesses.',
                    'overlay' => 35,
                    'cta' => ['label' => 'Get a Quote', 'url' => '#quote'],
                ]),
                'status' => 'published',
                'published_at' => now(),
                'is_homepage' => true,
                'show_header' => true,
                'show_footer' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            if ($homeId) {
                DB::table('pages')
                    ->where('id', $homeId)
                    ->whereNull('deleted_at')
                    ->update(['is_homepage' => true, 'updated_at' => now()]);
            }
        }

        if ($homeId && $heroDefinitionId) {
            $this->ensureRow('page_sections', [
                'page_id' => $homeId,
                'section_definition_id' => $heroDefinitionId,
                'sort_order' => 0,
            ], [
                'heading' => 'Professional Painting Services',
                'subheading' => 'Reliable painting solutions for homes and businesses.',
                'payload' => $this->json([]),
                'theme' => 'default',
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
            ]);
        }

        return $homeId;
    }

    private function seedService(): ?int
    {
        $serviceId = $this->ensureRow('services', ['slug' => 'hdb-painting'], [
            'name' => 'HDB Painting',
            'summary' => 'Default service record. Review and publish when ready.',
            'content' => '<p>Configure your HDB painting service content here.</p>',
            'icon' => 'paint-roller',
            'hero_config' => $this->json([]),
            'status' => 'draft',
            'is_featured' => true,
            'sort_order' => 0,
            'published_at' => null,
        ]);

        if ($serviceId) {
            $this->ensureRow('service_features', [
                'service_id' => $serviceId,
                'title' => 'Professional workmanship',
            ], [
                'description' => 'Default feature. Edit this text before publishing the service.',
                'icon' => 'check-circle',
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }

        return $serviceId;
    }

    private function seedLocation(): ?int
    {
        return $this->ensureRow('locations', ['slug' => 'singapore'], [
            'name' => 'Singapore',
            'region' => 'Singapore',
            'postal_codes' => $this->json([]),
            'summary' => 'Default service area. Review before publishing.',
            'content' => '<p>Configure location-specific content here.</p>',
            'hero_config' => $this->json([]),
            'status' => 'draft',
            'sort_order' => 0,
            'published_at' => null,
        ]);
    }

    private function seedServiceLocation(?int $serviceId, ?int $locationId): void
    {
        if (! $serviceId || ! $locationId || ! Schema::hasTable('service_location')) {
            return;
        }

        $this->ensurePivot('service_location', [
            'service_id' => $serviceId,
            'location_id' => $locationId,
        ], [
            'override_data' => $this->json([]),
            'is_active' => false,
        ]);
    }

    /** @return array{0:?int,1:?int} */
    private function seedPricing(?int $serviceId, ?int $locationId): array
    {
        $packageId = $this->ensureRow('pricing_packages', [
            'name' => 'Starter Painting Package',
            'service_id' => $serviceId,
            'location_id' => null,
        ], [
            'subtitle' => 'Default draft pricing package',
            'badge' => null,
            'description' => '<p>Review pricing before activating this package.</p>',
            'currency' => 'SGD',
            'is_featured' => false,
            'is_active' => false,
            'sort_order' => 0,
        ]);

        if ($packageId) {
            $this->ensureRow('pricing_items', [
                'pricing_package_id' => $packageId,
                'label' => 'Standard Property',
            ], [
                'amount' => null,
                'amount_max' => null,
                'price_type' => 'call',
                'unit' => 'property',
                'prefix' => null,
                'suffix' => null,
                'sort_order' => 0,
                'is_active' => false,
            ]);
        }

        $addonId = $this->ensureRow('pricing_addons', ['name' => 'Ceiling Painting'], [
            'description' => '<p>Default pricing add-on. Configure amount before activation.</p>',
            'amount' => null,
            'amount_max' => null,
            'price_type' => 'call',
            'unit' => 'job',
            'is_active' => false,
            'sort_order' => 0,
        ]);

        if ($packageId && $addonId) {
            $this->ensureRow('pricing_package_addon', [
                'pricing_package_id' => $packageId,
                'pricing_addon_id' => $addonId,
            ], [
                'override_data' => null,
            ]);
        }

        unset($locationId);

        return [$packageId, $addonId];
    }

    private function seedGallery(?int $serviceId): ?int
    {
        return $this->ensureRow('galleries', ['name' => 'Demo Portfolio'], [
            'attachable_type' => $serviceId ? Service::class : null,
            'attachable_id' => $serviceId,
            'layout' => 'grid',
            'is_active' => false,
        ]);
    }

    private function seedVideo(?int $serviceId): ?int
    {
        return $this->ensureRow('videos', ['title' => 'Demo Service Video'], [
            'attachable_type' => $serviceId ? Service::class : null,
            'attachable_id' => $serviceId,
            'caption' => 'Inactive demo video. Replace with your own video before activation.',
            'source_type' => 'youtube',
            'provider' => 'youtube',
            'provider_video_id' => 'dQw4w9WgXcQ',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => null,
            'autoplay' => false,
            'muted' => false,
            'controls' => true,
            'loop' => false,
            'processing_status' => 'ready',
            'processing_error' => null,
            'sort_order' => 0,
            'is_active' => false,
        ]);
    }

    private function seedTestimonial(?int $serviceId): ?int
    {
        $testimonialId = $this->ensureRow('testimonials', [
            'customer_name' => 'Demo Customer — Replace Me',
            'source' => 'direct',
        ], [
            'type' => 'text',
            'customer_title' => null,
            'rating' => null,
            'review' => 'Inactive demo testimonial. Replace this record with verified customer feedback.',
            'source_url' => null,
            'reviewed_at' => null,
            'is_featured' => false,
            'is_active' => false,
            'sort_order' => 0,
        ]);

        if ($testimonialId && $serviceId) {
            $this->ensurePivot('testimonialables', [
                'testimonial_id' => $testimonialId,
                'testimonialable_type' => Service::class,
                'testimonialable_id' => $serviceId,
            ], [
                'sort_order' => 0,
            ]);
        }

        return $testimonialId;
    }

    private function seedFaq(?int $serviceId): ?int
    {
        $faqId = $this->ensureRow('faqs', ['question' => 'Do you provide an on-site quotation?'], [
            'answer' => '<p>Configure the correct business answer before activating this FAQ.</p>',
            'is_active' => false,
        ]);

        if ($faqId && $serviceId) {
            $this->ensurePivot('faqables', [
                'faq_id' => $faqId,
                'faqable_type' => Service::class,
                'faqable_id' => $serviceId,
            ], [
                'sort_order' => 0,
            ]);
        }

        return $faqId;
    }

    /** @return array{0:?int,1:?int} */
    private function seedBlog(?int $adminId): array
    {
        $categoryId = $this->ensureRow('categories', ['slug' => 'general'], [
            'name' => 'General',
            'description' => '<p>Default blog category.</p>',
            'is_active' => true,
        ]);

        $postId = null;

        if ($adminId) {
            $postId = $this->ensureRow('posts', ['slug' => 'welcome-to-our-blog'], [
                'author_id' => $adminId,
                'category_id' => $categoryId,
                'title' => 'Welcome to Our Blog',
                'excerpt' => '<p>Default draft post. Replace with your first article.</p>',
                'body' => '<p>This is a draft starter post created by the project default seeder.</p>',
                'status' => 'draft',
                'published_at' => null,
                'reading_minutes' => 1,
                'allow_comments' => false,
            ]);
        }

        return [$categoryId, $postId];
    }

    /** @return array{0:?int} */
    private function seedContact(?int $homeId): array
    {
        $channelId = $this->ensureRow('contact_channels', [
            'type' => 'email',
            'value' => 'hello@example.com',
        ], [
            'label' => 'Demo Email — Replace Me',
            'region' => null,
            'display_value' => 'hello@example.com',
            'message_template' => null,
            'icon' => 'email',
            'colour' => 'primary',
            'availability_text' => null,
            'is_default' => false,
            'is_active' => false,
            'track_clicks' => false,
            'sort_order' => 0,
        ]);

        if ($channelId && $homeId) {
            $this->ensureRow('contact_targets', [
                'contact_channel_id' => $channelId,
                'targetable_type' => Page::class,
                'targetable_id' => $homeId,
            ]);
        }

        return [$channelId];
    }

    private function seedLeadFormField(): ?int
    {
        return $this->ensureRow('lead_form_fields', ['field_key' => 'property_type'], [
            'label' => 'Property Type',
            'placeholder' => 'Select property type',
            'options' => $this->json(['HDB', 'Condo', 'Landed', 'Commercial']),
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function seedDemoOperationalData(?int $adminId, ?int $locationId, ?int $serviceId, ?int $leadFormFieldId): void
    {
        $userId = null;

        if (Schema::hasTable('users')) {
            $user = DB::table('users')->where('email', 'demo.customer@example.com')->first();

            if ($user) {
                $userId = (int) $user->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => 'Demo Customer',
                    'email' => 'demo.customer@example.com',
                    'email_verified_at' => null,
                    'password' => Hash::make('DemoPassword123!'),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $leadId = $this->ensureRow('leads', ['reference' => 'DEMO-LEAD-0001'], [
            'user_id' => $userId,
            'name' => 'Demo Lead — Delete Me',
            'email' => 'demo.lead@example.com',
            'phone' => '+6590000000',
            'location_id' => $locationId,
            'message' => '<p>Demo lead created for local/testing environments.</p>',
            'metadata' => $this->json(['demo' => true]),
            'status' => 'closed',
            'assigned_to' => $adminId,
            'source_page_url' => '/',
            'utm' => $this->json(['utm_source' => 'demo-seeder']),
            'consent' => $this->json(['marketing' => false]),
            'pricing_snapshot' => null,
        ]);

        if (! $leadId) {
            return;
        }

        if ($leadFormFieldId) {
            $this->ensureRow('lead_form_answers', [
                'lead_id' => $leadId,
                'field_key' => 'property_type',
            ], [
                'lead_form_field_id' => $leadFormFieldId,
                'field_label' => 'Property Type',
                'answer' => 'HDB',
                'sort_order' => 0,
            ]);
        }

        if ($serviceId) {
            $this->ensureRow('lead_services', [
                'lead_id' => $leadId,
                'service_id' => $serviceId,
            ], [
                'notes' => '<p>Demo requested service.</p>',
            ]);
        }

        if (Schema::hasTable('lead_status_histories') && ! DB::table('lead_status_histories')->where('lead_id', $leadId)->exists()) {
            DB::table('lead_status_histories')->insert([
                'lead_id' => $leadId,
                'changed_by_type' => $adminId ? Admin::class : null,
                'changed_by_id' => $adminId,
                'from_status' => null,
                'to_status' => 'closed',
                'reason' => 'Demo record created by ProjectDefaultSeeder.',
                'created_at' => now(),
                'deleted_at' => null,
            ]);
        }

        if ($adminId) {
            $this->ensureRow('lead_notes', [
                'lead_id' => $leadId,
                'author_type' => Admin::class,
                'author_id' => $adminId,
            ], [
                'note' => '<p>Demo internal note created by ProjectDefaultSeeder.</p>',
                'visible_to_user' => false,
            ]);
        }

        if ($adminId) {
            $this->ensureRow('audit_logs', [
                'action' => 'defaults.seeded',
                'auditable_type' => Admin::class,
                'auditable_id' => $adminId,
            ], [
                'actor_type' => Admin::class,
                'actor_id' => $adminId,
                'old_values' => null,
                'new_values' => $this->json(['environment' => app()->environment()]),
                'ip_address' => null,
                'user_agent' => 'ProjectDefaultSeeder',
                'created_at' => now(),
            ]);
        }
    }

    private function seedSeo(?int $homeId): void
    {
        if (! $homeId) {
            return;
        }

        $this->ensureRow('seo_metas', [
            'seoable_type' => Page::class,
            'seoable_id' => $homeId,
        ], [
            'meta_title' => 'Professional Painting Services',
            'meta_description' => 'Configure the final homepage SEO description in Admin.',
            'canonical_url' => null,
            'robots' => 'index,follow',
            'og_title' => 'Professional Painting Services',
            'og_description' => 'Configure the final social description in Admin.',
            'schema_overrides' => null,
            'include_in_sitemap' => true,
            'sitemap_priority' => 1.0,
            'sitemap_changefreq' => 'weekly',
        ]);
    }

    private function seedRedirect(): void
    {
        $this->ensureRow('redirects', ['from_path' => '/old-home'], [
            'to_url' => '/',
            'status_code' => 301,
            'hits' => 0,
            'is_active' => false,
            'last_hit_at' => null,
        ]);
    }

    private function seedTracking(): void
    {
        $providers = ['meta_pixel', 'meta_capi', 'ga4', 'gtm', 'tiktok'];

        foreach ($providers as $provider) {
            $providerId = $this->ensureRow('tracking_providers', ['provider' => $provider], [
                'public_identifier' => null,
                'secret' => null,
                'config' => null,
                'is_enabled' => false,
                'test_mode' => true,
            ]);

            if ($providerId) {
                $this->ensureRow('tracking_event_rules', [
                    'tracking_provider_id' => $providerId,
                    'internal_event' => 'page_view',
                ], [
                    'provider_event' => match ($provider) {
                        'meta_pixel', 'meta_capi' => 'PageView',
                        'ga4', 'gtm' => 'page_view',
                        'tiktok' => 'PageView',
                        default => 'page_view',
                    },
                    'parameter_map' => $this->json([]),
                    'requires_marketing_consent' => true,
                    'is_enabled' => false,
                ]);
            }
        }
    }

    private function seedCampaign(?int $adminId, ?int $homeId): void
    {
        $campaignId = $this->ensureRow('campaigns', ['slug' => 'starter-campaign'], [
            'title' => 'Starter Campaign',
            'custom_route' => null,
            'summary' => '<p>Default draft campaign. Configure content and media before publishing.</p>',
            'status' => 'draft',
            'hero_config' => $this->json([
                'heading' => 'Starter Campaign',
                'subheading' => 'Configure your campaign hero.',
                'rating' => 5,
                'overlay' => 35,
                'cta_label' => 'Get a Quote',
                'cta_url' => '#quote',
                'embed_provider' => null,
                'embed_url' => null,
                'autoplay' => false,
                'muted' => true,
                'controls' => true,
                'loop' => false,
            ]),
            'published_at' => null,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        if ($campaignId) {
            $this->ensureRow('campaign_sections', [
                'campaign_id' => $campaignId,
                'section_key' => 'hero',
            ], [
                'heading' => 'Starter Campaign',
                'subheading' => 'Configure this section before publishing.',
                'payload' => $this->json([
                    'headline' => 'Starter Campaign',
                    'subheadline' => 'Configure this section before publishing.',
                    'cta_label' => 'Get a Quote',
                    'cta_url' => '#quote',
                ]),
                'is_enabled' => true,
                'sort_order' => 0,
            ]);
        }

        if (Schema::hasTable('campaign_settings')) {
            $settings = DB::table('campaign_settings')->where('id', 1)->first();

            if (! $settings) {
                DB::table('campaign_settings')->insert([
                    'id' => 1,
                    'default_campaign_id' => null,
                    'fallback_mode' => 'homepage',
                    'fallback_page_id' => $homeId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]);
            } elseif ($settings->fallback_page_id === null && $homeId) {
                DB::table('campaign_settings')
                    ->where('id', 1)
                    ->update([
                        'fallback_page_id' => $homeId,
                        'updated_by' => $settings->updated_by ?: $adminId,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function ensureRow(string $table, array $identity, array $defaults = []): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        foreach ($identity as $column => $value) {
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }

        $existing = $query->first();

        if ($existing) {
            return isset($existing->id) ? (int) $existing->id : null;
        }

        $payload = array_merge($identity, $defaults);
        $columns = $this->columns($table);

        if (in_array('created_at', $columns, true) && ! array_key_exists('created_at', $payload)) {
            $payload['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true) && ! array_key_exists('updated_at', $payload)) {
            $payload['updated_at'] = now();
        }

        if (in_array('deleted_at', $columns, true) && ! array_key_exists('deleted_at', $payload)) {
            $payload['deleted_at'] = null;
        }

        return (int) DB::table($table)->insertGetId($payload);
    }

    private function ensurePivot(string $table, array $identity, array $defaults = []): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $query = DB::table($table);

        foreach ($identity as $column => $value) {
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }

        if ($query->exists()) {
            return;
        }

        $payload = array_merge($identity, $defaults);
        $columns = $this->columns($table);

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table($table)->insert($payload);
    }

    private function value(string $table, array $where, string $column): mixed
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        foreach ($where as $key => $value) {
            $value === null ? $query->whereNull($key) : $query->where($key, $value);
        }

        return $query->value($column);
    }

    /** @return array<int, string> */
    private function columns(string $table): array
    {
        return $this->columnCache[$table] ??= Schema::getColumnListing($table);
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
