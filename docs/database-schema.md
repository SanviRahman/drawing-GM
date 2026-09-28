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
- Soft deletes: content, media, services, locations, posts and leads where recovery is valuable

## 2. Identity and access

### `users`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar(150) | required |
| email | varchar(190) | unique, required |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | required |
| phone | varchar(32) | nullable, E.164 where possible |
| avatar_media_id | bigint unsigned | nullable FK media |
| is_active | boolean | default true, indexed |
| remember_token | varchar(100) | nullable |
| last_login_at | timestamp | nullable |
| created_at/updated_at | timestamp | required |

### Spatie Permission tables

Use the migrations published by `spatie/laravel-permission` as the source of truth:

- `roles`: `id`, `name`, `guard_name`; unique (`name`, `guard_name`).
- `permissions`: `id`, `name`, `guard_name`; unique (`name`, `guard_name`).
- `model_has_roles`: `role_id`, `model_type`, `model_id`.
- `model_has_permissions`: `permission_id`, `model_type`, `model_id`.
- `role_has_permissions`: `permission_id`, `role_id`.

Seed `admin` and `user` roles plus granular permissions. Do not add a separate `users.role` enum or custom `role_user` table. The `User` model uses `HasRoles`.

## 3. Site configuration

### `site_settings`

| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| group_name | varchar(80) | branding, contact, footer, consent, etc. |
| setting_key | varchar(150) | unique |
| setting_value | longtext | JSON/text storage |
| value_type | varchar(30) | string, boolean, integer, json, encrypted |
| is_public | boolean | whether safe for public settings payload |
| created_at/updated_at | timestamp | |

Recommended keys include `site.name`, `site.timezone`, `site.currency`, `header.sticky`, `footer.description`, `widget.position`, `widget.scroll_threshold`, and `registration.enabled`. Logo/default hero files use Spatie collections rather than raw path/ID settings.

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

`id`, `title`, unique `slug`, nullable `excerpt`, `template`, JSON `hero_config`, `status`, nullable `published_at`, `is_homepage`, `show_header`, `show_footer`, nullable `created_by`, nullable `updated_by`, timestamps, soft deletes.

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
- `testimonials`
- `faq`
- `cta`
- `contact_form`
- `spacer`

### `page_sections`

`id`, `page_id`, `section_definition_id`, nullable `heading`, nullable `subheading`, JSON `payload`, `theme`, `sort_order`, `is_active`, nullable `starts_at`, nullable `ends_at`, timestamps, soft deletes.

Index: (`page_id`, `is_active`, `sort_order`).

### `section_media`

`id`, `page_section_id`, `media_id`, `role`, nullable `caption_override`, `sort_order`, timestamps.

Unique recommendation: (`page_section_id`, `media_id`, `role`).

## 5. Services, locations and prices

### `services`

`id`, `name`, unique `slug`, nullable `summary`, nullable `content`, nullable `icon`, JSON `hero_config`, `status`, `is_featured`, `sort_order`, nullable `published_at`, timestamps, soft deletes.

Service implements `HasMedia` with `hero_desktop`, `hero_mobile` and `gallery` collections. Plastering, Hacking and every other service therefore own independent hero images.

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
| id | bigint | PK |
| pricing_package_id | bigint | FK |
| label | varchar(190) | e.g. 3 Room HDB |
| amount | decimal(12,2) | nullable for call-for-price |
| amount_max | decimal(12,2) | nullable range maximum |
| price_type | varchar(30) | fixed, from, range, call |
| unit | varchar(80) | room, property, sq ft, job |
| prefix/suffix | varchar(40) | optional display text |
| sort_order | int | default 0 |
| is_active | boolean | default true |

### `pricing_addons`

`id`, `name`, nullable `description`, nullable `amount`, nullable `amount_max`, `price_type`, nullable `unit`, `is_active`, `sort_order`, timestamps, soft deletes.

### `pricing_package_addon`

`pricing_package_id`, `pricing_addon_id`, optional JSON `override_data`, composite unique.

## 6. Media and video

### Spatie `media`

Use the migration published by `spatie/laravel-medialibrary`; this is the canonical media table.

| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| model_type/model_id | morphs | owning model |
| uuid | uuid | package field |
| collection_name | varchar | `hero_desktop`, `gallery`, etc. |
| name/file_name | varchar | display/stored names |
| mime_type | varchar | detected MIME |
| disk/conversions_disk | varchar | storage disks |
| size | bigint | bytes |
| manipulations | json | package-managed |
| custom_properties | json | alt, caption, attribution, focal point, processing metadata |
| generated_conversions | json | package-managed |
| responsive_images | json | package-managed |
| order_column | unsigned integer | ordering |
| created_at/updated_at | timestamp | |

