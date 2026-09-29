# Laravel Folder Structure

## 1. Principle

Use Laravel conventions first. Group code by responsibility and domain without turning the project into a custom framework. Authorization uses `spatie/laravel-permission`; model-associated files and conversions use `spatie/laravel-medialibrary`. Controllers remain thin; validation lives in Form Requests; authorization in policies; reusable business operations in Actions/Services; slow work in Jobs.

## 2. Current + planned structure

```text
app/
├── Actions/
│   ├── Auth/
│   ├── Content/
│   │   ├── PublishContent.php
│   │   └── ReorderSections.php
│   ├── Contacts/
│   │   └── ResolveContactChannels.php
│   ├── Hero/
│   │   └── ResolveHeroMedia.php
│   ├── Leads/
│   │   ├── CreateLead.php
│   │   └── UpdateLeadStatus.php
│   ├── Media/
│   │   ├── StoreMedia.php
│   │   └── DeleteMedia.php
│   └── Seo/
│       └── CreateRedirectForSlugChange.php
├── Console/
│   └── Commands/
├── DTOs/
│   ├── ContactChannelData.php
│   ├── LeadData.php
│   └── SeoMetadataData.php
├── Enums/
│   ├── ContactChannelType.php
│   ├── ContentStatus.php
│   ├── LeadStatus.php
│   ├── MediaStatus.php
│   ├── PriceType.php
│   ├── TrackingProvider.php
│   └── VideoSourceType.php
├── Events/
│   ├── ContentPublished.php
│   ├── LeadCreated.php
│   ├── LeadStatusChanged.php
│   └── SettingsUpdated.php
├── Http/
│   ├── Controllers/
│   │   ├── Backoffice/Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── SettingController.php
│   │   │   ├── MenuController.php
│   │   │   ├── PageController.php
│   │   │   ├── PageSectionController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── LocationController.php
│   │   │   ├── PricingPackageController.php
│   │   │   ├── PricingAddonController.php
│   │   │   ├── MediaController.php
│   │   │   ├── GalleryController.php
│   │   │   ├── VideoController.php
│   │   │   ├── TestimonialController.php
│   │   │   ├── FaqController.php
│   │   │   ├── PostController.php
│   │   │   ├── ContactChannelController.php
│   │   │   ├── LeadController.php
│   │   │   ├── SeoController.php
│   │   │   ├── RedirectController.php
│   │   │   └── TrackingController.php
│   │   ├── Account/
│   │   │   ├── ProfileController.php
│   │   │   └── EnquiryController.php
│   │   └── Frontend/
│   │       ├── HomeController.php
│   │       ├── PageController.php
│   │       ├── ServiceController.php
│   │       ├── LocationController.php
│   │       ├── BlogController.php
│   │       ├── QuoteController.php
│   │       ├── ContactRedirectController.php
│   │       └── SitemapController.php
│   ├── Middleware/
│   │   ├── LteContextSwitcher.php      # implemented
│   │   ├── EnsureActiveUser.php        # planned
│   │   └── ApplyRedirects.php
│   ├── Requests/
│   │   ├── Backoffice/Admin/
│   │   │   ├── StorePageRequest.php
│   │   │   ├── StorePageSectionRequest.php
│   │   │   ├── StoreServiceRequest.php
│   │   │   ├── StoreLocationRequest.php
│   │   │   ├── StorePricingPackageRequest.php
│   │   │   ├── StoreMediaRequest.php
│   │   │   ├── StoreVideoRequest.php
│   │   │   ├── StoreContactChannelRequest.php
│   │   │   ├── UpdateTrackingProviderRequest.php
│   │   │   └── UpdateLeadRequest.php
│   │   ├── Account/
│   │   └── Frontend/
│   │       └── StoreQuoteRequest.php
│   └── Resources/
│       ├── ContactChannelResource.php
│       └── LeadResource.php
├── Jobs/
│   ├── GenerateImageVariants.php
│   ├── ProcessUploadedVideo.php
│   ├── SendLeadNotifications.php
│   ├── DispatchMetaConversion.php
│   └── WarmPublicContentCache.php
├── Listeners/
│   ├── InvalidateContentCache.php
│   ├── QueueLeadNotifications.php
│   └── RecordLeadStatusHistory.php
├── Models/
│   ├── Admin.php                 # implemented: admin guard, roles, avatars
│   ├── User.php                  # implemented: web/customer foundation
│   ├── Role.php                  # only if extending Spatie's Role model
│   ├── Permission.php            # only if extending Spatie's Permission model
│   ├── SiteSetting.php
│   ├── Menu.php
│   ├── MenuItem.php
│   ├── Page.php
│   ├── SectionDefinition.php
│   ├── PageSection.php
│   ├── Media.php                 # extends Spatie Media model only if customization is needed
│   ├── Service.php
│   ├── ServiceFeature.php
│   ├── Location.php
│   ├── PricingPackage.php
│   ├── PricingItem.php
│   ├── PricingAddon.php
│   ├── Gallery.php
│   ├── GalleryItem.php
│   ├── Video.php
│   ├── Testimonial.php
│   ├── Faq.php
│   ├── Category.php
│   ├── Post.php
│   ├── ContactChannel.php
│   ├── Lead.php
│   ├── LeadStatusHistory.php
│   ├── LeadNote.php
│   ├── SeoMeta.php
│   ├── Redirect.php
│   ├── TrackingProvider.php
│   ├── TrackingEventRule.php
│   └── AuditLog.php
├── Notifications/
│   ├── NewLeadAdminNotification.php
│   └── LeadReceivedUserNotification.php
├── Observers/
│   ├── MenuObserver.php
│   ├── PageObserver.php
│   ├── ServiceObserver.php
│   └── SiteSettingObserver.php
├── Policies/
│   ├── LeadPolicy.php
│   ├── MediaPolicy.php
│   ├── PagePolicy.php
│   └── UserPolicy.php
├── Providers/
├── Rules/
│   ├── E164Phone.php
│   ├── SafeEmbedUrl.php
│   ├── ValidTrackingIdentifier.php
│   └── ValidUploadContent.php
├── Services/
│   ├── AdminAvatarService.php          # implemented
│   ├── SiteSettingMediaService.php     # implemented
│   ├── Content/
│   │   ├── HeroResolver.php
│   │   ├── PageSectionRegistry.php
│   │   └── PublishedContentService.php
│   ├── Media/
│   │   ├── ImageVariantService.php
│   │   ├── MediaStorageService.php
│   │   └── VideoProcessingService.php
│   ├── Seo/
│   │   ├── SeoMetadataBuilder.php
│   │   ├── SchemaMarkupBuilder.php
│   │   └── SitemapService.php
│   ├── Tracking/
│   │   ├── TrackingManager.php
│   │   ├── Contracts/TrackingAdapter.php
│   │   ├── MetaPixelAdapter.php
│   │   ├── MetaConversionsApiAdapter.php
│   │   ├── GoogleAnalyticsAdapter.php
│   │   └── TikTokPixelAdapter.php
│   └── WhatsApp/
│       └── WhatsAppUrlBuilder.php
└── View/
    ├── Components/
    │   ├── Site/
    │   ├── Sections/
    │   └── Admin/
    └── Composers/
        ├── HeaderComposer.php
        └── FooterComposer.php

bootstrap/
config/
├── content.php
├── media.php
├── services.php
└── tracking.php

database/
├── factories/
├── migrations/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RolePermissionSeeder.php   # implemented
│   └── SectionDefinitionSeeder.php # planned with Page module
└── testing/

resources/
├── css/
│   ├── app.css
│   ├── admin.css
│   └── components/
├── js/
│   ├── app.js
│   ├── admin.js
│   └── components/
│       ├── contact-widget.js
│       ├── back-to-top.js
│       ├── mobile-menu.js
│       ├── faq.js
│       ├── slider.js
│       └── before-after.js
└── views/
    ├── components/
    │   ├── site/
    │   │   ├── header.blade.php
    │   │   ├── hero.blade.php
    │   │   ├── footer.blade.php
    │   │   ├── floating-contact.blade.php
    │   │   ├── whatsapp-list.blade.php
    │   │   ├── back-to-top.blade.php
    │   │   ├── seo.blade.php
    │   │   └── consent-banner.blade.php
    │   ├── sections/
    │   │   ├── hero.blade.php
    │   │   ├── rich-text.blade.php
    │   │   ├── benefit-grid.blade.php
    │   │   ├── service-carousel.blade.php
    │   │   ├── pricing.blade.php
    │   │   ├── gallery.blade.php
    │   │   ├── before-after.blade.php
    │   │   ├── video-gallery.blade.php
    │   │   ├── testimonials.blade.php
    │   │   ├── whatsapp-reviews.blade.php
    │   │   ├── paint-calculator.blade.php
    │   │   ├── faq.blade.php
    │   │   ├── cta.blade.php
    │   │   ├── contact-form.blade.php
    │   │   ├── safe-embed.blade.php
    │   │   └── spacer.blade.php
    │   └── ui/
    ├── layouts/
    │   ├── app.blade.php
    │   ├── account.blade.php
    │   └── admin.blade.php
    ├── frontend/
    │   ├── home.blade.php
    │   ├── pages/show.blade.php
    │   ├── services/index.blade.php
    │   ├── services/show.blade.php
    │   ├── locations/show.blade.php
    │   ├── blog/index.blade.php
    │   ├── blog/show.blade.php
    │   ├── quote/create.blade.php
    │   └── sitemap.blade.php
    ├── account/
    └── backoffice/admin/
        ├── dashboard/
        ├── settings/
        ├── menus/
        ├── pages/
        ├── services/
        ├── locations/
        ├── pricing/
        ├── media/
        ├── galleries/
        ├── videos/
        ├── testimonials/
        ├── faqs/
        ├── posts/
        ├── contacts/
        ├── leads/
        ├── seo/
        ├── redirects/
        └── tracking/

routes/
├── web.php          # implemented; mounts /admin + admin auth context
├── admin.php        # implemented; auth:admin backoffice routes
├── command.php      # implemented; protected system tools
├── console.php
├── account.php      # planned when customer portal starts
└── api.php          # planned only for real JSON endpoints

storage/
├── app/public/
├── app/private/leads/
└── logs/

tests/
├── Feature/
│   ├── Admin/
│   ├── Account/
│   ├── Frontend/
│   │   ├── ContactWidgetTest.php
│   │   ├── LeadSubmissionTest.php
│   │   ├── PublishedContentTest.php
│   │   └── SitemapTest.php
│   └── Security/
└── Unit/
    ├── Contacts/
    ├── Seo/
    ├── Tracking/
    └── WhatsApp/
```

