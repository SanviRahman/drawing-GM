<?php
namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions for the admin guard.
     *
     * Only permissions used by the application should be added here.
     *
     * @var array<string, array<int, string>>
     */
    private const ADMIN_PERMISSION_GROUPS = [
        'Dashboard'           => [
            'dashboard_view',
        ],

        'System Tools'        => [
            'system_tools_manage',
            'cache_clear',
            'queue_manage',
            'backup_manage',
        ],

        'Site Settings'       => [
            'site_setting_view',
            'site_setting_update',
            'site_setting_create',
            'site_setting_delete',
            'site_setting_trash',
            'site_setting_restore',
            'site_setting_force_delete',
        ],

        'Header Settings'     => [
            'header_setting_view',
            'header_setting_update',
        ],

        'Header Menu'         => [
            'header_menu_list',
            'header_menu_view',
            'header_menu_create',
            'header_menu_update',
            'header_menu_delete',
            'header_menu_toggle',
            'header_menu_reorder',
        ],

        'Footer Settings'     => [
            'footer_setting_view',
            'footer_setting_update',
        ],

        'Footer Links'        => [
            'footer_link_list',
            'footer_link_view',
            'footer_link_create',
            'footer_link_update',
            'footer_link_delete',
            'footer_link_toggle',
            'footer_link_reorder',
        ],

        'Menus'               => [
            'menu_list',
            'menu_view',
            'menu_create',
            'menu_update',
            'menu_delete',
            'menu_trash',
            'menu_restore',
            'menu_force_delete',
        ],

        'Pages'               => [
            'page_list',
            'page_view',
            'page_create',
            'page_update',
            'page_delete',
            'page_trash',
            'page_restore',
            'page_force_delete',
            'page_publish',
            'page_unpublish',
            'page_preview',
            'page_duplicate',
        ],

        'Page Sections'       => [
            'page_section_list',
            'page_section_view',
            'page_section_create',
            'page_section_update',
            'page_section_delete',
            'page_section_toggle',
            'page_section_reorder',
            'page_section_duplicate',
            'page_section_trash',
            'page_section_restore',
            'page_section_force_delete',
        ],

        'Section Media'       => [
            'section_media_list',
            'section_media_view',
            'section_media_create',
            'section_media_update',
            'section_media_delete',
            'section_media_trash',
            'section_media_restore',
            'section_media_force_delete',
        ],
        'Section Definitions' => [
            'section_definition_list',
            'section_definition_view',
            'section_definition_create',
            'section_definition_update',
            'section_definition_delete',
            'section_definition_trash',
            'section_definition_restore',
            'section_definition_force_delete',
            'section_definition_toggle',
        ],

        'Services'            => [
            'service_list',
            'service_view',
            'service_create',
            'service_update',
            'service_delete',
            'service_trash',
            'service_restore',
            'service_force_delete',
            'service_publish',
            'service_unpublish',
            'service_preview',
        ],

        'Service Features'    => [
            'service_feature_list',
            'service_feature_view',
            'service_feature_create',
            'service_feature_update',
            'service_feature_delete',
            'service_feature_toggle',
            'service_feature_reorder',
        ],

        'Locations'           => [
            'location_list',
            'location_view',
            'location_create',
            'location_update',
            'location_delete',
            'location_trash',
            'location_restore',
            'location_force_delete',
            'location_publish',
            'location_unpublish',
            'location_preview',
        ],

        'Pricing Packages'    => [
            'pricing_package_list',
            'pricing_package_view',
            'pricing_package_create',
            'pricing_package_update',
            'pricing_package_delete',
            'pricing_package_toggle',
            'pricing_package_reorder',
            'pricing_package_set_featured',
        ],

        'Pricing Items'       => [
            'pricing_item_list',
            'pricing_item_view',
            'pricing_item_create',
            'pricing_item_update',
            'pricing_item_delete',
            'pricing_item_toggle',
            'pricing_item_reorder',
        ],

        'Pricing Add-ons'     => [
            'pricing_addon_list',
            'pricing_addon_view',
            'pricing_addon_create',
            'pricing_addon_update',
            'pricing_addon_delete',
            'pricing_addon_toggle',
            'pricing_addon_reorder',
        ],

        'Campaigns'           => [
            'campaign_list',
            'campaign_view',
            'campaign_create',
            'campaign_update',
            'campaign_delete',
            'campaign_trash',
            'campaign_restore',
            'campaign_force_delete',
            'campaign_publish',
            'campaign_unpublish',
            'campaign_preview',
            'campaign_duplicate',
            'campaign_set_default',
        ],

        'Campaign Sections'   => [
            'campaign_section_list',
            'campaign_section_view',
            'campaign_section_create',
            'campaign_section_update',
            'campaign_section_delete',
            'campaign_section_toggle',
            'campaign_section_reorder',
            'campaign_section_duplicate',
        ],

        'Media'               => [
            'media_list',
            'media_view',
            'media_upload',
            'media_update',
            'media_delete',
            'media_trash',
            'media_restore',
            'media_force_delete',
            'media_download',
            'media_replace',
            'media_regenerate_conversion',
        ],

        'Galleries'           => [
            'gallery_list',
            'gallery_view',
            'gallery_create',
            'gallery_update',
            'gallery_delete',
            'gallery_toggle',
            'gallery_reorder',
        ],

        'Gallery Items'       => [
            'gallery_item_list',
            'gallery_item_view',
            'gallery_item_create',
            'gallery_item_update',
            'gallery_item_delete',
            'gallery_item_toggle',
            'gallery_item_reorder',
        ],

        'Videos'              => [
            'video_list',
            'video_view',
            'video_create',
            'video_update',
            'video_delete',
            'video_upload',
            'video_embed',
            'video_process',
            'video_toggle',
            'video_reorder',
        ],

        'Testimonials'        => [
            'testimonial_list',
            'testimonial_view',
            'testimonial_create',
            'testimonial_update',
            'testimonial_delete',
            'testimonial_toggle',
            'testimonial_reorder',
            'testimonial_set_featured',
        ],

        'Reviews'             => [
            'review_list',
            'review_view',
            'review_create',
            'review_update',
            'review_delete',
            'review_publish',
            'review_unpublish',
            'review_set_featured',
        ],

        'FAQs'                => [
            'faq_list',
            'faq_view',
            'faq_create',
            'faq_update',
            'faq_delete',
            'faq_toggle',
            'faq_reorder',
        ],

        'Blog Categories'     => [
            'blog_category_list',
            'blog_category_view',
            'blog_category_create',
            'blog_category_update',
            'blog_category_delete',
            'blog_category_toggle',
            'blog_category_reorder',
        ],

        'Blog Posts'          => [
            'blog_post_list',
            'blog_post_view',
            'blog_post_create',
            'blog_post_update',
            'blog_post_delete',
            'blog_post_trash',
            'blog_post_restore',
            'blog_post_force_delete',
            'blog_post_publish',
            'blog_post_unpublish',
            'blog_post_preview',
            'blog_post_duplicate',
        ],

        'Contact Channels'    => [
            'contact_channel_list',
            'contact_channel_view',
            'contact_channel_create',
            'contact_channel_update',
            'contact_channel_delete',
            'contact_channel_toggle',
            'contact_channel_reorder',
            'contact_channel_set_default',
        ],

        'Contact Messages'    => [
            'contact_message_list',
            'contact_message_view',
            'contact_message_delete',
            'contact_message_mark_read',
            'contact_message_mark_unread',
            'contact_message_reply',
        ],

        'Leads'               => [
            'lead_list',
            'lead_view',
            'lead_create',
            'lead_update',
            'lead_delete',
            'lead_trash',
            'lead_restore',
            'lead_force_delete',
            'lead_assign',
            'lead_change_status',
            'lead_export',
            'lead_internal_note',
            'lead_public_note',
            'lead_attachment_download',
        ],

        'SEO Settings'        => [
            'seo_setting_view',
            'seo_setting_update',
            'seo_sitemap_manage',
            'seo_schema_manage',
            'seo_social_manage',
        ],

        'Redirects'           => [
            'redirect_list',
            'redirect_view',
            'redirect_create',
            'redirect_update',
            'redirect_delete',
            'redirect_toggle',
            'redirect_import',
            'redirect_export',
        ],

        'Tracking Settings'   => [
            'tracking_setting_view',
            'tracking_setting_update',
            'tracking_event_manage',
            'tracking_test_event',
            'tracking_consent_manage',
        ],

        'Meta Pixel'          => [
            'meta_pixel_view',
            'meta_pixel_update',
            'meta_pixel_toggle',
            'meta_pixel_test',
            'meta_capi_manage',
        ],

        'Admins'              => [
            'admin_list',
            'admin_view',
            'admin_create',
            'admin_update',
            'admin_delete',
            'admin_trash',
            'admin_restore',
            'admin_force_delete',
            'admin_change_status',
            'admin_assign_role',
            'admin_reset_password',
        ],

        'Users'               => [
            'user_list',
            'user_view',
            'user_create',
            'user_update',
            'user_delete',
            'user_change_status',
            'user_view_enquiries',
            'user_reset_password',
        ],

        'Roles'               => [
            'role_list',
            'role_view',
            'role_create',
            'role_update',
            'role_delete',
            'role_trash',
            'role_restore',
            'role_force_delete',
            'role_assign_permission',
        ],

        'Permissions'         => [
            'permission_list',
            'permission_view',
            'permission_create',
            'permission_update',
            'permission_delete',
            'permission_trash',
            'permission_restore',
            'permission_force_delete',
        ],

        'Audit Logs'          => [
            'audit_log_list',
            'audit_log_view',
            'audit_log_export',
        ],
    ];

    /**
     * Permissions for the user/web guard.
     *
     * @var array<string, array<int, string>>
     */
    private const USER_PERMISSION_GROUPS = [
        'User Panel' => [
            'profile_view',
            'profile_update',
            'password_update',

            'enquiry_create',
            'enquiry_own_list',
            'enquiry_own_view',
            'enquiry_own_attachment_upload',
            'enquiry_own_attachment_download',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);

        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function (): void {
            $adminRole = Role::query()->firstOrCreate([
                'name'       => 'admin',
                'guard_name' => 'admin',
            ]);

            $userRole = Role::query()->firstOrCreate([
                'name'       => 'user',
                'guard_name' => 'web',
            ]);

            $adminPermissions = $this->seedPermissionGroups(
                groups: self::ADMIN_PERMISSION_GROUPS,
                guardName: 'admin',
            );

            $userPermissions = $this->seedPermissionGroups(
                groups: self::USER_PERMISSION_GROUPS,
                guardName: 'web',
            );

            $adminRole->syncPermissions($adminPermissions);
            $userRole->syncPermissions($userPermissions);

            $admin = $this->createInitialAdmin();

            $admin->syncRoles([$adminRole]);
        });

        $permissionRegistrar->forgetCachedPermissions();
    }

    /**
     * Create or update grouped permissions.
     *
     * @param array<string, array<int, string>> $groups
     *
     * @return array<int, Permission>
     */
    private function seedPermissionGroups(
        array $groups,
        string $guardName,
    ): array {
        $permissions = [];

        foreach ($groups as $groupName => $permissionNames) {
            foreach ($permissionNames as $permissionName) {
                $permissions[] = Permission::query()->updateOrCreate(
                    [
                        'name'       => $permissionName,
                        'guard_name' => $guardName,
                    ],
                    [
                        'group_name' => $groupName,
                    ],
                );
            }
        }

        return $permissions;
    }

    /**
     * Create the initial administrator.
     *
     * Running the seeder again will update basic profile information,
     * but it will not reset the existing password.
     */
    private function createInitialAdmin(): Admin
    {
        $admin = Admin::query()->firstOrNew([
            'email' => 'admin@gmail.com',
        ]);

        $isNewAdmin = ! $admin->exists;

        $admin->forceFill([
            'name'     => 'Super Admin',
            'username' => 'admin',
            'phone'    => '01700000000',
            'status'   => true,
        ]);

        if ($isNewAdmin) {
            $admin->password = Hash::make(
                'password123'
            );
        }

        $admin->save();

        return $admin;
    }
}
