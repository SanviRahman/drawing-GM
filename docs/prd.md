# Product Requirements Document (PRD)

## 1. Product name

Dynamic Service Website & Lead Management Platform

## 2. Objective

Create a Laravel CMS and public website for a home-service business. The platform must reproduce the useful conversion patterns observed in the reference websites while keeping all content, menus, contact channels, images, videos, pricing and tracking settings dynamic.

## 3. Confirmed project baseline

- PHP `^8.3` and Laravel `^13.17` are already selected in the repository.
- Admin backoffice uses `App\Models\Admin`, the `admins` table and the `admin` guard/provider.
- Customer accounts use `App\Models\User`, the `users` table and the `web` guard/provider.
- Admin UI uses AdminLTE 3.16; the public frontend uses Blade/Vite and can use Tailwind 4. Alpine.js may be added when needed but is not currently a required dependency.
- `spatie/laravel-permission` 8.3 manages guard-specific roles/permissions.
- `spatie/laravel-medialibrary` 11.23 manages model-associated media.
- The website initially supports one language and one business/brand.
- A customer portal is planned for enquiry history and can remain disabled until implemented.
- Currency, locale, timezone and contact details are admin-configurable.
- Before implementing Page models, the project will complete Media Picker v1 because existing Admin/Settings forms already contain picker integration hooks.

## 4. Product goals

- Maximize qualified WhatsApp, call and quotation leads.
- Allow non-technical admins to manage the whole website.
- Support scalable service and location landing pages.
- Provide reliable SEO, analytics and conversion tracking.
- Keep media delivery fast and secure.

## 5. User journeys

### 5.1 WhatsApp conversion journey

1. Guest visits a home, service or location page.
2. Guest opens the floating plus/contact button.
3. Guest clicks WhatsApp.
4. System displays all active, applicable WhatsApp contacts from the admin panel.
5. Guest selects a contact.
6. Browser opens WhatsApp with a prefilled message.
7. System records a privacy-safe click event and dispatches enabled tracking events.

### 5.2 Quotation journey

1. Guest clicks Get Quote/Book Measurement.
2. Guest enters name and WhatsApp number, with email optional.
3. Directly below email, the form renders active admin-managed booking select fields in configured order (for example house size, paint type, sealer requirement). Admin can add any number of these fields and choices.
4. The current booking form does not render an add-ons checklist or preferred-date field.
5. Guest may upload approved images/documents when that upload control is enabled.
6. System validates every dynamic answer against the current allowed choices, creates a lead and snapshots the submitted field labels/values.
7. Admin receives a notification.
8. If the guest has an account, the lead appears in their dashboard.
9. Admin updates status and records notes.

### 5.3 Content publishing journey

1. Admin creates or edits content.
2. Admin configures sections, media, SEO and publication state.
3. Admin previews the page.
4. Admin publishes it.
5. Relevant caches and sitemap data are invalidated.

## 6. Functional requirements

### FR-001 — Authentication and authorization

- Admin and User are separate authenticatable models and guards.
- Admin: `App\Models\Admin`, `admins`, guard `admin`, Spatie `HasRoles` with `guard_name=admin`.
- User: `App\Models\User`, `users`, guard `web`; customer-side roles/permissions can be enabled on User when the account module needs them.
- Admin and web roles/permissions are guard-specific and must never be mixed.
- Admin routes use `auth:admin` and granular permission/`can:*` checks; record-level policies remain required where applicable.
- Current Admin registration is disabled. Password reset uses the separate admin broker/token table.
- Users can access only their own profile/enquiries/authorized attachments when the customer portal is implemented.

**Acceptance criteria**

- A `web`-authenticated customer session does not satisfy `auth:admin`; admin pages require an Admin session.
- An authenticated Admin without the required permission receives HTTP 403 for protected actions.
- An unauthenticated visitor is redirected/challenged by the appropriate guard.
- Admin and User password/session flows remain isolated.
- Permission checks are server-side; hiding a menu/button is never the only authorization control.

### FR-002 — Global settings

Admin can manage:

- Site identity and branding
- Default SEO/social image
- Locale, timezone and currency
- Company contact data
- Business hours
- Social URLs
- Default WhatsApp message
- Public registration toggle
- Tracking/consent toggles

**Acceptance criteria**

- Changes render publicly after cache invalidation without deployment.
- Secret values are encrypted where appropriate and never returned to public templates.

### FR-003 — Dynamic header and context-specific hero

- Dynamic top bar, logo, menus and CTA.
- Home, Plastering, Hacking, False Ceiling, every Service, Location and Page can use different hero/header media.
- Admin manages desktop/mobile hero images through Spatie collections `hero_desktop` and `hero_mobile`.
- Hero configuration includes title, subtitle, overlay, focal point, CTA, active state and optional slider ordering.
- Hero resolution fallback is entity-specific media, then template/service default, then global default.
- Nested menu support.
- Sticky and mobile navigation settings.
- Per-item active state, target, icon and order.

