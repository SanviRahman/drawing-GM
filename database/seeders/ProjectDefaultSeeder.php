<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Location;
use App\Models\Page;
use App\Models\SectionDefinition;
use App\Models\Service;
use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProjectDefaultSeeder extends Seeder
{
    private const HOME_SOURCE = 'https://budgetpainting.sg/';
    private const PLASTER_SOURCE = 'https://budgetpainting.sg/wall-plastering/';
    private const HACK_SOURCE = 'https://budgetpainting.sg/hdb-wall-hacking-singapore/';

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

            // Previously separate BudgetPaintingReferenceSeeder: merged here, exactly once.
            $this->seedReferenceSitePages();
            $referenceServices = $this->seedReferenceServices();
            $referenceMenuItems = $this->seedReferenceMenu();
            $this->seedReferencePricing($referenceServices);
            $this->seedReferenceAddons();
            $this->seedReferenceFaqs($referenceMenuItems);

            // Inactive, clearly labelled CMS draft records; never fake active testimonials/media.
            $this->seedAdditionalDraftContent($adminId, $homeId, $referenceServices);

            // Public-safe, image-free Home content and verified caller-supplied WhatsApp contacts.
            $this->seedPublicHomepageContent();
            $this->seedConfiguredWhatsAppContacts();

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
            'provider_video_id' => null,
            'source_url' => null,
            'duration_seconds' => null,
            'autoplay' => false,
            'muted' => false,
            'controls' => true,
            'loop' => false,
            'processing_status' => 'pending',
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
            // Do not return or recreate soft-deleted records (unique slugs may remain reserved).
            if (isset($existing->deleted_at) && $existing->deleted_at !== null) {
                return null;
            }
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
    /**
     * Image-free, public-safe starter copy for the Home layout.
     * Nothing below asserts business history, rankings, guarantees or verified reviews.
     * All content is insert-if-missing: an administrator's existing edits win.
     */
    private function seedPublicHomepageContent(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasTable('page_sections')) {
            return;
        }

        $homeId = DB::table('pages')
            ->where('is_homepage', true)->whereNull('deleted_at')->value('id');
        if (! $homeId) {
            return;
        }

        $benefits = [
            ['title' => 'Plan the work scope', 'description' => 'Discuss which rooms, walls and ceilings need work before requesting a quotation.'],
            ['title' => 'Choose suitable materials', 'description' => 'Compare proposed paint grades and preparation steps for your property.'],
            ['title' => 'Ask about protection', 'description' => 'Confirm how floors, furniture and fixtures will be protected during the job.'],
            ['title' => 'Clarify the schedule', 'description' => 'Discuss access, work stages and suitable dates with the team.'],
            ['title' => 'Review the quotation', 'description' => 'Request a breakdown of the planned work and any possible extras.'],
            ['title' => 'Arrange final inspection', 'description' => 'Discuss the handover and how any remaining concerns can be raised.'],
        ];

        $guarantees = [
            ['title' => 'Scope confirmed before booking', 'description' => 'Confirm the exact work and materials before accepting an offer.'],
            ['title' => 'Clear preparation expectations', 'description' => 'Ask what protection and surface preparation is included.'],
            ['title' => 'A practical work plan', 'description' => 'Discuss the schedule and access needs before work starts.'],
            ['title' => 'Handover discussion', 'description' => 'Review the completed scope together.'],
        ];

        $steps = [
            ['title' => 'REQUEST A QUOTE', 'description' => 'Describe your rooms and service requirements.', 'button' => 'Get a Free Quote'],
            ['title' => 'REVIEW THE WORK', 'description' => 'Discuss surfaces, photos and the scope of preparation.', 'button' => 'Ask a Question'],
            ['title' => 'CONFIRM THE DETAILS', 'description' => 'Review pricing and work terms provided by the team.', 'button' => 'Enquire Now'],
            ['title' => 'ARRANGE THE PROJECT', 'description' => 'Coordinate a suitable date after agreeing to the quotation.', 'button' => 'Contact Us'],
            ['title' => 'CHECK THE FINISH', 'description' => 'Review the result and share feedback after completion.', 'button' => 'View Work'],
        ];

        $recap = [
            'Discuss your painting or plastering requirements before arranging work.',
            'Receive a quotation based on the proposed scope and site condition.',
            'Confirm the chosen paint, coating or plastering materials.',
            'Ask whether masking, furniture protection and cleanup are included.',
            'Agree on the work dates and site-access arrangements.',
            'Review project images and videos once approved media is available.',
            'Check the active FAQs or contact a member of the team.',
            'Submit a quotation enquiry using the website form.',
        ];

        $definitions = [
            'benefit_grid' => [
                'heading' => 'WHY CHOOSE OUR HOUSE PAINTERS?',
                'subheading' => 'Project planning details to review before booking',
                'payload' => ['items' => $benefits, 'guarantees' => $guarantees],
                'sort_order' => 10,
            ],
            'rich_text' => [
                'heading' => 'Before you proceed',
                'subheading' => 'Things to confirm before work starts',
                'payload' => ['text' => '<p>Confirm the areas to be worked on.</p><p>Ask which materials will be used.</p><p>Clarify preparation and cleanup expectations.</p><p>Agree on the work schedule and site access.</p><p>Review the final scope and quotation.</p>'],
                'sort_order' => 20,
            ],
            'video_gallery' => [
                'heading' => 'See how our work is done',
                'subheading' => 'Published project videos are shown here when available.',
                'payload' => [], 'sort_order' => 30,
            ],
            'testimonials' => [
                'heading' => 'Customer feedback',
                'subheading' => 'Verified customer feedback appears after approval.',
                'payload' => [], 'sort_order' => 40,
            ],
            'paint_calculator' => [
                'heading' => 'Explore painting price options',
                'subheading' => 'Select an available package to review its current published information.',
                'payload' => [], 'sort_order' => 50,
            ],
            'cta' => [
                'heading' => 'Let us help plan your project',
                'subheading' => 'Share your details to discuss the next steps.',
                'payload' => ['steps' => $steps, 'items' => $recap], 'sort_order' => 60,
            ],
            'before_after' => [
                'heading' => 'Before & After Workmanship Gallery',
                'subheading' => 'Real project photos appear here once uploaded.',
                'payload' => [], 'sort_order' => 70,
            ],
            'whatsapp_reviews' => [
                'heading' => 'Client messages',
                'subheading' => 'Approved WhatsApp testimonial screenshots appear when available.',
                'payload' => [], 'sort_order' => 80,
            ],
            'faq' => [
                'heading' => 'Frequently Asked Questions',
                'subheading' => 'General information about quotes and preparing for painting work.',
                'payload' => [], 'sort_order' => 90,
            ],
        ];

        foreach ($definitions as $key => $entry) {
            $definitionId = $this->value('section_definitions', ['key' => $key], 'id');
            if (! $definitionId) {
                continue;
            }
            $this->ensureRow('page_sections', [
                'page_id' => (int) $homeId,
                'section_definition_id' => (int) $definitionId,
            ], [
                'heading' => $entry['heading'],
                'subheading' => $entry['subheading'],
                'payload' => $this->json($entry['payload']),
                'theme' => 'default',
                'sort_order' => $entry['sort_order'],
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
            ]);
        }

        // Non-promissory, useful FAQs can be published without inventing company policy.
        // Both Page and Navbar mappings are created; NavbarFaqs chooses only ONE source.
        $questions = [
            'What information should I provide for a painting quotation?' => 'Include the property type, approximate room count, areas needing work, wall condition and any photos that help explain your requirements.',
            'Can I describe plastering and painting in the same enquiry?' => 'Yes. You can describe multiple types of work in the message box so the team can review the full request.',
            'How do I request a quotation through the website?' => 'Enter your name, WhatsApp or phone number, select any applicable fields, then submit the enquiry form.',
            'Is a calculator price the same as a final quotation?' => 'Calculator values are guide prices from published packages. A final quotation depends on the actual agreed scope.',
            'What should I confirm about surface preparation?' => 'Ask about cleaning, patching, crack treatment, masking and any primer or sealer requirements.',
            'Can I send photos when discussing my enquiry?' => 'You can contact an active WhatsApp number displayed on the website to ask how to share your project photos.',
            'Should I ask about furniture and floor protection?' => 'Yes. Confirm what needs moving or covering, which party is responsible, and whether those tasks are included in the quote.',
            'How will I know which contact number is available?' => 'Use the WhatsApp contact cards on the Home page. Only active numbers configured by the administrator appear there.',
        ];
        $homeMenuId = $this->value('menu_items', [
            'menu_id' => $this->value('menus', ['location' => 'header-primary'], 'id'),
            'label' => 'Home',
        ], 'id');
        $faqPosition = 10;
        foreach ($questions as $question => $answer) {
            $faqId = $this->ensureRow('faqs', ['question' => $question], [
                'answer' => '<p>'.htmlspecialchars($answer, ENT_QUOTES | ENT_HTML5, 'UTF-8').'</p>',
                'is_active' => true,
            ]);
            if (! $faqId) {
                continue;
            }
            $this->ensurePivot('faqables', [
                'faq_id' => $faqId, 'faqable_type' => Page::class, 'faqable_id' => (int) $homeId,
            ], ['sort_order' => $faqPosition]);
            if ($homeMenuId) {
                $this->ensurePivot('faqables', [
                    'faq_id' => $faqId, 'faqable_type' => MenuItem::class, 'faqable_id' => (int) $homeMenuId,
                ], ['sort_order' => $faqPosition]);
            }
            $faqPosition++;
        }

        // A useful, zero-fabricated-price package so the calculator works without
        // importing competitor rate cards. Admin can replace with approved amounts.
        if (Schema::hasTable('pricing_packages') && Schema::hasTable('pricing_items')
            && ! DB::table('pricing_packages')
                ->join('pricing_items', 'pricing_items.pricing_package_id', '=', 'pricing_packages.id')
                ->where('pricing_packages.is_active', true)
                ->whereNull('pricing_packages.deleted_at')
                ->where('pricing_items.is_active', true)
                ->whereNull('pricing_items.deleted_at')
                ->exists()) {
            $packageId = $this->ensureRow('pricing_packages', [
                'name' => 'Request a Custom Painting Quote',
                'service_id' => null,
                'location_id' => null,
            ], [
                'subtitle' => 'Price confirmed after scope review',
                'badge' => null,
                'description' => '<p>Price not published. Contact the team for a tailored quotation.</p>',
                'currency' => 'SGD',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]);
            if ($packageId) {
                $this->ensureRow('pricing_items', [
                    'pricing_package_id' => $packageId,
                    'label' => 'Tailored quotation',
                ], [
                    'amount' => null, 'amount_max' => null, 'price_type' => 'call',
                    'unit' => 'job', 'prefix' => null, 'suffix' => null,
                    'sort_order' => 0, 'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Only caller-supplied, valid WhatsApp numbers are provisioned as active.
     * Each ENV variable corresponds to one desk; do not reuse one phone for multiple
     * regions, invent reply-time guarantees or overwrite already edited channels.
     */
    private function seedConfiguredWhatsAppContacts(): void
    {
        if (! Schema::hasTable('contact_channels')) {
            return;
        }

        $desks = [
            ['WEBSITE_WA_ISLANDWIDE', 'Any Location', 'Islandwide'],
            ['WEBSITE_WA_NORTH', 'North / Northeast', 'North / Northeast'],
            ['WEBSITE_WA_EAST', 'East Region', 'East'],
            ['WEBSITE_WA_WEST', 'West / Central', 'West / Central'],
        ];
        $processed = [];
        foreach ($desks as $sortOrder => [$key, $label, $region]) {
            $raw = trim((string) env($key, ''));
            if ($raw === '') {
                continue;
            }
            $digits = preg_replace('/\D+/', '', $raw);
            if (! is_string($digits) || preg_match('/^[0-9]{7,15}$/', $digits) !== 1) {
                $this->command?->warn("Skipped invalid WhatsApp number from {$key}.");
                continue;
            }
            if (isset($processed[$digits])) {
                $this->command?->warn("Skipped duplicate WhatsApp number from {$key}.");
                continue;
            }
            $processed[$digits] = true;

            $existing = DB::table('contact_channels')
                ->where('type', 'whatsapp')
                ->where(function ($query) use ($digits) {
                    $query->where('value', $digits)
                        ->orWhere('value', '+'.$digits);
                })->first();
            if ($existing) {
                continue; // Never reactivate or change an admin-managed contact.
            }
            // Check all active/inactive records, even with formatted spaces/dashes.
            $matches = DB::table('contact_channels')->where('type', 'whatsapp')
                ->get(['value'])->contains(fn ($row) => preg_replace('/\D+/', '', (string) $row->value) === $digits);
            if ($matches) {
                continue;
            }

            $this->ensureRow('contact_channels', [
                'type' => 'whatsapp', 'value' => '+'.$digits,
            ], [
                'label' => $label,
                'region' => $region,
                'display_value' => '+'.$digits,
                'message_template' => null,
                'icon' => 'whatsapp',
                'colour' => 'whatsapp',
                'availability_text' => null,
                'is_default' => $sortOrder === 0,
                'is_active' => true,
                'track_clicks' => true,
                'sort_order' => $sortOrder + 1,
            ]);
        }
    }

    private function seedReferenceSitePages(): void
    {
        $pages = [
            ['plastering', 'Wall Plastering', 'Review-only page inspired by wall plastering topics.', self::PLASTER_SOURCE],
            ['hacking', 'Wall & Tile Hacking', 'Review-only page for removal and hacking services.', self::HACK_SOURCE],
            ['false-ceiling', 'False Ceiling', 'Page placeholder: no verified BudgetPainting false-ceiling price data was found.', null],
            ['pricing', 'Painting Prices', 'Review-only pricing page; competitor prices are not your business prices.', self::HOME_SOURCE],
            ['contact', 'Contact', 'Add your company contact details after verification.', null],
            ['blog', 'Blog', 'Articles to be managed by your team.', null],
            ['cost-calculator', 'Painting Cost Calculator', 'A calculator requires separately implemented logic and approved rates.', 'https://budgetpainting.sg/painting-cost-calculator/'],
        ];
        foreach ($pages as [$slug, $title, $description, $source]) {
            $this->referenceInsertMissing('pages', ['slug' => $slug], [
                'title' => $title,
                'excerpt' => $description.($source ? ' Source: '.$source : ''),
                'template' => 'default',
                'hero_config' => json_encode(['heading' => $title, 'subheading' => 'Draft — confirm before publication'], JSON_THROW_ON_ERROR),
                'status' => 'draft', 'published_at' => null, 'is_homepage' => false,
                'show_header' => true, 'show_footer' => true,
                'created_by' => null, 'updated_by' => null,
            ]);
        }
        // Do not modify the user's existing homepage or create a second homepage.
    }

    /** @return array<string, int> */
    private function seedReferenceServices(): array
    {
        $data = [
            'house-painting' => ['House Painting', 'HDB, condo and residential painting service reference.', self::HOME_SOURCE],
            'wall-plastering' => ['Wall Plastering', 'Surface preparation and smoothing; suitability depends on substrate.', self::PLASTER_SOURCE],
            'wall-hacking' => ['Hacking', 'Removal of selected wall/floor tiles and fittings; permits may be required.', self::HACK_SOURCE],
            'false-ceiling' => ['False Ceiling', 'Placeholder only; no verified source-specific facts or prices seeded.', null],
        ];
        $ids = [];
        foreach ($data as $slug => [$name, $summary, $source]) {
            $id = $this->referenceInsertMissing('services', ['slug' => $slug], [
                'name' => $name, 'summary' => $summary,
                'content' => '<p><strong>UNPUBLISHED REFERENCE — NOT YOUR BUSINESS OFFER.</strong></p>'.
                    ($source ? '<p>Research: '.htmlspecialchars($source, ENT_QUOTES, 'UTF-8').'</p>' : '<p>Verify this service and its scope.</p>'),
                'icon' => 'paint-roller',
                'hero_config' => json_encode([], JSON_THROW_ON_ERROR),
                'status' => 'draft', 'published_at' => null,
                'is_featured' => false, 'sort_order' => count($ids),
            ]);
            if ($id) $ids[$slug] = $id;
        }
        return $ids;
    }

    /** @return array<string, int> */
    private function seedReferenceMenu(): array
    {
        $menuId = $this->referenceInsertMissing('menus', ['location' => 'header-primary'], [
            'name' => 'Header Primary', 'is_active' => true,
        ]);
        if (! $menuId) return [];
        $rows = [
            ['Home', '/', 0],
            ['Plastering', '/plastering', 10],
            ['Hacking', '/hacking', 20],
            ['False Ceiling', '/false-ceiling', 30],
            ['Pricing', '/pricing', 40],
            ['Contact', '/contact', 50],
            ['Blog', '/blog', 60],
            ['Cost Calculator', '/cost-calculator', 70],
        ];
        $ids = [];
        foreach ($rows as [$label, $url, $order]) {
            $id = $this->referenceInsertMissing('menu_items', ['menu_id' => $menuId, 'label' => $label], [
                'parent_id' => null, 'link_type' => 'url', 'linkable_type' => null, 'linkable_id' => null,
                'url' => $url, 'icon' => null, 'target' => '_self', 'css_class' => null,
                'sort_order' => $order,
                // Inactive on purpose: public routes may not yet exist in the supplied ZIP.
                'is_active' => false, 'faq_section_enabled' => false,
            ]);
            if ($id) $ids[$label] = $id;
        }
        return $ids;
    }

    /** @param array<string,int> $services */
    private function seedReferencePricing(array $services): void
    {
        $painting = $services['house-painting'] ?? null;
        $plastering = $services['wall-plastering'] ?? null;
        $categories = ['Per Room', '2 Room HDB', '3 Room HDB', '4 Room HDB', '5 Room HDB', 'Executive Apartment', 'Executive Maisonette'];
        $packages = [
            ['Reference · Basic', 'Nippon Matex White', [280, 599, 649, 699, 899, 999, 1249], $painting, self::HOME_SOURCE],
            ['Reference · Standard', 'Nippon Vinilex 5000', [320, 649, 699, 799, 999, 1099, 1299], $painting, self::HOME_SOURCE],
            ['Reference · Superior', 'Nippon Odourless', [400, 699, 949, 1099, 1249, 1299, 1499], $painting, self::HOME_SOURCE],
            ['Reference · Luxury', 'Nippon Odourless Ultra Durable', [450, 1000, 1200, 1350, 1450, 1550, 1900], $painting, self::HOME_SOURCE],
        ];
        foreach ($packages as $position => [$name, $subtitle, $amounts, $serviceId, $source]) {
            $packageId = $this->referencePackage($name, $subtitle, $serviceId, $position, $source);
            if (! $packageId) continue;
            foreach ($amounts as $i => $amount) {
                $this->referencePrice($packageId, $categories[$i], (float) $amount, null, 'fixed', $i === 0 ? 'room' : 'property', $i);
            }
        }

        // Separate plastering rate ranges, as published on the plastering reference page.
        $packageId = $this->referencePackage('Reference · Wall Plastering Only', 'Published size-based ranges', $plastering, 50, self::PLASTER_SOURCE);
        $ranges = [
            ['Per Room', 800, 1100, 'room'], ['2 Room HDB (up to 600 sqft)', 1700, 2100, 'property'],
            ['3 Room HDB (up to 800 sqft)', 2100, 2800, 'property'],
            ['4 Room HDB (up to 1000 sqft)', 2800, 3400, 'property'],
            ['5 Room HDB (up to 1200 sqft)', 3400, 4100, 'property'],
            ['Executive Apartment (up to 1500 sqft)', 4100, 4700, 'property'],
            ['Executive Maisonette (up to 1500 sqft)', 4700, 5600, 'property'],
        ];
        if ($packageId) foreach ($ranges as $i => [$label, $min, $max, $unit]) {
            $this->referencePrice($packageId, $label, (float) $min, (float) $max, 'range', $unit, $i);
        }
        // The plastering page also lists a separate painting-only rate card, distinct from the homepage.
        $packageId = $this->referencePackage('Reference · Painting on Plastering Page', 'Nippon Vinilex 5000', $painting, 60, self::PLASTER_SOURCE);
        $plasterPainting = [[300, 350], [599, null], [799, null], [999, null], [1299, null], [1499, null], [1699, null]];
        if ($packageId) foreach ($plasterPainting as $i => [$min, $max]) {
            $this->referencePrice($packageId, $categories[$i], (float) $min, $max !== null ? (float) $max : null, $max !== null ? 'range' : 'fixed', $i === 0 ? 'room' : 'property', $i);
        }
    }

    private function referencePackage(string $name, string $subtitle, ?int $serviceId, int $position, string $source): ?int
    {
        // Key is unique to this import. Never update an existing admin-edited package.
        return $this->referenceInsertMissing('pricing_packages', ['name' => $name, 'service_id' => $serviceId, 'location_id' => null], [
            'subtitle' => $subtitle, 'badge' => 'UNVERIFIED',
            'description' => '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: '.htmlspecialchars($source, ENT_QUOTES, 'UTF-8').'</p>',
            'currency' => 'SGD', 'is_featured' => false, 'is_active' => false, 'sort_order' => $position,
        ]);
    }

    private function referencePrice(int $packageId, string $label, float $min, ?float $max, string $type, string $unit, int $order): void
    {
        $this->referenceInsertMissing('pricing_items', ['pricing_package_id' => $packageId, 'label' => $label], [
            'amount' => $min, 'amount_max' => $max, 'price_type' => $type, 'unit' => $unit,
            'prefix' => null, 'suffix' => null, 'sort_order' => $order, 'is_active' => false,
        ]);
    }

    private function seedReferenceAddons(): void
    {
        $addons = [
            ['Reference · Sealer (1 room)', 50, null, 'fixed', 'room'],
            ['Reference · Sealer (whole house)', 150, 350, 'range', 'house'],
            ['Reference · Door frame', 30, null, 'fixed', 'frame'],
            ['Reference · Door + frame set', 60, null, 'fixed', 'set'],
            ['Reference · Anti-mould ceiling upgrade', 50, null, 'fixed', 'bedroom'],
            ['Reference · Balcony painting', 80, 100, 'range', 'balcony'],
            ['Reference · Major peeling + putty', 50, 300, 'range', 'job'],
            ['Reference · Extra colour', 50, 100, 'range', 'wall/room'],
            ['Reference · High ceiling (over 2.8m)', 100, 400, 'range', 'job'],
            ['Reference · Pipe painting', 50, null, 'fixed', 'toilet/yard'],
            ['Reference · Wallpaper removal', 50, 100, 'range', 'wall/room'],
            ['Reference · Touch-up trip', 150, 250, 'range', 'trip'],
        ];
        foreach ($addons as $i => [$name, $min, $max, $type, $unit]) {
            $this->referenceInsertMissing('pricing_addons', ['name' => $name], [
                'description' => '<p>UNVERIFIED reference range from '.self::HOME_SOURCE.'. Review source rules and scope.</p>',
                'amount' => $min, 'amount_max' => $max,
                'price_type' => $type, 'unit' => $unit,
                'is_active' => false, 'sort_order' => $i + 100,
            ]);
        }
    }

    /** @param array<string,int> $items */
    private function seedReferenceFaqs(array $items): void
    {
        // Paraphrased informational starter questions; answers do not claim your company's terms.
        $data = [
            'Home' => [
                ['Which paint grades are shown in the comparison?', 'The public reference lists Nippon Matex White, Vinilex 5000, Odourless and Odourless Ultra Durable. Confirm which products your own company offers.'],
                ['Does the painting price depend on flat size?', 'The reference separates per-room quotes from 2-room to 5-room HDB and executive flat prices. All local prices must be approved separately.'],
                ['Is the calculator estimate a final quotation?', 'The source labels its calculator price as an estimate. Your company should confirm a final quotation after reviewing the actual project scope.'],
            ],
            'Plastering' => [
                ['Should existing wall tiles be removed before plastering?', 'Substrate assessment is essential. The reference recommends tile removal rather than plastering straight over tiled areas; inspect the surface before promising a method.'],
                ['How long does plastering normally take?', 'The reference discusses approximately 1–2 days for a 3-room HDB and 3–4 days for larger homes. Your own team must confirm actual scheduling.'],
                ['How many coats of plaster are needed?', 'The reference discusses two plaster coats with possible additional coats. Actual requirements depend on the wall and product system.'],
                ['When can a new coat be applied?', 'Drying and recoat times vary with materials, moisture and ventilation; follow the selected product guidance and site conditions.'],
            ],
            'Hacking' => [
                ['Is approval necessary before wall or tile hacking?', 'Some HDB renovation works need approval. Check current HDB guidance and appoint qualified contractors before work starts.'],
                ['Which rooms and fittings may need removal?', 'The reference covers kitchen and bathroom tiles, skirting and selected fittings. Confirm each demolition item and disposal in the quotation.'],
                ['Can hacking be scheduled on weekends?', 'Working hours depend on local rules, approvals and contractor scheduling. Confirm the allowed dates before booking.'],
            ],
            'False Ceiling' => [
                ['Can the quotation include a false-ceiling design?', 'Draft placeholder: confirm whether this service is actually offered and what site survey is required.'],
                ['Can lights and access panels be included?', 'Draft placeholder: define lighting, access, service clearances and licensed electrical requirements before approval.'],
            ],
        ];
        foreach ($data as $label => $faqs) {
            $navId = $items[$label] ?? null;
            if (! $navId) continue;
            foreach ($faqs as $position => [$question, $answer]) {
                // FAQ record is inactive by design until the company verifies the answer.
                $faqId = $this->referenceInsertMissing('faqs', ['question' => '[Reference] '.$question], [
                    'answer' => '<p>'.htmlspecialchars($answer, ENT_QUOTES, 'UTF-8').'</p>',
                    'is_active' => false,
                ]);
                if ($faqId) {
                    $this->referenceInsertMissing('faqables', [
                        'faq_id' => $faqId,
                        'faqable_type' => MenuItem::class,
                        'faqable_id' => $navId,
                    ], ['sort_order' => $position]);
                }
            }
        }
    }

    /** @param array<string,mixed> $where @param array<string,mixed> $attributes */
    private function referenceInsertMissing(string $table, array $where, array $attributes): ?int
    {
        $existing = DB::table($table)->where($where)->first();
        if ($existing) {
            // Never resurrect trashed items or overwrite changes to an existing record.
            return property_exists($existing, 'deleted_at') && $existing->deleted_at !== null ? null : (property_exists($existing, 'id') ? (int) $existing->id : 1);
        }
        $data = array_merge($where, $attributes);
        if (Schema::hasColumn($table, 'created_at')) $data['created_at'] = now();
        if (Schema::hasColumn($table, 'updated_at')) $data['updated_at'] = now();
        // faqables has no id; insert + return a sentinel for the idempotent mapping case.
        if (! Schema::hasColumn($table, 'id')) {
            DB::table($table)->insert($data);
            return 1;
        }
        return (int) DB::table($table)->insertGetId($data);
    }

    /**
     * Safe draft CMS data for previewing the admin interface.
     * "At least 10" applies to editable CONTENT models, not singleton settings,
     * actual users/admins, permission records, binary media, or real enquiries.
     * Public-facing claims/reviews/prices/contact details remain disabled.
     * Content is inserted only if its natural key is missing; never overwritten.
     *
     * @param array<string,int> $referenceServices
     */
    private function seedAdditionalDraftContent(?int $adminId, ?int $homeId, array $referenceServices): void
    {
        // 10 site settings (the original set contains nine).
        foreach ([
            'header.announcement' => ['header', '', 'string', true],
            'footer.copyright' => ['footer', '', 'string', true],
            'contact.quote_heading' => ['contact', 'Request a quotation', 'string', true],
        ] as $key => [$group, $value, $type, $public]) {
            $this->ensureRow('site_settings', ['setting_key' => $key], [
                'group_name' => $group, 'setting_value' => $value,
                'value_type' => $type, 'is_public' => $public,
            ]);
        }

        // The header-primary navbar MUST be exactly the eight reference screenshot labels
        // on a fresh database. DO NOT generate extra primary-nav items here.
        $extraPages = [
            'about' => 'About Us', 'gallery' => 'Project Gallery',
            'service-areas' => 'Service Areas', 'request-quote' => 'Request a Quote',
        ];
        foreach ($extraPages as $slug => $title) {
            $this->ensureRow('pages', ['slug' => $slug], [
                'title' => $title, 'excerpt' => 'UNPUBLISHED sample page — write and approve content.',
                'template' => 'default',
                'hero_config' => $this->json(['heading' => $title]),
                'status' => 'draft', 'published_at' => null, 'is_homepage' => false,
                'show_header' => true, 'show_footer' => true,
                'created_by' => $adminId, 'updated_by' => $adminId,
            ]);
        }

        $draftServices = [
            'condo-painting' => 'Condo Painting', 'landed-house-painting' => 'Landed House Painting',
            'office-painting' => 'Office Painting', 'ceiling-painting' => 'Ceiling Painting',
            'wall-repair' => 'Wall Repair', 'waterproofing-consultation' => 'Waterproofing Consultation',
            'door-painting' => 'Door Painting', 'commercial-painting' => 'Commercial Painting',
        ];
        foreach ($draftServices as $slug => $title) {
            $this->ensureRow('services', ['slug' => $slug], [
                'name' => $title, 'summary' => 'DRAFT service category. Verify whether your business offers it.',
                'content' => '<p>Draft only. Replace with your approved business description.</p>',
                'icon' => 'paint-roller', 'hero_config' => $this->json([]),
                'status' => 'draft', 'is_featured' => false,
                'sort_order' => 100, 'published_at' => null,
            ]);
        }
        $locationNames = [
            'central-singapore' => 'Central Singapore', 'north-singapore' => 'North Singapore',
            'south-singapore' => 'South Singapore', 'east-singapore' => 'East Singapore',
            'west-singapore' => 'West Singapore', 'ang-mo-kio' => 'Ang Mo Kio',
            'bishan' => 'Bishan', 'tampines' => 'Tampines',
            'jurong-east' => 'Jurong East', 'woodlands' => 'Woodlands',
        ];
        foreach ($locationNames as $slug => $name) {
            $this->ensureRow('locations', ['slug' => $slug], [
                'name' => $name, 'region' => 'Singapore', 'postal_codes' => $this->json([]),
                'summary' => 'DRAFT location only — service availability unverified.',
                'content' => '<p>Confirm coverage before enabling this location.</p>',
                'hero_config' => $this->json([]), 'status' => 'draft',
                'sort_order' => 100, 'published_at' => null,
            ]);
        }
        // Natural-key service features: 10 distinct editable draft examples.
        $primaryServiceId = (int) ($this->value('services', ['slug' => 'hdb-painting'], 'id') ?: 0);
        if ($primaryServiceId) {
            foreach ([
                'Surface assessment', 'Work-area protection', 'Wall cleaning', 'Surface preparation',
                'Primer evaluation', 'Paint selection', 'Coat application', 'Edge detailing',
                'Final inspection', 'Post-work cleanup',
            ] as $i => $title) {
                $this->ensureRow('service_features', ['service_id' => $primaryServiceId, 'title' => '[Draft] '.$title], [
                    'description' => 'Example item only: confirm whether your service includes this step.',
                    'icon' => 'check-circle', 'sort_order' => $i + 1, 'is_active' => false,
                ]);
            }
        }

        // The reference seeder brings 6 pricing packages and 42 items.
        // Add 4 inactive, CALL-for-quote packages with a unique, non-fabricated price item.
        foreach (['Condo Painting', 'Landed House Painting', 'Office Painting', 'Ceiling Painting'] as $i => $name) {
            $serviceSlug = ['condo-painting', 'landed-house-painting', 'office-painting', 'ceiling-painting'][$i];
            $serviceId = $this->value('services', ['slug' => $serviceSlug], 'id');
            $pkgId = $this->ensureRow('pricing_packages', [
                'name' => '[Draft] '.$name.' Enquiry', 'service_id' => $serviceId, 'location_id' => null,
            ], [
                'subtitle' => 'Request a tailored quote', 'badge' => null,
                'description' => '<p>Placeholder package, no validated price.</p>',
                'currency' => 'SGD', 'is_featured' => false, 'is_active' => false,
                'sort_order' => $i + 200,
            ]);
            if ($pkgId) {
                $this->ensureRow('pricing_items', [
                    'pricing_package_id' => $pkgId, 'label' => 'Custom quotation',
                ], [
                    'amount' => null, 'amount_max' => null, 'price_type' => 'call',
                    'unit' => 'job', 'prefix' => null, 'suffix' => null,
                    'sort_order' => 0, 'is_active' => false,
                ]);
            }
        }

        $categoryNames = [
            'painting-guides' => 'Painting Guides', 'wall-preparation' => 'Wall Preparation',
            'hdb-renovation' => 'HDB Renovation', 'condo-renovation' => 'Condo Renovation',
            'plastering-guides' => 'Plastering Guides', 'paint-selection' => 'Paint Selection',
            'renovation-checklists' => 'Renovation Checklists', 'colour-planning' => 'Colour Planning',
            'surface-care' => 'Surface Care', 'project-planning' => 'Project Planning',
        ];
        foreach ($categoryNames as $slug => $name) {
            $catId = $this->ensureRow('categories', ['slug' => $slug], [
                'name' => $name, 'description' => 'Draft category for editorial planning.',
                'is_active' => false,
            ]);
            if ($catId && $adminId) {
                $this->ensureRow('posts', ['slug' => $slug.'-starter'], [
                    'author_id' => $adminId, 'category_id' => $catId,
                    'title' => '[Draft] '.$name.' — Article Outline',
                    'excerpt' => 'UNPUBLISHED outline; content to be written and verified.',
                    'body' => '<p>Draft article placeholder. Add original content before publishing.</p>',
                    'status' => 'draft', 'published_at' => null,
                    'reading_minutes' => 1, 'allow_comments' => false,
                ]);
            }
        }

        // 10 inactive image galleries + 10 inactive gallery-item metadata placeholders.
        // Neither a photo nor a Spatie media relation is fabricated.
        foreach ([
            'Living Rooms', 'Bedrooms', 'Kitchen Painting', 'Corridor Painting',
            'Ceiling Works', 'Wall Plastering', 'Surface Preparation', 'Accent Walls',
            'Commercial Sites', 'Completed Homes',
        ] as $i => $name) {
            $galleryId = $this->ensureRow('galleries', ['name' => '[Draft] '.$name], [
                'attachable_type' => null, 'attachable_id' => null,
                'layout' => 'grid', 'is_active' => false,
            ]);
            if ($galleryId) {
                $this->ensureRow('gallery_items', [
                    'gallery_id' => $galleryId, 'title' => '[Draft] '.$name.' — Upload photo here',
                ], [
                    'item_type' => 'image',
                    'caption' => 'Inactive gallery placeholder without real media.',
                    'sort_order' => $i, 'is_active' => false,
                ]);
            }
        }

        // 10 inactive videos; no invented YouTube IDs, external videos, or preview images.
        foreach (range(1, 10) as $i) {
            $this->ensureRow('videos', ['title' => sprintf('[Draft] Video Slot %02d', $i)], [
                'attachable_type' => null, 'attachable_id' => null,
                'caption' => 'Upload or link a video owned/licensed by your business.',
                'source_type' => 'youtube', 'provider' => 'youtube',
                'provider_video_id' => null, 'source_url' => null,
                'duration_seconds' => null, 'autoplay' => false, 'muted' => false,
                'controls' => true, 'loop' => false, 'processing_status' => 'pending',
                'processing_error' => null, 'sort_order' => $i,
                'is_active' => false,
            ]);
        }
        // These are form/UI placeholders, never customer claims or a star rating.
        foreach (range(1, 10) as $i) {
            $this->ensureRow('testimonials', [
                'customer_name' => sprintf('[Draft] Review Slot %02d', $i),
                'source' => 'direct',
            ], [
                'type' => 'text', 'customer_title' => null,
                'rating' => null, 'review' => 'UNVERIFIED EMPTY SLOT — enter a genuine customer review.',
                'source_url' => null, 'reviewed_at' => null,
                'is_featured' => false, 'is_active' => false, 'sort_order' => $i,
            ]);
        }

        // 10 inactive communication slots: .invalid prevents accidental real delivery.
        for ($i = 1; $i <= 10; $i++) {
            $email = sprintf('not-configured-%02d@example.invalid', $i);
            $channelId = $this->ensureRow('contact_channels', [
                'type' => 'email', 'value' => $email,
            ], [
                'label' => sprintf('[Draft] Channel %02d', $i), 'region' => null,
                'display_value' => $email, 'message_template' => null,
                'icon' => 'mail', 'colour' => '#216D61', 'availability_text' => null,
                'is_default' => false, 'is_active' => false,
                'track_clicks' => false, 'sort_order' => $i,
            ]);
            if ($channelId && $homeId) {
                $this->ensureRow('contact_targets', [
                    'contact_channel_id' => $channelId,
                    'targetable_type' => Page::class, 'targetable_id' => $homeId,
                ]);
            }
        }
        // The lead form supports only admin-configured SELECT questions here.
        $selects = [
            'property_type_extra' => ['Property category', ['HDB', 'Condo', 'Landed', 'Commercial']],
            'room_count' => ['Number of rooms', ['1', '2', '3', '4', '5+']],
            'work_scope' => ['Project scope', ['Painting', 'Plastering', 'Hacking', 'Other']],
            'property_status' => ['Property condition', ['Occupied', 'Vacant', 'Under renovation']],
            'site_access' => ['Access type', ['Lift', 'Stairs', 'Ground floor']],
            'preferred_contact' => ['Preferred contact', ['WhatsApp', 'Phone', 'Email']],
            'wall_condition' => ['Wall condition', ['Good', 'Uneven', 'Peeling', 'Unsure']],
            'paint_finish' => ['Preferred finish', ['Matte', 'Eggshell', 'Satin', 'Unsure']],
            'ceiling_included' => ['Include ceiling', ['Yes', 'No', 'Unsure']],
            'quotation_type' => ['Quotation preference', ['On-site', 'Remote first', 'Unsure']],
        ];
        foreach ($selects as $key => [$label, $options]) {
            $this->ensureRow('lead_form_fields', ['field_key' => $key], [
                'label' => $label, 'placeholder' => 'Choose an option',
                'options' => $this->json($options), 'is_required' => false,
                'is_active' => false, 'sort_order' => 100,
            ]);
        }

        // 10 unique FAQs for general consultation, all OFF by default.
        $generalQuestions = [
            'How are room measurements assessed?', 'Can you quote from photos?',
            'What should be prepared before a site visit?', 'What materials should be confirmed?',
            'Is there a minimum work scope?', 'Do you include surface protection?',
            'Are colour samples available?', 'What affects drying time?',
            'How are change requests handled?', 'What information is needed for a quote?',
        ];
        foreach ($generalQuestions as $i => $question) {
            $faqId = $this->ensureRow('faqs', ['question' => '[Draft] '.$question], [
                'answer' => '<p>Draft only. Your business must provide and approve its own answer.</p>',
                'is_active' => false,
            ]);
            if ($faqId && $homeId) {
                $this->ensurePivot('faqables', [
                    'faq_id' => $faqId, 'faqable_type' => Page::class, 'faqable_id' => $homeId,
                ], ['sort_order' => 100 + $i]);
            }
        }

        // One page-section and an SEO meta per page; no invented active public content.
        $heroId = $this->value('section_definitions', ['key' => 'hero'], 'id');
        if (Schema::hasTable('pages')) {
            foreach (DB::table('pages')->select('id', 'title')->limit(50)->get() as $page) {
                if ($heroId && (int) $page->id !== (int) $homeId) {
                    $this->ensureRow('page_sections', [
                        'page_id' => $page->id, 'section_definition_id' => $heroId,
                        'sort_order' => 0,
                    ], [
                        'heading' => '[Draft] '.$page->title, 'subheading' => 'Update this section',
                        'payload' => $this->json([]), 'theme' => 'default',
                        'is_active' => false, 'starts_at' => null, 'ends_at' => null,
                    ]);
                }
                $this->ensureRow('seo_metas', [
                    'seoable_type' => Page::class, 'seoable_id' => $page->id,
                ], [
                    'meta_title' => $page->title, 'meta_description' => 'Draft SEO metadata',
                    'canonical_url' => null, 'robots' => 'noindex,follow',
                    'og_title' => null, 'og_description' => null, 'schema_overrides' => null,
                    'include_in_sitemap' => false, 'sitemap_priority' => null,
                    'sitemap_changefreq' => 'monthly',
                ]);
            }
        }
        for ($i = 1; $i <= 10; $i++) {
            $this->ensureRow('redirects', ['from_path' => '/draft-old-page-'.$i], [
                'to_url' => '/', 'status_code' => 301, 'hits' => 0,
                'is_active' => false, 'last_hit_at' => null,
            ]);
        }
        // Existing project supports five real tracking providers, not ten invented vendors.
        foreach (['meta_pixel', 'meta_capi', 'ga4', 'gtm', 'tiktok'] as $provider) {
            $providerId = $this->value('tracking_providers', ['provider' => $provider], 'id');
            if (! $providerId) {
                continue;
            }
            $this->ensureRow('tracking_event_rules', [
                'tracking_provider_id' => $providerId, 'internal_event' => 'lead_submit',
            ], [
                'provider_event' => 'Lead', 'parameter_map' => $this->json([]),
                'requires_marketing_consent' => true, 'is_enabled' => false,
            ]);
        }

        // Nine further campaign drafts plus existing starter-campaign = ten.
        for ($i = 1; $i <= 9; $i++) {
            $title = sprintf('[Draft] Campaign %02d', $i);
            $campaignId = $this->ensureRow('campaigns', ['slug' => sprintf('draft-campaign-%02d', $i)], [
                'title' => $title, 'custom_route' => null,
                'summary' => '<p>Inactive planning example; do not publish as a real offer.</p>',
                'status' => 'draft', 'hero_config' => $this->json(['heading' => $title]),
                'published_at' => null, 'starts_at' => null, 'ends_at' => null,
                'created_by' => $adminId, 'updated_by' => $adminId,
            ]);
            if ($campaignId) {
                $this->ensureRow('campaign_sections', [
                    'campaign_id' => $campaignId, 'section_key' => 'hero',
                ], [
                    'heading' => $title, 'subheading' => 'Draft campaign section',
                    'payload' => $this->json([]), 'is_enabled' => false, 'sort_order' => 0,
                ]);
            }
        }
    }

}