## 2.1 Spatie/auth integration conventions

- `Admin` is the implemented backoffice authenticatable model and uses guard `admin`, `HasRoles`, `SoftDeletes` and Spatie Media Library.
- `User` is the implemented customer/web authentication foundation. Add `HasRoles` for guard `web` when user-side granular permissions are actually enforced.
- Custom `Role` and `Permission` extend Spatie models and use soft deletes; `Permission` also uses `group_name`.
- Do not collapse Admin and User into one authenticatable model.
- Implemented media owners are `Admin.avatars` and `SiteSetting.site_logo`, `site_favicon`, `default_hero`.
- Planned Page, Service, Location, Post, GalleryItem, Video, Testimonial, Lead, Campaign and eligible CampaignSection/SeoMeta models implement `HasMedia` only where they directly own media.
- Register collections/conversions on the owner model or a focused trait/service.
- `ResolveHeroMedia` will read `hero_desktop`/`hero_mobile` from the current content entity and apply the documented fallback.
- Do not create a second generic media table beside Spatie's `media` table.
- Media Picker v1 reads authorized existing media and target services copy the selected source file into the target collection; the picker does not transfer source ownership.

### 2.2 Media Picker — next files to add

Media Picker is the next implementation milestone before Page/Section models. Keep it inside the current Backoffice/Admin namespace and view convention:

