# Database Schema Specification

## 1. General standards

- Engine: InnoDB
- Character set: `utf8mb4`
- Collation: project default compatible with MySQL 8
- IDs: `BIGINT UNSIGNED AUTO_INCREMENT` unless the team standardizes on ULIDs before migrations
- Money: `DECIMAL(12,2)`, never floating point
- Phone numbers: normalized E.164 text, never integer
- Timestamps: UTC in storage; render using configured timezone
- Flexible block/provider configuration: JSON with application-level validation
- Secrets: encrypted casts and excluded from serialization/logs
- Soft deletes: content, media, services, locations, posts, testimonials and leads where recovery is valuable

## 2. Identity and access

The actual application uses separate Admin and User authentication domains. This is intentional and must remain consistent across migrations, guards, permissions and future actor foreign keys.

### `admins` — implemented

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar | required |
| username | varchar | unique, required |
| email | varchar | unique, required |
| phone | varchar(20) | nullable, unique |
| email_verified_at | timestamp | nullable |
| password | varchar | required, hashed by model cast |
| status | boolean | default true |
| photo | varchar(2048) | nullable legacy compatibility path only |
| remember_token | varchar(100) | nullable |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable, soft deletes |

`Admin` is an authenticatable model using the `admin` guard, `HasRoles`, `SoftDeletes` and Spatie Media Library. The canonical avatar collection is `avatars` (single-file/public). New avatar writes should use Spatie media; `photo` is retained only for legacy fallback/migration compatibility.

`admin_password_reset_tokens` is the separate password-reset token table for the admin provider.

### `users` — implemented foundation

Current repository columns:

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar | required |
| email | varchar | unique, required |
| email_verified_at | timestamp | nullable |
| password | varchar | required |
| remember_token | varchar(100) | nullable |
| created_at/updated_at | timestamp | required |

The customer portal may later add `phone`, `is_active`, `last_login_at`, soft deletes and/or a user avatar through additive migrations. Do not document those fields as already implemented until their migrations exist.

`password_reset_tokens` belongs to the `users` provider. The `sessions.user_id` relation remains the standard web-session relation.

### Spatie Permission tables — implemented customization

The project uses Spatie Permission with custom Role/Permission models and guard-specific records:

- `roles`: `id`, `name`, `guard_name`, timestamps, soft deletes.
- `permissions`: `id`, `name`, `guard_name`, required `group_name`, timestamps, soft deletes.
- `model_has_roles`: `role_id`, `model_type`, `model_id`.
- `model_has_permissions`: `permission_id`, `model_type`, `model_id`.
- `role_has_permissions`: `permission_id`, `role_id`.

Guard rules:

- `admin` guard binds to `App\Models\Admin`; admin roles/permissions use `guard_name = admin`.
- `web` guard binds to `App\Models\User`; user-side roles/permissions, when enforced, use `guard_name = web`.
- Never cross-assign roles/permissions between guards.
- The current `RolePermissionSeeder` seeds the admin permission groups, an `admin` role, web-side user permissions/role, and the initial Admin account.
- The current `User` model does not yet use `HasRoles`; add it when customer-side permission checks are implemented rather than pretending it is already active.

## 3. Site configuration

### `site_settings`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| group_name | varchar(80) | branding, contact, footer, consent, etc. |
| setting_key | varchar(150) | unique |
| setting_value | longtext | JSON/text storage |
| value_type | varchar(30) | string, boolean, integer, json, encrypted |
| is_public | boolean | whether safe for public settings payload |
| created_at/updated_at | timestamp | required |

Recommended keys include `site.name`, `site.timezone`, `site.currency`, `header.sticky`, `footer.description`, `widget.position`, `widget.scroll_threshold`, and `registration.enabled`. Implemented branding collections are `site_logo`, `site_favicon` and `default_hero`; files are stored through Spatie collections rather than setting-value file paths. A `mobile_logo` collection may be added later only when the UI requires it.

### `menus`

`id`, `name`, unique `location`, `is_active`, timestamps.

