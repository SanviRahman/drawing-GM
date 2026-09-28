# Application Architecture

## 1. Architectural style

Use a modular Laravel monolith. This keeps the initial system simple while enforcing domain boundaries through controllers, Form Requests, policies, actions/services, events and jobs. Avoid premature microservices.

## 2. Recommended stack

- Laravel 12, PHP 8.3+
- `spatie/laravel-permission`
- `spatie/laravel-medialibrary`
- MySQL 8+
- Blade + Alpine.js
- Tailwind CSS or Bootstrap 5
- Vite
- Redis cache/queue in production where available
- S3-compatible object storage for production media
- Nginx + PHP-FPM
- Supervisor/systemd for queue workers
- Scheduler cron every minute
- Optional FFmpeg for uploaded-video processing

## 3. High-level diagram

```mermaid
flowchart LR
    B[Browser] --> CDN[CDN / Reverse Proxy]
    CDN --> WEB[Laravel Web Application]
    WEB --> DB[(MySQL)]
    WEB --> CACHE[(Redis Cache)]
    WEB --> STORAGE[(Object Storage)]
    WEB --> QUEUE[(Queue)]
    QUEUE --> WORKER[Laravel Worker]
    WORKER --> STORAGE
    WORKER --> MAIL[Mail Provider]
    WEB --> TRACK[Approved Tracking Providers]
    ADMIN[Admin Browser] --> WEB
```

## 3.1 Package installation baseline

```bash
composer require spatie/laravel-permission spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
php artisan permission:cache-reset
```

Publish package configuration only when project-specific changes are required. Pin versions compatible with the confirmed Laravel/PHP versions.

## 4. Application layers

### 4.1 Presentation

- Public Blade views/components
- Admin Blade/Livewire views or an approved admin package
- Alpine controllers for menu, contact widget, slider, FAQ and back-to-top
- ViewModels/resource objects where templates would otherwise contain queries

### 4.2 HTTP/application

- Thin controllers
- Form Request validation and authorization
- Action classes for meaningful use cases
- API Resources only where JSON endpoints are required
- Spatie Permission middleware/permission checks plus policies for entity-level authorization

### 4.3 Domain/services

Suggested services/actions:

- `ResolvePublishedPage`
- `RenderPageSections`
- `ResolveContactChannels`
- `CreateLead`
- `UpdateLeadStatus`
- `PublishContent`
- `StoreMedia`
- `ProcessUploadedVideo`
- `BuildSeoMetadata`
- `DispatchTrackingEvent`
- `InvalidateContentCache`

### 4.4 Persistence

- Eloquent models and relationships
- Query scopes for `published`, `active`, `ordered`
- Transactions for multi-table writes
- Database constraints/indexes as the final integrity boundary

## 5. Routing design

### Public routes

- `/`
- `/services`
- `/services/{service:slug}`
- `/locations/{location:slug}`
- `/pricing`
- `/gallery`
- `/blog`
- `/blog/{post:slug}` or `/{post:slug}` only if collision strategy is defined
- `/contact`
- `/quote`
- `/privacy-policy`
- `/sitemap`

### User routes

Prefix `/account`, middleware `auth`, `verified` where enabled:

- profile
- enquiries
- enquiry detail
- attachment download through authorization

### Admin routes

Prefix `/admin`, middleware `auth`, `active`, `role:admin`:

- dashboard
- settings, menus, pages, sections
- services, locations, pricing
- media, galleries, videos
- testimonials, FAQs, posts
- contact channels, leads
- SEO, redirects, tracking
- audit logs

Route model binding must use slugs only on public routes and IDs/ULIDs where appropriate in admin routes.

## 5.1 Authorization with Spatie Permission

- `User` uses `Spatie\Permission\Traits\HasRoles`.
- Seed `admin` and `user` roles plus granular permissions.
- Admin can initially receive all administrative permissions; do not scatter role-name checks throughout controllers.
- Routes use permission middleware for broad access and policies for record-level decisions.
- Reset Spatie's permission cache after role/permission mutations and deployment seeding.

## 6. Dynamic page rendering

1. Route resolves a Page, Service, Location or Post.
2. Published scope enforces status and publication time.
3. Application resolves ordered active sections.
4. Each section key maps to a known Blade component.
5. Payload is validated when saved, not trusted at render time.
6. Spatie media collections provide URLs, conversions and responsive variants.
7. SEO builder creates metadata and schema.
8. Cached output/data is invalidated when related content changes.

Do not execute arbitrary PHP/Blade/JavaScript stored in the database.

## 7. Header, footer and hero architecture

- `HeaderComposer` loads cached branding, top bar and `header-primary` menu.
- A `HeroResolver` receives the current Page/Service/Location and resolves Spatie collections in this order: `hero_desktop`/`hero_mobile` on the entity, template/service default, global default.
- Home, Plastering, Hacking and other service pages therefore render independent dynamic images.
- Cache keys include entity type, entity ID, locale and hero/media update version to prevent cross-page image leakage.
- Hero media conversions include desktop, tablet/mobile and social preview sizes.
- `FooterComposer` loads cached footer settings and menu groups.
- Menu trees are built once and cached by location.
- Model observers/events invalidate menu/global-layout cache after mutations.
- Public templates render components such as `<x-site.header />` and `<x-site.footer />`.