```text
app/Http/Controllers/Backoffice/Admin/MediaController.php
app/Services/Media/MediaPickerService.php              # optional focused query/serialization service
resources/views/backoffice/admin/media/picker.blade.php
resources/views/backoffice/admin/includes/media_picker.blade.php
```

`media_picker.blade.php` should expose the global `window.MediaPicker` API already expected by Admin and Site Setting forms. Version 1 browses/selects existing media only; direct form uploads remain the new-upload path. If the script grows, move implementation into a Vite-managed module while keeping the same public JavaScript contract.

## 3. Route file responsibilities

### `routes/web.php`

Implemented route entry point. It currently serves the public root and mounts `/admin` with `lte_context:admin`, Laravel UI auth routes (registration disabled), `command.php` and the named routes from `admin.php`.

### `routes/admin.php`

Implemented backoffice CRUD routes protected by `auth:admin`. New sensitive actions should additionally use granular `can:*`/Spatie permission checks and policies where record-level authorization is needed.

### `routes/command.php`

Implemented protected system-tool routes under `/admin/command`; they require `auth:admin` and `can:system_tools_manage`. Environment-restricted destructive commands must remain restricted.

### `routes/account.php` — planned

Create when the customer portal starts. Use `web` authentication plus ownership policies for profile/enquiries/private attachment access.

### `routes/api.php` — planned/optional

Add only for endpoints that truly require JSON, such as upload signing or tracking. Do not duplicate ordinary Blade CRUD endpoints.

## 4. Naming conventions

- Models: singular StudlyCase.
- Database tables: plural snake_case.
- Controllers: resource name + `Controller`.
- Form Requests: `Store...Request`, `Update...Request`.
- Actions: imperative verb phrase.
- Events: past tense.
- Jobs: imperative description of asynchronous work.
- Blade section file names match `section_definitions.key`.