**Acceptance criteria**

- Admin can reorder menu items.
- Disabled items do not render.
- Keyboard users can open and close mobile/dropdown menus.

### FR-004 — Dynamic footer

- Dynamic footer layout, content, menu groups, contact/social data and legal links.
- Service/location links may be manually selected or automatically generated.

**Acceptance criteria**

- No footer text or links require source-code edits.
- Footer links respect publication status.

### FR-005 — Page and section management

- Admin can create pages with title, slug, template, state and SEO.
- Pages contain ordered reusable section blocks.
- Supported blocks include hero, rich text, cards, benefits, pricing, media gallery, before/after, video, testimonials, WhatsApp review proof, paint calculator, FAQ, CTA, contact form and approved safe embed.
- Sections can be duplicated, hidden and reordered.

**Acceptance criteria**

- Only enabled sections render.
- Invalid block configuration is rejected.
- Rich text is sanitized before rendering.

### FR-006 — Service management

- Create services with slug, summary, content, images, features, pricing, FAQs and related services.
- Control featured state and sort order.

**Acceptance criteria**

- Published services appear in configured menus/lists.
- Draft services return 404 publicly except in authorized preview.

### FR-007 — Location landing pages

- Manage location name, slug and unique content.
- Link one or more services.
- Override hero, pricing, contact routing and SEO by location.

**Acceptance criteria**

- Each published location page has unique title, canonical and configurable description.
- Location pages cannot be bulk-created with identical body content without an explicit warning.

### FR-008 — Pricing

- Manage packages, package features, property/room prices, sale badges and add-ons.
- Prices support optional prefix/suffix, range, call-for-price and display order.
- A package can be global or attached to specific services/locations.

**Acceptance criteria**

- Admin changes immediately update every page using the package.
- Historical leads retain submitted pricing snapshots if quotation values are stored.

### FR-009 — Spatie Media Library and Media Picker

- Spatie Media Library is the canonical generic media layer; do not add a competing media/upload table.
- Implemented owners include Admin (`avatars`) and SiteSetting (`site_logo`, `site_favicon`, `default_hero`).
- Existing Admin and Site Setting forms already provide direct file inputs plus hidden `*_media_id` fields and call `MediaPicker.open(...)`.
- Implement **Media Picker v1 before Page models**.

Media Picker v1 requirements:

- Keep the existing JavaScript contract compatible: `MediaPicker.open(callback, options = {})`.
- Browse existing authorized Spatie media inside an AdminLTE modal.
- Use server-side search/filter/pagination; never load the entire media table at once.
- Allow field-level MIME restrictions; image fields must reject non-image selections on both client and server.
- Return safe metadata only: ID, display/file name, MIME, size, collection, authorized preview/thumbnail URL and created time.
- Exclude private Lead attachments and other sensitive/private collections from the generic picker.
- Require `auth:admin` plus `media_list`/`media_view` permissions.
- Selecting an existing asset must not transfer its Spatie owner. Target services copy the source file into the target model's own named collection.
- Picker v1 is selection/reuse only; existing form upload inputs remain the new-upload workflow.
- Picker v1 requires no new database table.

General media requirements:

- Validate extension, detected MIME/signature and maximum size server-side.
- Prefer direct Spatie ownership and named collections over duplicate ownership foreign keys/pivots.
- Use `custom_properties` for alt/caption/focal/attribution metadata where needed.
- Generate responsive image variants asynchronously where useful.
- Private media is served only through authorization/signed routes.

**Acceptance criteria**

- Existing Admin avatar and Site Setting Media Picker buttons open the global picker.
- Search/filter/pagination work correctly.
- Admins without media permissions cannot access picker endpoints.
- Private Lead files never appear in generic picker results.
- Non-image selection is rejected for image-only fields server-side.
- Reusing media leaves the source media row/owner unchanged.
- Direct upload still works without using the picker.

### FR-010 — Galleries and before/after media

- Create galleries by service/location/page.
- Support ordinary items and before/after pairs.
- Reorder and toggle items.

**Acceptance criteria**

- Before/after comparison is touch and keyboard usable.
- Images use responsive sizes and lazy loading below the fold.

### FR-011 — Video management

- Support YouTube, Vimeo, uploaded MP4/WebM and approved external URLs.
- Validate embed domains and IDs.
- Upload poster image and configure controls, muted, loop and autoplay.
- Optional queue processing creates optimized output and thumbnails.

**Acceptance criteria**

- Invalid providers and file formats are rejected.
- Autoplay video is muted.
- Video failures display a poster/fallback link.