Do not create a second generic media table. If necessary, use a custom model extending `Spatie\MediaLibrary\MediaCollections\Models\Media`. Private lead attachments use a private disk and authorized downloads.

### `galleries`

`id`, `name`, nullable `attachable_type`, nullable `attachable_id`, `layout`, `is_active`, timestamps, soft deletes.

### `gallery_items`

`id`, `gallery_id`, `item_type`, nullable `media_id`, nullable `before_media_id`, nullable `after_media_id`, nullable `title`, nullable `caption`, `sort_order`, `is_active`, timestamps.

Rules:

- `item_type=image` requires `media_id`.
- `item_type=before_after` requires both before/after media IDs.

### `videos`

`id`, nullable polymorphic attachable, `title`, nullable `caption`, `source_type`, nullable `provider`, nullable `provider_video_id`, nullable `source_url`, nullable `disk`, nullable `path`, nullable `poster_media_id`, nullable `mime_type`, nullable `size_bytes`, nullable `duration_seconds`, `autoplay`, `muted`, `controls`, `loop`, `processing_status`, nullable `processing_error`, `sort_order`, `is_active`, timestamps, soft deletes.

Rules:

- Embed requires allowlisted provider + provider ID/URL.
- Upload requires disk/path/MIME.
- Autoplay requires muted.

## 7. Testimonials and FAQs

### `testimonials`

`id`, `customer_name`, nullable `customer_title`, tinyint `rating`, `review`, nullable `photo_media_id`, `source`, nullable `source_url`, nullable `reviewed_at`, `is_featured`, `is_active`, `sort_order`, timestamps, soft deletes.

Rating check: 1–5.

### `faqs`

`id`, `question`, `answer`, `is_active`, timestamps, soft deletes.

### `faqables`

`faq_id`, `faqable_type`, `faqable_id`, `sort_order`, timestamps; unique (`faq_id`, `faqable_type`, `faqable_id`).

## 8. Blog

### `categories`

`id`, `name`, unique `slug`, nullable `description`, `is_active`, timestamps, soft deletes.

### `posts`

`id`, `author_id`, nullable `category_id`, nullable `featured_media_id`, `title`, unique `slug`, nullable `excerpt`, `body`, `status`, nullable `published_at`, nullable `reading_minutes`, `allow_comments` default false, timestamps, soft deletes.

### `post_media`

`post_id`, `media_id`, `role`, `sort_order`, composite unique.

## 9. Contact widget

### `contact_channels`

| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| type | varchar(30) | whatsapp, phone, email, custom |
| label | varchar(120) | Customer-facing label |
| region | varchar(120) | nullable e.g. East/West |
| value | varchar(255) | normalized number/email/URL |
| display_value | varchar(120) | nullable formatted display |
| message_template | text | nullable, WhatsApp placeholders allowed |
| icon | varchar(100) | controlled icon key |
| colour | varchar(20) | validated hex/token |
| availability_text | varchar(120) | nullable |
| is_default | boolean | default false |
| is_active | boolean | default true |
| track_clicks | boolean | default true |
| sort_order | int | default 0 |
| created_at/updated_at/deleted_at | timestamp | |

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

Store only if first-party reporting is required, with a retention period.

## 10. Leads

### `leads`

`id`, nullable `user_id`, unique `reference`, `name`, nullable `email`, `phone`, nullable `location_id`, nullable `property_type`, nullable `preferred_date`, `message`, `status`, nullable `assigned_to`, nullable `source_page_url`, nullable `utm` JSON, nullable `consent` JSON, nullable `pricing_snapshot` JSON, timestamps, soft deletes.

Statuses: `new`, `contacted`, `qualified`, `quoted`, `won`, `lost`, `spam`, `closed`.

Indexes: status, created_at, assigned_to, user_id, location_id.

### `lead_services`

`lead_id`, `service_id`, nullable `notes`, composite unique.

### `lead_attachments`

`id`, `lead_id`, `media_id`, timestamps. Lead files must use private visibility.

### `lead_status_histories`

`id`, `lead_id`, nullable `changed_by`, nullable `from_status`, `to_status`, nullable `reason`, `created_at`.

### `lead_notes`

`id`, `lead_id`, `user_id`, `note`, `visible_to_user`, timestamps, soft deletes.

## 11. SEO, redirects and tracking

### `seo_metas`

`id`, polymorphic `seoable`, nullable `meta_title`, nullable `meta_description`, nullable `canonical_url`, `robots`, nullable `og_title`, nullable `og_description`, nullable `og_media_id`, nullable `schema_overrides` JSON, `include_in_sitemap`, nullable `sitemap_priority` decimal(2,1), nullable `sitemap_changefreq`, timestamps.