## 5. Controller boundaries

Good controller flow:

1. Authorize.
2. Receive validated Form Request data.
3. Call Action/Service.
4. Redirect or return response.

Controllers must not contain image conversion, tracking-provider HTTP calls, large Eloquent query construction or inline authorization logic.

## 6. Frontend component ownership

- `header.blade.php`: dynamic branding/menu/CTA only.
- `hero.blade.php`: context-specific Home/Plastering/Hacking/Service/Page image, overlay, content and CTA using resolved Spatie media.
- `footer.blade.php`: dynamic footer groups and company data.
- `floating-contact.blade.php`: launcher state and channel controls.
- `whatsapp-list.blade.php`: database-provided WhatsApp options.
- `back-to-top.blade.php`: threshold and accessibility behaviour.
- `sections/*`: known, validated dynamic blocks. `whatsapp-reviews.blade.php` renders screenshot/social-proof testimonials and `paint-calculator.blade.php` renders validated calculator configuration/output; `safe-embed.blade.php` renders only allowlisted provider URLs/IDs; `spacer.blade.php` renders layout spacing from validated tokens.
- `seo.blade.php`: escaped metadata and generated JSON-LD.

## 7. Files not to create

Avoid:

- One `Helper.php` containing unrelated global functions.
- Controllers with hundreds of lines.
- Raw uploaded files inside `public/uploads` without Laravel storage control.
- Per-page duplicated controllers/views for every location.
- Tracking JavaScript pasted into database fields.
- Phone numbers or WhatsApp URLs hard-coded across templates.

## 8. Campaign module additions

```text
app/
├── Actions/Campaigns/
│   ├── SetDefaultCampaign.php
│   ├── PublishCampaign.php
│   ├── ReorderCampaignSections.php
│   └── ToggleCampaignSection.php
├── DTOs/
│   └── CampaignHeroData.php
├── Enums/
│   ├── CampaignStatus.php
│   └── CampaignSectionType.php
├── Http/Controllers/
│   ├── Backoffice/Admin/
│   │   ├── CampaignController.php
│   │   ├── CampaignSectionController.php
│   │   └── DefaultCampaignController.php
│   └── Frontend/
│       └── CampaignController.php
├── Http/Requests/Admin/
│   ├── StoreCampaignRequest.php
│   ├── UpdateCampaignRequest.php
│   ├── UpdateCampaignHeroRequest.php
│   ├── UpdateCampaignSectionRequest.php
│   └── SetDefaultCampaignRequest.php
├── Models/
│   ├── Campaign.php
│   ├── CampaignSection.php
│   └── CampaignSetting.php
├── Policies/
│   └── CampaignPolicy.php
├── Services/Campaigns/
│   ├── CampaignHeroResolver.php
│   ├── CampaignSectionRegistry.php
│   └── ResolveDefaultCampaign.php
resources/views/
├── backoffice/admin/campaigns/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── preview.blade.php
│   └── partials/
│       ├── hero-settings.blade.php
│       ├── section-toggle.blade.php
│       └── media-priority.blade.php
├── frontend/campaigns/
│   └── show.blade.php
└── components/campaign-sections/
    ├── hero.blade.php
    ├── hero-benefits.blade.php
    ├── service-grid.blade.php
    ├── category-brand.blade.php
    ├── pricing.blade.php
    ├── gallery.blade.php
    ├── video-gallery.blade.php
    ├── testimonials.blade.php
    ├── whatsapp-reviews.blade.php
    ├── faq.blade.php
    ├── cta.blade.php
    └── lead-form.blade.php
tests/Feature/Admin/
├── CampaignCrudTest.php
├── DefaultCampaignTest.php
├── CampaignSectionToggleTest.php
└── CampaignHeroMediaTest.php
```

### Campaign model rules

- `Campaign` implements `HasMedia` and registers `hero_images`, `hero_video`, `hero_video_poster` and `social_image`.
- `CampaignSection` implements `HasMedia` only when sections own independent assets.
- `CampaignHeroResolver` owns embed/image/uploaded-video priority.
- `SetDefaultCampaign` is the only class allowed to change the singleton default reference.
- Blade views receive resolved media/state and contain no database queries.
- Admin toggles submit authorized, validated requests; visual switches alone are not security controls.
- Campaign section registry keys and Blade filenames must stay one-to-one: `category_brand` → `category-brand.blade.php`, `whatsapp_reviews` → `whatsapp-reviews.blade.php`. Unknown keys are rejected.