Locations: `header-primary`, `header-top`, `footer-services`, `footer-company`, `footer-legal`.

### `menu_items`

`id`, `menu_id`, nullable `parent_id`, `label`, `link_type`, nullable `linkable_type`, nullable `linkable_id`, nullable `url`, nullable `icon`, `target`, `css_class`, `sort_order`, `is_active`, timestamps.

Indexes:
- (`menu_id`, `parent_id`, `sort_order`)
- (`linkable_type`, `linkable_id`)

Validate `target` against `_self` and `_blank`.

## 4. Pages and sections

### `pages`

`id`, `title`, unique `slug`, nullable `excerpt`, `template`, JSON `hero_config`, `status`, nullable `published_at`, `is_homepage`, `show_header`, `show_footer`, nullable `created_by` (FK admins), nullable `updated_by` (FK admins), timestamps, soft deletes.

Page implements `HasMedia` with `hero_desktop` and `hero_mobile` collections. Home therefore owns its image independently from service pages.

Status values: `draft`, `published`, `archived`.

Indexes:
- unique `slug`
- (`status`, `published_at`)
- unique conditional homepage rule enforced at application/transaction level

### `section_definitions`

`id`, unique `key`, `name`, nullable `description`, JSON `schema_json`, `is_active`, timestamps.

Initial keys:
- `hero`
- `rich_text`
- `benefit_grid`
- `service_carousel`
- `pricing`
- `gallery`
- `before_after`
- `video_gallery`
- `testimonials` (Google / Star reviews)
- `whatsapp_reviews` (WhatsApp chat screenshot proof showcase)
- `paint_calculator` (Property size and paint calculator)
- `faq`
- `cta`
- `contact_form`
- `safe_embed` (allowlisted provider/URL only; no arbitrary admin JavaScript)
- `spacer`

Registry invariant: each active `section_definitions.key` must map to one allowlisted validation schema and one `resources/views/components/sections/{key-with-hyphens}.blade.php` component. `whatsapp_reviews` maps to `whatsapp-reviews.blade.php`; `paint_calculator` maps to `paint-calculator.blade.php`; `safe_embed` maps to `safe-embed.blade.php`.

### `page_sections`

`id`, `page_id`, `section_definition_id`, nullable `heading`, nullable `subheading`, JSON `payload`, `theme`, `sort_order`, `is_active`, nullable `starts_at`, nullable `ends_at`, timestamps, soft deletes.

Index: (`page_id`, `is_active`, `sort_order`).

### `section_media`

`id`, `page_section_id`, `media_id`, `role`, nullable `caption_override`, `sort_order`, timestamps.

Unique recommendation: (`page_section_id`, `media_id`, `role`).

## 5. Services, locations and prices

### `services`

`id`, `name`, unique `slug`, nullable `summary`, nullable `content`, nullable `icon`, JSON `hero_config`, `status`, `is_featured`, `sort_order`, nullable `published_at`, timestamps, soft deletes.

Service implements `HasMedia` with `hero_desktop`, `hero_mobile` and `gallery` collections. HDB Painting, Condo Painting, Commercial, Plastering, and other services own independent hero images.

### `service_features`

`id`, `service_id`, `title`, nullable `description`, nullable `icon`, `sort_order`, `is_active`, timestamps.

### `locations`

`id`, `name`, unique `slug`, nullable `region`, nullable `postal_codes` JSON, nullable `summary`, nullable `content`, JSON `hero_config`, `status`, `sort_order`, nullable `published_at`, timestamps, soft deletes.

Location implements `HasMedia` with `hero_desktop`, `hero_mobile` and `gallery` collections.

### `service_location`

`service_id`, `location_id`, JSON `override_data`, `is_active`, timestamps. Composite PK/unique on both IDs.

### `pricing_packages`

`id`, nullable `service_id`, nullable `location_id`, `name`, nullable `subtitle`, nullable `badge`, nullable `description`, `currency` char(3), `is_featured`, `is_active`, `sort_order`, timestamps, soft deletes.