Unique: (`seoable_type`, `seoable_id`).

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

`id`, nullable `user_id`, `action`, `auditable_type`, nullable `auditable_id`, nullable JSON `old_values`, nullable JSON `new_values`, nullable `ip_address`, nullable `user_agent`, `created_at`.

Sensitive values and secrets must be redacted before insert.

Laravel default operational tables as required:

- `password_reset_tokens`
- `sessions` when database sessions are used
- `jobs`
- `job_batches`
- `failed_jobs`
- `cache`
- `cache_locks`
- `notifications` if database notifications are enabled

## 13. Migration order

1. Users plus published Spatie Permission tables and role/permission seeders
2. Published Spatie Media Library `media` table
3. Settings and menus
4. Pages and sections
5. Services and locations
6. Pricing
7. Galleries and videos
8. Testimonials and FAQs
9. Blog
10. Contact channels
11. Leads
12. SEO, redirects and tracking
13. Audit and operational tables


## 14. Dynamic hero/media mapping

| Owner model | Collection | Cardinality | Purpose |
|---|---|---:|---|
| Page | `hero_desktop` | 1 or ordered many | Home/normal page desktop hero |
| Page | `hero_mobile` | 1 or ordered many | Home/normal page mobile hero |
| Service | `hero_desktop` | 1 | Plastering/Hacking/etc. desktop hero |
| Service | `hero_mobile` | 1 | Service mobile hero |
| Location | `hero_desktop` | 1 | Location desktop hero |
| Location | `hero_mobile` | 1 | Location mobile hero |
| Branding owner | `default_hero` | 1 | Final fallback |

`hero_config` stores heading, overlay, focal position, CTA and slider mode. File metadata remains in Spatie's `media` table. Relevant media/config changes must invalidate hero caches.

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
| created_by/updated_by | bigint unsigned | nullable user FK |
| created_at/updated_at/deleted_at | timestamp | soft deletes |

`Campaign` implements Spatie `HasMedia` with collections:

- `hero_images`: ordered slider images
- `hero_video`: single uploaded video
- `hero_video_poster`: single poster
- `social_image`: single campaign sharing image
- Additional section-owned media can be attached to the `CampaignSection` model.

### `campaign_settings`

Singleton row, normally `id = 1`:

| Column | Type | Rules |
|---|---|---|
| id | tinyint unsigned | PK |
| default_campaign_id | bigint unsigned | nullable FK campaigns, unique |
| fallback_mode | varchar(30) | normal_home or selected_page |
| fallback_page_id | bigint unsigned | nullable FK pages |
| updated_by | bigint unsigned | nullable user FK |
| created_at/updated_at | timestamp | |

A singleton reference is preferred over independent `campaigns.is_default` flags because it guarantees one source of truth. `SetDefaultCampaign` locks the settings row, validates campaign eligibility, updates the FK and invalidates caches in one transaction.

### `campaign_sections`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| campaign_id | bigint unsigned | FK, indexed |
| section_key | varchar(80) | controlled type |
| heading/subheading | varchar/text | nullable |
| payload | json | validated by section type |
| is_enabled | boolean | default true, indexed |
| sort_order | unsigned integer | default 0 |
| created_at/updated_at/deleted_at | timestamp | |

Index: (`campaign_id`, `is_enabled`, `sort_order`).

Initial `section_key` values:

- `hero`
- `hero_benefits`
- `service_grid`
- `category_brand`
- `pricing`
- `gallery`
- `video_gallery`
- `testimonials`
- `faq`
- `cta`
- `lead_form`

Disabled section data remains stored but must not be returned by public enabled-section queries.

### Campaign hero video configuration

The Hero section's validated `payload`/`hero_config` contains:

- `embed_provider`: nullable allowlisted YouTube/Vimeo/Facebook provider
- `embed_url`: nullable validated URL
- `source_priority`: fixed server-controlled order, not arbitrary executable logic
- `video_autoplay`: boolean
- `video_muted`: boolean
- `video_controls`: boolean
- `video_loop`: boolean
- `slider_autoplay`: boolean
- `slider_interval_ms`: bounded integer

Validation rule: if `video_autoplay = true`, require `video_muted = true`; otherwise disable autoplay and require visible controls. The frontend must use `playsinline` for mobile playback.

### Optional `campaignables`

For reusable product/service/category relationships:

`campaign_id`, `campaignable_type`, `campaignable_id`, `role`, `sort_order`, `is_active`, timestamps; unique (`campaign_id`, `campaignable_type`, `campaignable_id`, `role`).
