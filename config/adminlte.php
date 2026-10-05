<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title'                                   => 'AdminLTE 3',
    'title_prefix'                            => '',
    'title_postfix'                           => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only'                            => false,
    'use_full_favicon'                        => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts'                            => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo'                                    => '<b>Admin</b>LTE',
    'logo_img'                                => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class'                          => 'brand-image img-circle elevation-3',
    'logo_img_xl'                             => null,
    'logo_img_xl_class'                       => 'brand-image-xs',
    'logo_img_alt'                            => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo'                               => [
        'enabled' => false,
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'Auth Logo',
            'class'  => '',
            'width'  => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader'                               => [
        'enabled' => true,
        'mode'    => 'fullscreen',
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width'  => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled'                        => true,
    'usermenu_header'                         => false,
    'usermenu_header_class'                   => 'bg-primary',
    'usermenu_image'                          => true,
    'usermenu_desc'                           => false,
    'usermenu_profile_url'                    => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav'                           => null,
    'layout_boxed'                            => null,
    'layout_fixed_sidebar'                    => null,
    'layout_fixed_navbar'                     => null,
    'layout_fixed_footer'                     => null,
    'layout_dark_mode'                        => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card'                       => 'card-outline card-primary',
    'classes_auth_header'                     => '',
    'classes_auth_body'                       => '',
    'classes_auth_footer'                     => '',
    'classes_auth_icon'                       => '',
    'classes_auth_btn'                        => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body'                            => '',
    'classes_brand'                           => '',
    'classes_brand_text'                      => '',
    'classes_content_wrapper'                 => '',
    'classes_content_header'                  => '',
    'classes_content'                         => '',
    'classes_sidebar'                         => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav'                     => '',
    'classes_topnav'                          => 'navbar-white navbar-light',
    'classes_topnav_nav'                      => 'navbar-expand',
    'classes_topnav_container'                => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini'                            => 'lg',
    'sidebar_collapse'                        => false,
    'sidebar_collapse_auto_size'              => false,
    'sidebar_collapse_remember'               => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme'                 => 'os-theme-light',
    'sidebar_scrollbar_auto_hide'             => 'l',
    'sidebar_nav_accordion'                   => true,
    'sidebar_nav_animation_speed'             => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar'                           => false,
    'right_sidebar_icon'                      => 'fas fa-cogs',
    'right_sidebar_theme'                     => 'dark',
    'right_sidebar_slide'                     => true,
    'right_sidebar_push'                      => true,
    'right_sidebar_scrollbar_theme'           => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide'       => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url'                           => true,
    'dashboard_url'                           => 'admin.dashboard',
    'logout_url'                              => 'logout',
    'login_url'                               => 'login',
    'register_url'                            => null,
    'password_reset_url'                      => 'password.request',
    'password_email_url'                      => 'password.email',
    'profile_url'                             => false,
    'disable_darkmode_routes'                 => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling'                  => false,
    'laravel_css_path'                        => 'css/app.css',
    'laravel_js_path'                         => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu'                                    => [

        /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

        [
            'text'   => 'Dashboard',
            'route'  => 'admin.dashboard',
            'icon'   => 'fas fa-fw fa-tachometer-alt',
            'can'    => 'dashboard_view',
            'active' => ['admin', 'admin/dashboard'],
        ],

        [
            'header' => 'BLOG MANAGEMENT',
        ],

        [
            'text'    => 'Blog Manage',
            'icon'    => 'fas fa-fw fa-blog',
            'active'  => [
                'admin/categories*',
                'admin/posts*',
                'admin/contact-channels*',
                'admin/contact-targets*',
            ],

            'submenu' => [
                [
                    'text'   => 'Categories',
                    'route'  => 'admin.categories.index',
                    'icon'   => 'fas fa-fw fa-folder-open',
                    'can'    => 'blog_category_list',
                    'active' => ['admin/categories*'],
                ],
                [
                    'text'   => 'Posts',
                    'route'  => 'admin.posts.index',
                    'icon'   => 'fas fa-fw fa-newspaper',
                    'can'    => 'blog_post_list',
                    'active' => ['admin/posts*'],
                ],
                [
                    'text'   => 'Contact Channels',
                    'route'  => 'admin.contact_channels.index',
                    'icon'   => 'fas fa-fw fa-address-book',
                    'can'    => 'contact_channel_list',
                    'active' => ['admin/contact-channels*'],
                ],
                [
                    'text'   => 'Contact Targets',
                    'route'  => 'admin.contact_targets.index',
                    'icon'   => 'fas fa-fw fa-crosshairs',
                    'can'    => 'contact_target_list',
                    'active' => ['admin/contact-targets*'],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | CMS
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'CMS MANAGEMENT',
        ],
        [
            'text'    => 'CMS Pages',
            'icon'    => 'fas fa-fw fa-layer-group',
            'active'  => [
                'admin/pages*',
                'admin/section-definitions*',
                'admin/page-sections*',
                'admin/section-media*',
                'admin/faqs*',
            ],
            'submenu' => [
                [
                    'text'   => 'Pages',
                    'route'  => 'admin.pages.index',
                    'icon'   => 'far fa-fw fa-file-alt',
                    'can'    => 'page_list',
                    'active' => ['admin/pages*'],
                ],
                [
                    'text'   => 'Section Definitions',
                    'route'  => 'admin.section_definitions.index',
                    'icon'   => 'fas fa-fw fa-layer-group',
                    'can'    => 'section_definition_list',
                    'active' => ['admin/section-definitions*'],
                ],
                [
                    'text'   => 'Page Sections',
                    'route'  => 'admin.page_sections.index',
                    'icon'   => 'fas fa-fw fa-th-large',
                    'can'    => 'page_section_list',
                    'active' => ['admin/page-sections*'],
                ],
                [
                    'text'   => 'Section Media',
                    'route'  => 'admin.section_media.index',
                    'icon'   => 'far fa-fw fa-images',
                    'can'    => 'section_media_list',
                    'active' => ['admin/section-media*'],
                ],
                [
                    'text'   => 'FAQs',
                    'route'  => 'admin.faqs.index',
                    'icon'   => 'fas fa-fw fa-question-circle',
                    'can'    => 'faq_list',
                    'active' => ['admin/faqs*'],
                ],

            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Lead Management
        |--------------------------------------------------------------------------
        */

        // Keep both submenu items under LEAD MANAGEMENT.
        [
            'header' => 'LEAD MANAGEMENT',
        ],
        [
            'text'    => 'Leads & Booking',
            'icon'    => 'fas fa-fw fa-user-tag',
            'active'  => [
                'admin/leads*',
                'admin/lead-form-fields*',
            ],
            'submenu' => [
                [
                    'text'   => 'Leads',
                    'route'  => 'admin.leads.index',
                    'icon'   => 'fas fa-fw fa-user-tag',
                    'can'    => 'lead_list',
                    'active' => ['admin/leads*'],
                ],
                [
                    'text'   => 'Booking Form Fields',
                    'route'  => 'admin.lead_form_fields.index',
                    'icon'   => 'fas fa-fw fa-list-alt',
                    'can'    => 'lead_form_field_list',
                    'active' => ['admin/lead-form-fields*'],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */
        [
            'header' => 'SERVICES MANAGEMENT',
        ],
        [
            'text'    => 'Services & Locations',
            'icon'    => 'fas fa-fw fa-tools',
            'active'  => [
                'admin/services*',
                'admin/service-features*',
                'admin/locations*',
                'admin/pricing-packages*',
                'admin/pricing-items*',
                'admin/pricing-addons*',
                'admin/pricing-package-addons*',
            ],
            'submenu' => [
                [
                    'text'   => 'Services',
                    'route'  => 'admin.services.index',
                    'icon'   => 'fas fa-fw fa-tools',
                    'can'    => 'service_list',
                    'active' => ['admin/services*'],
                ],
                [
                    'text'   => 'Service Features',
                    'route'  => 'admin.service_features.index',
                    'icon'   => 'fas fa-fw fa-list-ul',
                    'can'    => 'service_feature_list',
                    'active' => ['admin/service-features*'],
                ],
                [
                    'text'   => 'Locations',
                    'route'  => 'admin.locations.index',
                    'icon'   => 'fas fa-fw fa-map-marker-alt',
                    'can'    => 'location_list',
                    'active' => ['admin/locations*'],
                ],
                [
                    'text'   => 'Pricing Packages',
                    'route'  => 'admin.pricing_packages.index',
                    'icon'   => 'fas fa-fw fa-tags',
                    'can'    => 'pricing_package_list',
                    'active' => ['admin/pricing-packages*'],
                ],

                [
                    'text'   => 'Pricing Items',
                    'route'  => 'admin.pricing_items.index',
                    'icon'   => 'fas fa-fw fa-list-ol',
                    'can'    => 'pricing_item_list',
                    'active' => ['admin/pricing-items*'],
                ],
                [
                    'text'   => 'Pricing Add-ons',
                    'route'  => 'admin.pricing_addons.index',
                    'icon'   => 'fas fa-fw fa-puzzle-piece',
                    'can'    => 'pricing_addon_list',
                    'active' => ['admin/pricing-addons*'],
                ],
                [
                    'text'   => 'Package Add-on Mapping',
                    'route'  => 'admin.pricing_package_addons.index',
                    'icon'   => 'fas fa-fw fa-link',
                    'can'    => 'pricing_package_addon_list',
                    'active' => ['admin/pricing-package-addons*'],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Gallery Management
        |--------------------------------------------------------------------------
        */

        ['header' => 'GALLERY MANAGEMENT'],
        [
            'text'    => 'Gallery Manage',
            'icon'    => 'fas fa-fw fa-images',
            'active'  => [
                'admin/galleries*',
                'admin/gallery-items*',
                'admin/videos*',
                'admin/testimonials*',
            ],
            'submenu' => [
                [
                    'text'   => 'Galleries',
                    'route'  => 'admin.galleries.index',
                    'icon'   => 'far fa-fw fa-image',
                    'can'    => 'gallery_list',
                    'active' => ['admin/galleries*'],
                ],
                [
                    'text'   => 'Gallery Items',
                    'route'  => 'admin.gallery_items.index',
                    'icon'   => 'fas fa-fw fa-photo-video',
                    'can'    => 'gallery_item_list',
                    'active' => ['admin/gallery-items*'],
                ],
                [
                    'text'   => 'Videos',
                    'route'  => 'admin.videos.index',
                    'icon'   => 'fas fa-fw fa-video',
                    'can'    => 'video_list',
                    'active' => ['admin/videos*'],
                ],

                [
                    'text'   => 'Testimonials',
                    'route'  => 'admin.testimonials.index',
                    'icon'   => 'fas fa-fw fa-comment-dots',
                    'can'    => 'testimonial_list',
                    'active' => ['admin/testimonials*'],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Site Configuration
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'SITE CONFIGURATION',
        ],

        [
            'text'   => 'Site Settings',
            'route'  => 'admin.settings.index',
            'icon'   => 'fas fa-fw fa-cogs',
            'can'    => 'site_setting_view',
            'active' => ['admin/settings*'],
        ],

        [
            'text'   => 'Menus',
            'route'  => 'admin.menus.index',
            'icon'   => 'fas fa-fw fa-sitemap',
            'can'    => 'menu_list',
            'active' => ['admin/menus*'],
        ],

        [
            'text'   => 'Media Management',
            'route'  => 'admin.media.index',
            'icon'   => 'fas fa-fw fa-photo-video',
            'can'    => 'media_list',
            'active' => ['admin/media-management*'],
        ],


        /*
        |--------------------------------------------------------------------------
        | SEO & Tracking
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'SEO & TRACKING',
        ],

        [
            'text'    => 'SEO & Tracking',
            'icon'    => 'fas fa-fw fa-chart-area',
            'active'  => [
                'admin/seo*',
                'admin/redirects*',
                'admin/tracking*',
                'admin/tracking-event-rules*',
            ],
            'submenu' => [
                [
                    'text'   => 'SEO Metadata',
                    'route'  => 'admin.seo.index',
                    'icon'   => 'fas fa-fw fa-search',
                    'can'    => 'seo_meta_list',
                    'active' => ['admin/seo*'],
                ],
                [
                    'text'   => 'Redirects',
                    'route'  => 'admin.redirects.index',
                    'icon'   => 'fas fa-fw fa-random',
                    'can'    => 'redirect_list',
                    'active' => ['admin/redirects*'],
                ],
                [
                    'text'   => 'Tracking Providers',
                    'route'  => 'admin.tracking.index',
                    'icon'   => 'fas fa-fw fa-chart-line',
                    'can'    => 'tracking_provider_list',
                    'active' => ['admin/tracking', 'admin/tracking/*'],
                ],
                [
                    'text'   => 'Tracking Event Rules',
                    'route'  => 'admin.tracking_event_rules.index',
                    'icon'   => 'fas fa-fw fa-project-diagram',
                    'can'    => 'tracking_event_rule_list',
                    'active' => ['admin/tracking-event-rules*'],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'ACCOUNT',
        ],

        [
            'text'   => 'Profile',
            'route'  => 'admin.profile',
            'icon'   => 'fas fa-fw fa-user-circle',
            'active' => ['admin/profile*'],
        ],

        [
            'text'   => 'Change Password',
            'route'  => 'admin.password',
            'icon'   => 'fas fa-fw fa-key',
            'active' => ['admin/password*'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Access Control
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'ACCESS CONTROL',
        ],

        [
            'text'   => 'Admins',
            'route'  => 'admin.admins.index',
            'icon'   => 'fas fa-fw fa-user-shield',
            'can'    => 'admin_list',
            'active' => ['admin/admins*'],
        ],

        [
            'text'   => 'Roles',
            'route'  => 'admin.roles.index',
            'icon'   => 'fas fa-fw fa-user-tag',
            'can'    => 'role_list',
            'active' => ['admin/roles*'],
        ],

        [
            'text'   => 'Permissions',
            'route'  => 'admin.permissions.index',
            'icon'   => 'fas fa-fw fa-key',
            'can'    => 'permission_list',
            'active' => ['admin/permissions*'],
        ],

        /*
        |--------------------------------------------------------------------------
        | System
        |--------------------------------------------------------------------------
        */
        [
            'header' => 'SYSTEM TOOLS',
            'can'    => 'system_tools_manage',
        ],
        [
            'text'   => 'System Commands',
            'route'  => 'command.index',
            'icon'   => 'fas fa-fw fa-terminal',
            'active' => ['admin/command*'],
            'can'    => 'system_tools_manage',
        ],
    ],

/*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters'                                 => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

/*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins'                                 => [

        'Datatables'  => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.13.11/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.13.11/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],

        'Select2'     => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                ],
            ],
        ],

        'Chartjs'     => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js',
                ],
            ],
        ],

        /*
    |--------------------------------------------------------------------------
    | Global SweetAlert2
    |--------------------------------------------------------------------------
    |
    | active=true থাকার কারণে adminlte::page extend করা সব admin page-এ
    | SweetAlert2 automatically load হবে।
    |
    */

        'Sweetalert2' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
                ],
            ],
        ],

        /*
    |--------------------------------------------------------------------------
    | Global TinyMCE
    |--------------------------------------------------------------------------
    |
    | Script globally load হবে। তবে শুধু .tinymce-editor class যুক্ত
    | textarea editor হিসেবে initialize হবে।
    |
    */

        'TinyMCE'     => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js',
                ],
            ],
        ],

        'Pace'        => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/pace.min.js',
                ],
            ],
        ],
    ],

/*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe'                                  => [
        'default_tab' => [
            'url'   => null,
            'title' => null,
        ],
        'buttons'     => [
            'close'           => true,
            'close_all'       => true,
            'close_all_other' => true,
            'scroll_left'     => true,
            'scroll_right'    => true,
            'fullscreen'      => true,
        ],
        'options'     => [
            'loading_screen'    => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items'  => true,
        ],
    ],

/*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire'                                => false,
];