A null location means global/service-level pricing. Location-specific records override global packages only when explicitly configured.

### `pricing_items`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| pricing_package_id | bigint unsigned | FK |
| label | varchar(190) | e.g. 3-Room HDB, 4-Room HDB, Condo 2-Bedroom |
| amount | decimal(12,2) | nullable for call-for-price |
| amount_max | decimal(12,2) | nullable range maximum |
| price_type | varchar(30) | fixed, from, range, call |
| unit | varchar(80) | room, property, sq ft, job |
| prefix/suffix | varchar(40) | optional display text (e.g. "Nett", "From") |
| sort_order | int | default 0 |
| is_active | boolean | default true |

### `pricing_addons`

`id`, `name`, nullable `description`, nullable `amount`, nullable `amount_max`, `price_type`, nullable `unit`, `is_active`, `sort_order`, timestamps, soft deletes.
(e.g., Ceiling painting, Sealer coat, Door and gate frame, Balcony paint).

### `pricing_package_addon`

`pricing_package_id`, `pricing_addon_id`, optional JSON `override_data`, composite unique.

## 6. Media and video

### Spatie `media`

Use the migration published by `spatie/laravel-medialibrary`; this is the canonical media table.

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| model_type/model_id | morphs | owning model |
| uuid | uuid | package field |
| collection_name | varchar | `hero_desktop`, `gallery`, `whatsapp_proof`, etc. |
| name/file_name | varchar | display/stored names |
| mime_type | varchar | detected MIME |
| disk/conversions_disk | varchar | storage disks |
| size | bigint unsigned | bytes |
| manipulations | json | package-managed |
| custom_properties | json | alt, caption, attribution, focal point, processing metadata |
| generated_conversions | json | package-managed |
| responsive_images | json | package-managed |
| order_column | unsigned integer | ordering |
| created_at/updated_at | timestamp | required |

Do not create a second generic media table. Private lead attachments use a private disk and authorized downloads.

#### Media Picker v1

Media Picker v1 does not require a new database table. It reads authorized rows from the existing Spatie `media` table and returns safe selection metadata to Admin forms. Generic picker queries must exclude private lead attachments/sensitive collections. When an existing media item is selected for Admin avatar or Site Setting branding, the current service copies the source file into the target model's own collection and creates a new owned Spatie media row; it does not reassign the source row's `model_type/model_id`.

### `galleries`

`id`, `name`, nullable `attachable_type`, nullable `attachable_id`, `layout`, `is_active`, timestamps, soft deletes.

### `gallery_items`

`id`, `gallery_id`, `item_type`, nullable `title`, nullable `caption`, `sort_order`, `is_active`, timestamps, soft deletes.

`GalleryItem` implements `HasMedia` and uses named collections:
- `image` for an ordinary gallery image.
- `before` and `after` for a comparison pair.

Rules:
- `item_type=image` requires one active media item in the `image` collection.
- `item_type=before_after` requires both `before` and `after` media collections.
- Do not duplicate these relationships with `media_id`, `before_media_id`, or `after_media_id` columns.

### `videos`

`id`, nullable polymorphic attachable, `title`, nullable `caption`, `source_type`, nullable `provider`, nullable `provider_video_id`, nullable `source_url`, nullable `duration_seconds`, `autoplay`, `muted`, `controls`, `loop`, `processing_status`, nullable `processing_error`, `sort_order`, `is_active`, timestamps, soft deletes.

`Video` implements `HasMedia` with `video_file` and `video_poster` collections. Uploaded file MIME, size, disk/path and conversion metadata live in Spatie's `media` table/custom properties rather than duplicated columns on `videos`.

Rules:
- Embed requires allowlisted provider + provider ID/URL.
- Upload requires a validated item in `video_file`.
- Autoplay requires muted playback; otherwise autoplay is disabled and controls remain available.

## 7. Testimonials and FAQs

### `testimonials`