## 8. Floating contact widget architecture

### Server side

`ResolveContactChannels` accepts current page/service/location context and returns:

1. Matching active targeted channels
2. Active global fallback channels
3. Default channel first
4. Remaining channels by sort order

Only safe public fields are serialized to the Blade component.

### Client side

An Alpine component manages:

- `open`: launcher expanded/collapsed
- `whatsappOpen`: WhatsApp list visible
- Escape/outside-click close
- Focus return to launcher
- Click event dispatch
- Responsive popover/bottom sheet

Generated WhatsApp URL:

```text
https://wa.me/{digits_only_e164}?text={url_encoded_message}
```

Never hard-code numbers in Blade or JavaScript. Message placeholders may include safe values such as page title, service and location; unresolved placeholders must be removed.

### Back-to-top

A separate lightweight Alpine/native controller:

- observes `window.scrollY`
- toggles visibility after configured threshold
- calls `window.scrollTo({ top: 0, behavior })`
- uses `behavior: auto` for reduced-motion users

## 9. Media architecture

Use Spatie Media Library as the canonical media layer. Content models implement `HasMedia` and `InteractsWithMedia`, register named collections and conversions, and use package APIs such as `addMedia`, `getFirstMediaUrl` and responsive image rendering. A custom `Media` model may extend Spatie's Media model only for project-specific metadata/behaviour.

Recommended collections:

- `Page`, `Service`, `Location`: `hero_desktop`, `hero_mobile`, `gallery`
- `SiteSetting` or branding owner: `logo`, `mobile_logo`, `favicon`, `default_hero`
- `Post`: `featured`, `content_images`
- `GalleryItem`: `image`, `before`, `after`
- `Video`: `video_file`, `video_poster`
- `Testimonial`: `photo`
- `User`: `avatar`
- `Lead`: private `attachments`

### Images

1. Form Request validates file size and allowed content.
2. Service verifies detected MIME/signature.
3. Original file is stored with generated name.
4. Media row is created.
5. Queue generates responsive variants and WebP/AVIF when supported.
6. Frontend uses width/height, `srcset`, `sizes` and lazy loading.

### Uploaded videos

1. Validate MP4/WebM and configured maximum size/duration.
2. Store original on private/pending path.
3. Queue probes/transcodes if enabled.
4. Generate poster/metadata.
5. Mark `ready` and expose final public/CDN URL.
6. On failure, record a safe error and notify admin.

Large uploads should use direct-to-object-storage signed uploads when hosting limits require it.

### Embedded videos

- Accept only allowlisted YouTube/Vimeo URL formats.
- Extract provider video ID server-side.
- Render privacy-enhanced embeds when practical.
- Lazy-load iframe only after user interaction or proximity to viewport.

## 10. Lead architecture

`CreateLead` performs:

1. Rate-limit and anti-spam verification.
2. Server-side validation.
3. Transaction: lead, services, attribution and attachments.
4. Status-history creation.
5. Commit.
6. Queue admin/customer notifications.
7. Dispatch internal `LeadCreated` event.
8. Trigger browser/server tracking using a shared event ID where applicable.

Attachments remain private and are served through policies/signed routes.

## 11. Tracking architecture

### Configuration

- Admin selects provider and enters validated IDs.
- Secrets use encrypted model casts/config storage.
- Raw JavaScript injection is disabled by default.
- Public layout receives only enabled public identifiers.

### Internal event bus

Frontend and backend use stable internal names:

- `page_view`
- `view_content`
- `view_pricing`
- `contact`
- `click_whatsapp`
- `click_call`
- `submit_quote`
- `lead`

Provider adapters map internal events to Meta/GA/TikTok names.

### Meta Pixel and CAPI

- Browser Pixel handles page/interaction events after required consent.
- Optional CAPI sends qualified server events from queued jobs.
- Browser and server events share `event_id` for deduplication.
- Never send passwords, message bodies, or unapproved personally identifiable information.

## 12. SEO architecture

- `SeoMetadataBuilder` merges global defaults with entity-specific metadata.
- Canonical generation uses trusted application URL and route models.
- JSON-LD is generated from structured fields, not arbitrary admin JSON unless strictly validated.
- Sitemap query includes published, indexable Pages, Services, Locations and Posts.
- Slug changes can create 301 redirects transactionally.
- Breadcrumb component and schema use the same hierarchy source.

## 13. Security architecture