### FR-012 — Testimonials and reviews

- Manage manual testimonials with rating, customer, photo, source and publication state.
- Optionally embed an approved third-party review widget.
- Do not scrape reviews in violation of provider terms.

### FR-013 — FAQs

- Reusable FAQ records can attach to services, locations or pages.
- Admin controls order and visibility.
- Valid FAQPage JSON-LD is generated only for visible questions and answers.

### FR-014 — Blog

- Posts, categories, author, excerpt, featured image, rich body, SEO, publication time and state.
- Listing, detail, pagination and related posts.

**Acceptance criteria**

- Future posts are not public before their publication timestamp.
- Slugs are unique and redirects can preserve changed URLs.

### FR-015 — Floating contact widget

- Position, colours, launcher icon and visibility are admin-configurable.
- Collapsed state shows a plus/contact icon.
- Expanded state shows configured channels and an X/close icon.
- WhatsApp click displays all applicable active WhatsApp numbers.
- Numbers are sorted by priority and filtered by optional page/service/location targeting.
- Selection opens WhatsApp with a URL-encoded message.

**Acceptance criteria**

- No phone number is hard-coded in frontend templates or JavaScript.
- Inactive numbers disappear without deployment.
- Clicking outside, X or Escape closes the panel.
- Only one contact is marked default for a given targeting scope.
- All controls have accessible names and focus states.

### FR-016 — Back-to-top button

- Hidden until the scroll threshold is reached.
- Appears fixed at the configured position.
- Smooth scrolls to top; motion is disabled for reduced-motion users.

**Acceptance criteria**

- Works with mouse, touch and keyboard.
- Does not overlap cookie banner or the expanded contact panel.

### FR-017 — Leads/enquiries and booking-form fields

- Fixed booking fields are `name` (required), WhatsApp/phone number (required) and `email` (optional).
- Directly below email, render active admin-managed booking fields ordered by `sort_order`; the initial field type is a single-select with an admin-defined label, placeholder and ordered choice list.
- Admin can add, edit, reorder, activate/deactivate and soft-delete any number of booking fields.
- Example dynamic fields include house size, paint type and sealer requirement; these are configuration examples, not hard-coded database columns.
- The current booking baseline does not include an add-ons checklist or preferred-date control. Pricing add-ons remain independent pricing content and are not automatically rendered inside booking.
- Submitted dynamic answers are stored as historical snapshots linked to the Lead so later field edits do not alter previous enquiries.
- Public form may additionally capture configured location/services, message and approved attachments.
- Store source page, UTM values and consent flags.
- Admin can assign, change status and add internal/public notes.
- Notify configured recipients.

**Acceptance criteria**

- Email may be empty; name and WhatsApp/phone are validated server-side.
- Only active booking fields render publicly, in configured order.
- A submitted select value not present in the field's current allowed choices is rejected.
- Adding a new booking field in Admin requires no frontend source-code edit.
- Removing/deactivating a field removes it from new bookings without destroying historical answer snapshots.
- Add-ons and preferred date are absent from the current booking UI.
- Server-side validation is mandatory.
- Rate limits and anti-spam protection apply.
- Users can see only their own enquiries.

### FR-018 — SEO

- Editable meta title/description, canonical, robots and social image.
- Breadcrumbs and appropriate JSON-LD.
- XML and HTML sitemaps.
- Redirect manager for changed slugs.

**Acceptance criteria**

- Draft/noindex content is excluded from XML sitemap.
- Canonical URLs are absolute and point to the preferred public URL.

### FR-019 — Tracking and consent

Supported providers:

- Meta Pixel
- Optional Meta Conversions API
- Google Analytics 4 / Google Tag Manager
- TikTok Pixel

Events:

- PageView
- ViewContent
- ViewPricing
- Contact
- ClickWhatsApp
- ClickCall
- Lead
- SubmitQuote

**Acceptance criteria**

- Admin inputs validated provider IDs, not arbitrary script by default.
- Disabled providers inject no script or event.
- Marketing events respect configured consent policy.
- Browser/server duplicate events share an event ID.
- Test mode can be enabled without exposing secrets.

### FR-020 — Admin dashboard

Display:

- Lead counts by status/date/source
- Recent leads
- Published/draft content counts
- Media processing failures
- Tracking/provider configuration health

### FR-021 — Auditability

- Record important administrative changes: settings, tracking, contact numbers, lead status and destructive media/content actions.
- Store actor, action, entity, safe before/after summary, IP and timestamp.

### FR-022 — Campaign management

- Admin can create campaigns with title, slug/custom route, summary, status, publication dates and optional expiry.
- Campaign list supports search, status filter, bulk actions, preview, edit and soft delete.
- Campaign permissions use Spatie Permission, including `view campaigns`, `create campaigns`, `edit campaigns`, `publish campaigns`, `set default campaign` and `delete campaigns`.
- Campaign media uses Spatie Media Library.