Designed to support both **Google/Star Reviews** and **WhatsApp Chat Screenshot Social Proof**.

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| type | varchar(30) | default `text`; allowed: `text`, `whatsapp_screenshot`, `video` |
| customer_name | varchar(150) | required |
| customer_title | varchar(150) | nullable |
| rating | tinyint unsigned | nullable, check 1-5 |
| review | text | nullable when a screenshot/video is the proof source |
| source | varchar(50) | `google`, `whatsapp`, `facebook`, `direct` |
| source_url | varchar(255) | nullable link to original review |
| reviewed_at | timestamp | nullable |
| is_featured | boolean | default false, indexed |
| is_active | boolean | default true, indexed |
| sort_order | int | default 0 |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable (soft deletes) |

`Testimonial` implements `HasMedia` with `photo` and `testimonial_screenshot` collections. Do not duplicate them using `photo_media_id`/`screenshot_media_id` columns.

### `testimonialables`

Polymorphic mapping for associating testimonials with specific services (e.g. HDB painting testimonials vs. Condo painting testimonials) or location pages:

`id`, `testimonial_id`, `testimonialable_type`, `testimonialable_id`, `sort_order`, timestamps.
Unique: (`testimonial_id`, `testimonialable_type`, `testimonialable_id`).

### `faqs`

`id`, `question`, `answer`, `is_active`, timestamps, soft deletes.

### `faqables`

`faq_id`, `faqable_type`, `faqable_id`, `sort_order`, timestamps; unique (`faq_id`, `faqable_type`, `faqable_id`).

## 8. Blog

### `categories`

`id`, `name`, unique `slug`, nullable `description`, `is_active`, timestamps, soft deletes.

### `posts`

`id`, `author_id` (FK admins), nullable `category_id`, `title`, unique `slug`, nullable `excerpt`, `body`, `status`, nullable `published_at`, nullable `reading_minutes`, `allow_comments` default false, timestamps, soft deletes.

`Post` implements `HasMedia` with `featured` and `content_images` collections. Do not create `featured_media_id` or a duplicate `post_media` pivot when direct Spatie ownership represents the relationship.

## 9. Contact widget

### `contact_channels`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| type | varchar(30) | whatsapp, phone, email, custom |
| label | varchar(120) | Customer-facing label (e.g. "WhatsApp Sales 1", "Fast Response") |
| region | varchar(120) | nullable e.g. North/South/East/West |
| value | varchar(255) | normalized number/email/URL |
| display_value | varchar(120) | nullable formatted display |
| message_template | text | nullable, WhatsApp placeholders allowed |
| icon | varchar(100) | controlled icon key |
| colour | varchar(20) | validated hex/token |
| availability_text | varchar(120) | nullable (e.g. "Mon-Sun 8am-10pm") |
| is_default | boolean | default false |
| is_active | boolean | default true |
| track_clicks | boolean | default true |
| sort_order | int | default 0 |
| created_at/updated_at/deleted_at | timestamp | soft deletes |

Indexes: (`type`, `is_active`, `sort_order`), `is_default`.

### `contact_targets`

`id`, `contact_channel_id`, `targetable_type`, `targetable_id`, timestamps.

Unique: (`contact_channel_id`, `targetable_type`, `targetable_id`).

Selection algorithm:
1. Active channels targeting current location/service/page.
2. Active global channels without targets.
3. Sort default first, then `sort_order`, then ID.

### Optional `contact_clicks`

`id`, nullable `contact_channel_id`, `session_hash`, nullable `page_url`, nullable `referer`, nullable `utm` JSON, nullable `ip_hash`, `clicked_at`.

## 10. Leads

### `leads`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | nullable FK users |
| reference | varchar(50) | unique quotation reference (e.g. BP-2026-0042) |
| name | varchar(150) | required |
| email | varchar(190) | nullable |
| phone | varchar(32) | required, E.164 normalized |
| location_id | bigint unsigned | nullable FK locations |
| message | text | nullable |
| metadata | json | nullable supplemental/non-field metadata such as calculator output or attribution-safe context; dynamic booking answers are stored in `lead_form_answers` |
| status | varchar(30) | default 'new' ('new', 'contacted', 'qualified', 'quoted', 'won', 'lost', 'spam', 'closed') |
| assigned_to | bigint unsigned | nullable FK admins; assignee must have the required lead-management permission |
| source_page_url | varchar(255) | nullable lead landing URL |
| utm | json | nullable UTM parameters |
| consent | json | nullable consent log |
| pricing_snapshot | json | nullable submitted package quote snapshot |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable (soft deletes) |