- CSRF middleware for web forms.
- Form Requests for validation and authorization.
- Policies for all admin/user resources.
- `fillable` allowlists or guarded models with controlled DTO/action input.
- Sanitized rich text allowlist.
- Upload signature/MIME checks and safe extensions.
- Private lead attachments.
- Rate limits for login, password reset, quote and tracking endpoints.
- Security headers: CSP, HSTS, X-Content-Type-Options, Referrer-Policy and frame policy.
- Encrypted tokens and redacted logs/audit values.
- Dependency and vulnerability checks in CI.

## 14. Caching and invalidation

Cache candidates:

- Global public settings
- Header/footer menus
- Contact channels
- Published page/service/location data
- Pricing packages
- FAQ/testimonial lists
- Sitemap document

Use tagged cache where supported. Invalidate through domain events/observers after successful commits. Do not cache authenticated user-specific pages publicly.

## 15. Queue and scheduler

Queued jobs:

- Image conversion
- Video processing
- Lead notifications
- Meta CAPI dispatch
- Sitemap regeneration/warming
- Expired media cleanup

Scheduled tasks:

- Publish scheduled posts/pages
- Retry/alert failed media processing
- Prune sessions, temporary uploads, old click logs and audit data per policy
- Backup and health checks as deployment tooling permits

## 16. Testing strategy

### Feature tests

- Admin/User authorization
- CRUD validation
- Publication visibility
- Contact-target resolution
- Spatie role/permission enforcement and permission-cache behaviour
- Entity-specific hero resolution and fallback
- Lead submission and attachment access
- Tracking enable/disable and consent rules
- Redirect and sitemap behavior

### Unit tests

- WhatsApp URL/message generation
- Pricing display formatter
- SEO metadata merge
- Contact sorting/fallback
- Tracking provider mapping

### Browser tests

- Responsive menu
- Floating launcher expand/close
- WhatsApp list rendering
- Back-to-top threshold and action
- Slider, FAQ and before/after controls
- Quote submission

### Security tests

- Stored/reflected XSS
- Unauthorized media access
- Malicious uploads
- IDOR against enquiries
- Rate limiting and CSRF

## 17. Deployment topology

Minimum production:

- Nginx
- PHP-FPM application
- MySQL
- Queue worker
- Scheduler cron
- Object storage/CDN
- TLS certificate
- Central logs/error monitoring
- Automated database and media backups

Deployment order:

1. Maintenance-safe release directory
2. Composer install and frontend build
3. Configuration/cache warmup
4. Backward-compatible migrations
5. Symlink/current release switch
6. Queue restart
7. Health and smoke tests

## 18. Campaign architecture

### Campaign resolution

- Public campaign routes resolve only published, active, started and non-expired records.
- The user-panel campaign slot calls `ResolveDefaultCampaign`.
- `ResolveDefaultCampaign` reads the singleton `campaign_settings.default_campaign_id`, verifies eligibility and returns the configured fallback when invalid.
- Cache keys include campaign ID, update timestamp/version and audience context.

### Setting the default campaign

`SetDefaultCampaign` performs:

1. Authorize `set default campaign` through Spatie Permission.
2. Validate that the campaign is active, published and within its schedule.
3. Start a database transaction.
4. Lock the singleton `campaign_settings` row for update.
5. Replace `default_campaign_id`.
6. Commit and invalidate default/user-panel caches.
7. Record an audit log.

This prevents concurrent requests from creating conflicting defaults.

### Campaign section rendering

- `Campaign` owns ordered `CampaignSection` records.
- Admin editor shows a toggle for every section.
- Public repository queries only `is_enabled = true` sections.
- Section keys map to allowlisted Blade components; payload schemas are validated on write.
- Disabled sections preserve data but do not render or emit tracking/schema.

### Campaign hero resolver

`CampaignHeroResolver` returns the first valid source:

1. Allowlisted active embed video
2. Ordered Spatie `hero_images`
3. Ready Spatie `hero_video`
4. Campaign or global fallback image

The resolver returns a typed ViewModel so Blade does not contain priority logic.

### Video playback rules

- `autoplay`, `muted`, `controls` and `loop` are stored as booleans in validated hero configuration.
- Autoplay with sound is blocked by many browsers. Server validation forces `muted = true` for autoplay, or turns autoplay off and ensures controls are visible.
- Render `playsinline` for mobile.
- Embedded provider parameters are generated server-side from allowlisted options.
- Lower-priority media should not be eagerly loaded when a higher-priority source is selected.
- User interaction can unmute only when controls/provider API support it.

### Campaign cache invalidation

Invalidate campaign/default caches after:

- Campaign status/schedule/content change
- Default selection change
- Section toggle/reorder/update
- Campaign media add/remove/reorder/conversion completion
- Campaign expiry scheduler transition

### Campaign testing

- Default uniqueness under concurrent requests
- Draft/inactive/expired campaign exclusion
- User panel default/fallback behaviour
- Section Active/Inactive persistence and frontend omission
- Embed → slider → uploaded-video → fallback priority
- Autoplay/muted browser-policy validation
- Per-campaign media/settings isolation
- Spatie permission checks for campaign actions