**Acceptance criteria**

- Draft/inactive/expired campaigns are unavailable publicly except through an authorized preview.
- Campaign slugs/routes are unique and cannot collide with reserved application routes.
- Campaign changes invalidate campaign and default-campaign caches.

### FR-023 — Default campaign

- Admin can mark one eligible campaign as default from the list or edit screen.
- A singleton default-campaign reference is the database source of truth; the UI switch reflects that reference.
- Only an active, published and non-expired campaign can become default.
- Setting a default runs transactionally and replaces the previous default.
- The user panel displays the resolved default campaign in its configured slot.
- If the default becomes invalid, the system uses configured normal-home fallback and alerts Admin.

**Acceptance criteria**

- Two concurrent requests cannot produce two defaults.
- Deactivating/deleting the default requires selecting a replacement or accepting fallback behaviour.
- Users never see draft or inactive campaign content.

### FR-024 — Campaign section visibility

- Every campaign has ordered typed sections from an allowlisted registry, including hero, hero benefits, service grid, optional category/brand, pricing, gallery, video, testimonials/WhatsApp proof, FAQ, CTA and lead form.
- Every section exposes an Active/Inactive toggle.
- Toggle changes preserve section content.
- Disabled sections are excluded from frontend HTML, structured data and section-specific tracking.
- Admin can reorder, duplicate and preview sections.

**Acceptance criteria**

- Disabling Hero Benefits hides only that campaign's Hero Benefits section.
- Re-enabling a section restores its saved data and order.
- Preview clearly distinguishes disabled sections from public output.

### FR-025 — Campaign hero source and video controls

Hero source priority:

1. Valid embed video
2. Ordered image slider
3. Uploaded campaign video
4. Campaign/global fallback image

Options:

- Embed URL/provider
- Multiple images and order
- Uploaded MP4/WebM video and poster
- Autoplay toggle
- Muted toggle
- Controls toggle
- Loop toggle
- Hero section Active/Inactive toggle

**Acceptance criteria**

- When an embed is active and valid, lower-priority sources do not load unnecessarily.
- Without an embed, image slider is used when it has active images.
- Uploaded video is used only when the first two sources are unavailable.
- Autoplay-enabled playback is muted to comply with browser policies; if admin requires sound, autoplay is disabled and controls are shown.
- User can unmute through visible controls when allowed.
- Video settings apply only to the campaign being edited.

## 7. Non-functional requirements

### Performance

- Responsive images and lazy loading.
- Cache settings, navigation, published pages and common queries.
- Queue email, video and image processing.
- Avoid third-party scripts until required/consented.

### Security

- CSRF, XSS prevention, parameter binding and mass-assignment controls.
- Strict upload validation.
- Signed URLs for private lead attachments.
- Login/form throttling.
- Encrypted integration tokens.
- Content Security Policy planned for production.

### Accessibility

- Semantic headings and landmarks.
- Visible focus styles.
- Keyboard-compatible menus, sliders, FAQ, widget and back-to-top control.
- Alt text management.
- Reduced-motion support.

### Reliability

- Transactional writes for compound admin operations.
- Idempotent queued jobs where possible.
- Daily backups and restore testing.
- Error monitoring and failed-job handling.

## 8. Analytics definitions

| Event | Trigger | Minimum parameters |
|---|---|---|
| `page_view` | Public page viewed | page type, URL |
| `view_content` | Service/location/post viewed | entity ID, name |
| `view_pricing` | Pricing enters viewport or is opened | package/service |
| `contact` | Contact panel/channel selected | channel type |
| `click_whatsapp` | WhatsApp number selected | contact ID, label, scope |
| `click_call` | Phone link selected | contact ID |
| `submit_quote` | Valid quote submitted | lead reference, service |
| `lead` | Lead successfully persisted | lead reference, source |

Phone/email values must not be sent to analytics providers unless legally permitted and explicitly designed.

## 9. Release acceptance checklist

- All header/footer data is dynamic.
- Every visible number originates from contact-channel records.
- Floating widget works in collapsed, expanded, WhatsApp-list and closed states.
- Back-to-top appears at the configured threshold.
- All page sections can be enabled, disabled and reordered.
- Image and video workflows pass validation tests.
- Separate `admin`/`web` guard isolation and granular permissions pass feature tests.
- Media Picker permissions, filtering, private-media exclusion and selection-copy behavior pass feature/browser tests.
- Home, Plastering and Hacking each render their own configured desktop/mobile hero image and correct fallback.
- SEO metadata, schema, redirects and sitemap are validated.
- Tracking events are verified with provider test tools.
- Mobile, accessibility, security and performance checks pass.