Indexes: `status`, `created_at`, `assigned_to`, `user_id`, `location_id`.

### `lead_form_fields`

Admin-managed booking/quotation select fields rendered after the optional email field.

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK |
| label | varchar(150) | required; customer-facing field label |
| field_key | varchar(100) | unique, required, stable machine key |
| placeholder | varchar(190) | nullable, e.g. `Select house size` |
| options | json | required ordered list of allowed select choices |
| is_required | boolean | default false |
| is_active | boolean | default true |
| sort_order | int | default 0 |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable, soft deletes |

Rules:

- Public booking form order is: `name` (required), `phone`/WhatsApp number (required), `email` (optional), then active `lead_form_fields` ordered by `sort_order` and ID.
- Initial implementation renders each dynamic booking field as a single-select control.
- Admin may create any number of fields such as `Size of House to Paint`, `Type of Paint to Use`, `Sealer Needed?`, etc.
- Submitted values must match the currently allowed choices for the selected field.
- The booking form does **not** include an add-ons checklist or preferred-date field in the current baseline. Pricing add-ons remain a separate pricing domain and are not automatically injected into booking.
- Field definitions use soft deletes so historical lead answers can remain understandable.

### `lead_form_answers`

`id`, `lead_id`, nullable `lead_form_field_id`, `field_key`, `field_label`, `answer` text, `sort_order`, timestamps, soft deletes.

Rules:

- Unique recommendation: (`lead_id`, `field_key`).
- `lead_form_field_id` may become null if a field is permanently removed; `field_key`, `field_label` and `answer` are snapshots retained with the lead.
- Answers are written only after validating against the active field definition and its allowed `options`.

### `lead_services`

`lead_id`, `service_id`, nullable `notes`, composite unique.

### Lead attachments

`Lead` implements `HasMedia` with a private `attachments` collection. Attachment media is authorized through the parent Lead policy/signed download route. Do not create a duplicate `lead_attachments` pivot unless future requirements require one media file to be shared by multiple leads.

### `lead_status_histories`

`id`, `lead_id`, nullable `changed_by_type`, nullable `changed_by_id`, nullable `from_status`, `to_status`, nullable `reason`, `created_at`. `changed_by_type/id` is a constrained polymorphic actor (normally `Admin`, optionally `User` for future self-service actions).

### `lead_notes`

`id`, `lead_id`, nullable `author_type`, nullable `author_id`, `note`, `visible_to_user`, timestamps, soft deletes. `author_type/id` is a constrained polymorphic actor supporting `Admin` and `User`; policies determine visibility and write access.

## 11. SEO, redirects and tracking

### `seo_metas`

`id`, polymorphic `seoable`, nullable `meta_title`, nullable `meta_description`, nullable `canonical_url`, `robots`, nullable `og_title`, nullable `og_description`, nullable `schema_overrides` JSON, `include_in_sitemap`, nullable `sitemap_priority` decimal(2,1), nullable `sitemap_changefreq`, timestamps.

Unique: (`seoable_type`, `seoable_id`).

`SeoMeta` may implement `HasMedia` with a single `social_image` collection. This avoids a separate `og_media_id` ownership path and keeps public social images inside the canonical media layer.

### `redirects`

`id`, unique `from_path`, `to_url`, `status_code` default 301, `hits` default 0, `is_active`, nullable `last_hit_at`, timestamps.

Allowed status: 301, 302, 307, 308.

### `tracking_providers`

`id`, unique `provider`, nullable `public_identifier`, nullable encrypted `secret`, nullable JSON encrypted `config`, `is_enabled`, `test_mode`, timestamps.

Providers: `meta_pixel`, `meta_capi`, `ga4`, `gtm`, `tiktok`.

### `tracking_event_rules`

`id`, `tracking_provider_id`, `internal_event`, `provider_event`, JSON `parameter_map`, `requires_marketing_consent`, `is_enabled`, timestamps.

Unique: (`tracking_provider_id`, `internal_event`).

## 12. Operations

### `audit_logs`

`id`, nullable `actor_type`, nullable `actor_id`, `action`, `auditable_type`, nullable `auditable_id`, nullable JSON `old_values`, nullable JSON `new_values`, nullable `ip_address`, nullable `user_agent`, `created_at`. `actor_type/id` supports the separate `Admin` and `User` authenticatable models.

Sensitive values and secrets must be redacted before insert.

Laravel default operational tables:
- `password_reset_tokens`
- `sessions`
- `jobs`
- `job_batches`
- `failed_jobs`
- `cache`
- `cache_locks`
- `notifications`

## 13. Migration order

1. `users` table, plus published Spatie Permission tables and role/permission seeders
2. Published Spatie Media Library `media` table
3. Settings and menus
4. Pages and sections
5. Services and locations
6. Pricing
7. Galleries and videos
8. Testimonials, testimonialables and FAQs
9. Blog
10. Contact channels
11. Leads, booking form fields/answers, lead services, status history and notes; lead attachment media is owned directly by Lead
12. SEO, redirects and tracking
13. Audit and operational tables
14. Campaigns and campaign sections

## 14. Dynamic hero/media mapping

| Owner model | Collection | Cardinality | Purpose |
|---|---|---:|---|
| Page | `hero_desktop` | 1 or ordered many | Home/normal page desktop hero |
| Page | `hero_mobile` | 1 or ordered many | Home/normal page mobile hero |
| Service | `hero_desktop` | 1 | Service-specific desktop hero |
| Service | `hero_mobile` | 1 | Service mobile hero |
| Location | `hero_desktop` | 1 | Location desktop hero |
| Location | `hero_mobile` | 1 | Location mobile hero |
| Testimonial | `testimonial_screenshot` | 1 | WhatsApp chat proof screenshot |
| Branding owner | `default_hero` | 1 | Final fallback |

`hero_config` stores heading, overlay, focal position, CTA and slider mode. File metadata remains in Spatie's `media` table.

## 15. Campaign schema

### `campaigns`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| title | varchar(190) | required |
| slug | varchar(190) | unique |
| custom_route | varchar(255) | nullable unique, validated against reserved routes |
| summary | text | nullable |
| status | varchar(30) | draft, published, inactive, archived |
| hero_config | json | heading, overlay, rating, CTA and playback configuration |
| published_at | timestamp | nullable, indexed |
| starts_at | timestamp | nullable |
| ends_at | timestamp | nullable |
| created_by/updated_by | bigint unsigned | nullable FK admins |
| created_at/updated_at/deleted_at | timestamp | soft deletes |

`Campaign` implements Spatie `HasMedia` with collections: `hero_images`, `hero_video`, `hero_video_poster`, `social_image`.

### `campaign_settings`

Singleton row (`id = 1`): `id`, `default_campaign_id` (FK campaigns), `fallback_mode`, `fallback_page_id`, `updated_by` (FK admins), timestamps.

### `campaign_sections`

`id`, `campaign_id`, `section_key`, `heading`, `subheading`, JSON `payload`, `is_enabled`, `sort_order`, timestamps, soft deletes.

Index: (`campaign_id`, `is_enabled`, `sort_order`).

Supported `section_key` values: `hero`, `hero_benefits`, `service_grid`, `category_brand`, `pricing`, `gallery`, `video_gallery`, `testimonials`, `whatsapp_reviews`, `faq`, `cta`, `lead_form`.

The registry is allowlisted: every supported key must have matching validation schema, admin editor configuration and Blade component. Unknown keys must be rejected rather than rendered dynamically.