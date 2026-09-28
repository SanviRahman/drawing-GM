# Database Schema Specification

## 1. General standards

- Engine: InnoDB
- Character set: `utf8mb4`
- Collation: project default compatible with MySQL 8
- IDs: `BIGINT UNSIGNED AUTO_INCREMENT` unless the team standardizes on ULIDs before migrations[cite: 2]
- Money: `DECIMAL(12,2)`, never floating point[cite: 2]
- Phone numbers: normalized E.164 text, never integer[cite: 2]
- Timestamps: UTC in storage; render using configured timezone[cite: 2]
- Flexible block/provider configuration: JSON with application-level validation[cite: 2]
- Secrets: encrypted casts and excluded from serialization/logs[cite: 2]
- Soft deletes: content, media, services, locations, posts, testimonials and leads where recovery is valuable[cite: 2]

## 2. Identity and access

### `admins`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar(150) | required |
| username | varchar(100) | unique, required |
| email | varchar(190) | unique, required |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | required |
| phone | varchar(32) | nullable, E.164 where possible |
| status | boolean | default true, indexed |
| avatar_media_id | bigint unsigned | nullable FK media |
| remember_token | varchar(100) | nullable |
| last_login_at | timestamp | nullable |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable (soft deletes) |

### `users`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| name | varchar(150) | required[cite: 2] |
| email | varchar(190) | unique, required[cite: 2] |
| email_verified_at | timestamp | nullable[cite: 2] |
| password | varchar(255) | required[cite: 2] |
| phone | varchar(32) | nullable, E.164 where possible[cite: 2] |
| avatar_media_id | bigint unsigned | nullable FK media[cite: 2] |
| is_active | boolean | default true, indexed[cite: 2] |
| remember_token | varchar(100) | nullable[cite: 2] |
| last_login_at | timestamp | nullable[cite: 2] |
| created_at/updated_at | timestamp | required[cite: 2] |
| deleted_at | timestamp | nullable (soft deletes) |

### Spatie Permission tables

Use the migrations published by `spatie/laravel-permission` as the source of truth[cite: 2]:

- `roles`: `id`, `name`, `guard_name`; unique (`name`, `guard_name`)[cite: 2].
- `permissions`: `id`, `name`, `guard_name`, nullable `group_name`; unique (`name`, `guard_name`)[cite: 2].
- `model_has_roles`: `role_id`, `model_type`, `model_id`[cite: 2].
- `model_has_permissions`: `permission_id`, `model_type`, `model_id`[cite: 2].
- `role_has_permissions`: `permission_id`, `role_id`[cite: 2].

Guards:
- `admin` guard: binds to `App\Models\Admin` with permissions for dashboard, site tools, admins, roles, content, leads, and settings.
- `web` guard: binds to `App\Models\User` with permissions for customer portal, profile, and enquiry tracking[cite: 2, 3].

## 3. Site configuration

### `site_settings`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| group_name | varchar(80) | branding, contact, footer, consent, etc.[cite: 2] |
| setting_key | varchar(150) | unique[cite: 2] |
| setting_value | longtext | JSON/text storage[cite: 2] |
| value_type | varchar(30) | string, boolean, integer, json, encrypted[cite: 2] |
| is_public | boolean | whether safe for public settings payload[cite: 2] |
| created_at/updated_at | timestamp | required[cite: 2] |

Recommended keys include `site.name`, `site.timezone`, `site.currency`, `header.sticky`, `footer.description`, `widget.position`, `widget.scroll_threshold`, and `registration.enabled`[cite: 2]. Logo/default hero files use Spatie collections rather than raw path/ID settings[cite: 2].

### `menus`

`id`, `name`, unique `location`, `is_active`, timestamps[cite: 2].

Locations: `header-primary`, `header-top`, `footer-services`, `footer-company`, `footer-legal`[cite: 2].

### `menu_items`

`id`, `menu_id`, nullable `parent_id`, `label`, `link_type`, nullable `linkable_type`, nullable `linkable_id`, nullable `url`, nullable `icon`, `target`, `css_class`, `sort_order`, `is_active`, timestamps[cite: 2].

Indexes:
- (`menu_id`, `parent_id`, `sort_order`)[cite: 2]
- (`linkable_type`, `linkable_id`)[cite: 2]

Validate `target` against `_self` and `_blank`[cite: 2].

## 4. Pages and sections

### `pages`

`id`, `title`, unique `slug`, nullable `excerpt`, `template`, JSON `hero_config`, `status`, nullable `published_at`, `is_homepage`, `show_header`, `show_footer`, nullable `created_by`, nullable `updated_by`, timestamps, soft deletes[cite: 2].

Page implements `HasMedia` with `hero_desktop` and `hero_mobile` collections[cite: 2]. Home therefore owns its image independently from service pages[cite: 2].

Status values: `draft`, `published`, `archived`[cite: 2].

Indexes:
- unique `slug`[cite: 2]
- (`status`, `published_at`)[cite: 2]
- unique conditional homepage rule enforced at application/transaction level[cite: 2]

### `section_definitions`

`id`, unique `key`, `name`, nullable `description`, JSON `schema_json`, `is_active`, timestamps[cite: 2].

Initial keys:
- `hero`[cite: 2]
- `rich_text`[cite: 2]
- `benefit_grid`[cite: 2]
- `service_carousel`[cite: 2]
- `pricing`[cite: 2]
- `gallery`[cite: 2]
- `before_after`[cite: 2]
- `video_gallery`[cite: 2]
- `testimonials` (Google / Star reviews)[cite: 2]
- `whatsapp_reviews` (WhatsApp chat screenshot proof showcase)
- `paint_calculator` (Property size and paint calculator)
- `faq`[cite: 2]
- `cta`[cite: 2]
- `contact_form`[cite: 2]
- `spacer`[cite: 2]

### `page_sections`

`id`, `page_id`, `section_definition_id`, nullable `heading`, nullable `subheading`, JSON `payload`, `theme`, `sort_order`, `is_active`, nullable `starts_at`, nullable `ends_at`, timestamps, soft deletes[cite: 2].

Index: (`page_id`, `is_active`, `sort_order`)[cite: 2].

### `section_media`

`id`, `page_section_id`, `media_id`, `role`, nullable `caption_override`, `sort_order`, timestamps[cite: 2].

Unique recommendation: (`page_section_id`, `media_id`, `role`)[cite: 2].

## 5. Services, locations and prices

### `services`

`id`, `name`, unique `slug`, nullable `summary`, nullable `content`, nullable `icon`, JSON `hero_config`, `status`, `is_featured`, `sort_order`, nullable `published_at`, timestamps, soft deletes[cite: 2].

Service implements `HasMedia` with `hero_desktop`, `hero_mobile` and `gallery` collections[cite: 2]. HDB Painting, Condo Painting, Commercial, Plastering, and other services own independent hero images.

### `service_features`

`id`, `service_id`, `title`, nullable `description`, nullable `icon`, `sort_order`, `is_active`, timestamps[cite: 2].

### `locations`

`id`, `name`, unique `slug`, nullable `region`, nullable `postal_codes` JSON, nullable `summary`, nullable `content`, JSON `hero_config`, `status`, `sort_order`, nullable `published_at`, timestamps, soft deletes[cite: 2].

Location implements `HasMedia` with `hero_desktop`, `hero_mobile` and `gallery` collections[cite: 2].

### `service_location`

`service_id`, `location_id`, JSON `override_data`, `is_active`, timestamps[cite: 2]. Composite PK/unique on both IDs[cite: 2].

### `pricing_packages`

`id`, nullable `service_id`, nullable `location_id`, `name`, nullable `subtitle`, nullable `badge`, nullable `description`, `currency` char(3), `is_featured`, `is_active`, `sort_order`, timestamps, soft deletes[cite: 2].

A null location means global/service-level pricing[cite: 2]. Location-specific records override global packages only when explicitly configured[cite: 2].

### `pricing_items`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| pricing_package_id | bigint unsigned | FK[cite: 2] |
| label | varchar(190) | e.g. 3-Room HDB, 4-Room HDB, Condo 2-Bedroom[cite: 2] |
| amount | decimal(12,2) | nullable for call-for-price[cite: 2] |
| amount_max | decimal(12,2) | nullable range maximum[cite: 2] |
| price_type | varchar(30) | fixed, from, range, call[cite: 2] |
| unit | varchar(80) | room, property, sq ft, job[cite: 2] |
| prefix/suffix | varchar(40) | optional display text (e.g. "Nett", "From")[cite: 2] |
| sort_order | int | default 0[cite: 2] |
| is_active | boolean | default true[cite: 2] |

### `pricing_addons`

`id`, `name`, nullable `description`, nullable `amount`, nullable `amount_max`, `price_type`, nullable `unit`, `is_active`, `sort_order`, timestamps, soft deletes[cite: 2].
(e.g., Ceiling painting, Sealer coat, Door and gate frame, Balcony paint).

### `pricing_package_addon`

`pricing_package_id`, `pricing_addon_id`, optional JSON `override_data`, composite unique[cite: 2].

## 6. Media and video

### Spatie `media`

Use the migration published by `spatie/laravel-medialibrary`; this is the canonical media table[cite: 2].

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| model_type/model_id | morphs | owning model[cite: 2] |
| uuid | uuid | package field[cite: 2] |
| collection_name | varchar | `hero_desktop`, `gallery`, `whatsapp_proof`, etc.[cite: 2] |
| name/file_name | varchar | display/stored names[cite: 2] |
| mime_type | varchar | detected MIME[cite: 2] |
| disk/conversions_disk | varchar | storage disks[cite: 2] |
| size | bigint unsigned | bytes[cite: 2] |
| manipulations | json | package-managed[cite: 2] |
| custom_properties | json | alt, caption, attribution, focal point, processing metadata[cite: 2] |
| generated_conversions | json | package-managed[cite: 2] |
| responsive_images | json | package-managed[cite: 2] |
| order_column | unsigned integer | ordering[cite: 2] |
| created_at/updated_at | timestamp | required[cite: 2] |

Do not create a second generic media table[cite: 2]. Private lead attachments use a private disk and authorized downloads[cite: 2].

### `galleries`

`id`, `name`, nullable `attachable_type`, nullable `attachable_id`, `layout`, `is_active`, timestamps, soft deletes[cite: 2].

### `gallery_items`

`id`, `gallery_id`, `item_type`, nullable `media_id`, nullable `before_media_id`, nullable `after_media_id`, nullable `title`, nullable `caption`, `sort_order`, `is_active`, timestamps[cite: 2].

Rules:
- `item_type=image` requires `media_id`[cite: 2].
- `item_type=before_after` requires both before/after media IDs[cite: 2].

### `videos`

`id`, nullable polymorphic attachable, `title`, nullable `caption`, `source_type`, nullable `provider`, nullable `provider_video_id`, nullable `source_url`, nullable `disk`, nullable `path`, nullable `poster_media_id`, nullable `mime_type`, nullable `size_bytes`, nullable `duration_seconds`, `autoplay`, `muted`, `controls`, `loop`, `processing_status`, nullable `processing_error`, `sort_order`, `is_active`, timestamps, soft deletes[cite: 2].

Rules:
- Embed requires allowlisted provider + provider ID/URL[cite: 2].
- Upload requires disk/path/MIME[cite: 2].
- Autoplay requires muted[cite: 2].

## 7. Testimonials and FAQs

### `testimonials`

Designed to support both **Google/Star Reviews** and **WhatsApp Chat Screenshot Social Proof** as seen on high-converting Singapore painting portals[cite: 4]:

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK |
| type | varchar(30) | default 'text' ('text', 'whatsapp_screenshot', 'video') |
| customer_name | varchar(150) | required |
| customer_title | varchar(150) | nullable (e.g. "4-Room HDB Owner, Punggol", "Condo Resident") |
| rating | tinyint unsigned | nullable, check 1–5 |
| review | text | nullable (optional when type is screenshot) |
| photo_media_id | bigint unsigned | nullable FK media (customer avatar) |
| screenshot_media_id | bigint unsigned | nullable FK media (WhatsApp chat proof screenshot) |
| source | varchar(50) | default 'google' ('google', 'whatsapp', 'facebook', 'direct') |
| source_url | varchar(255) | nullable link to original review |
| reviewed_at | timestamp | nullable |
| is_featured | boolean | default false, indexed |
| is_active | boolean | default true, indexed |
| sort_order | int | default 0 |
| created_at/updated_at | timestamp | required |
| deleted_at | timestamp | nullable (soft deletes) |

### `testimonialables`

Polymorphic mapping for associating testimonials with specific services (e.g. HDB painting testimonials vs. Condo painting testimonials) or location pages:

`id`, `testimonial_id`, `testimonialable_type`, `testimonialable_id`, `sort_order`, timestamps.
Unique: (`testimonial_id`, `testimonialable_type`, `testimonialable_id`).

### `faqs`

`id`, `question`, `answer`, `is_active`, timestamps, soft deletes[cite: 2].

### `faqables`

`faq_id`, `faqable_type`, `faqable_id`, `sort_order`, timestamps; unique (`faq_id`, `faqable_type`, `faqable_id`)[cite: 2].

## 8. Blog

### `categories`

`id`, `name`, unique `slug`, nullable `description`, `is_active`, timestamps, soft deletes[cite: 2].

### `posts`

`id`, `author_id`, nullable `category_id`, nullable `featured_media_id`, `title`, unique `slug`, nullable `excerpt`, `body`, `status`, nullable `published_at`, nullable `reading_minutes`, `allow_comments` default false, timestamps, soft deletes[cite: 2].

### `post_media`

`post_id`, `media_id`, `role`, `sort_order`, composite unique[cite: 2].

## 9. Contact widget

### `contact_channels`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| type | varchar(30) | whatsapp, phone, email, custom[cite: 2] |
| label | varchar(120) | Customer-facing label (e.g. "WhatsApp Sales 1", "Fast Response")[cite: 2] |
| region | varchar(120) | nullable e.g. North/South/East/West[cite: 2] |
| value | varchar(255) | normalized number/email/URL[cite: 2] |
| display_value | varchar(120) | nullable formatted display[cite: 2] |
| message_template | text | nullable, WhatsApp placeholders allowed[cite: 2] |
| icon | varchar(100) | controlled icon key[cite: 2] |
| colour | varchar(20) | validated hex/token[cite: 2] |
| availability_text | varchar(120) | nullable (e.g. "Mon-Sun 8am-10pm")[cite: 2] |
| is_default | boolean | default false[cite: 2] |
| is_active | boolean | default true[cite: 2] |
| track_clicks | boolean | default true[cite: 2] |
| sort_order | int | default 0[cite: 2] |
| created_at/updated_at/deleted_at | timestamp | soft deletes[cite: 2] |

Indexes: (`type`, `is_active`, `sort_order`), `is_default`[cite: 2].

### `contact_targets`

`id`, `contact_channel_id`, `targetable_type`, `targetable_id`, timestamps[cite: 2].

Unique: (`contact_channel_id`, `targetable_type`, `targetable_id`)[cite: 2].

Selection algorithm:
1. Active channels targeting current location/service/page[cite: 2].
2. Active global channels without targets[cite: 2].
3. Sort default first, then `sort_order`, then ID[cite: 2].

### Optional `contact_clicks`

`id`, nullable `contact_channel_id`, `session_hash`, nullable `page_url`, nullable `referer`, nullable `utm` JSON, nullable `ip_hash`, `clicked_at`[cite: 2].

## 10. Leads

### `leads`

| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| user_id | bigint unsigned | nullable FK users[cite: 2] |
| reference | varchar(50) | unique quotation reference (e.g. BP-2026-0042)[cite: 2] |
| name | varchar(150) | required[cite: 2] |
| email | varchar(190) | nullable[cite: 2] |
| phone | varchar(32) | required, E.164 normalized[cite: 2] |
| location_id | bigint unsigned | nullable FK locations[cite: 2] |
| property_type | varchar(80) | nullable (e.g. HDB 3-Room, Condo, Landed)[cite: 2] |
| preferred_date | date | nullable[cite: 2] |
| message | text | nullable[cite: 2] |
| metadata | json | nullable (house condition e.g. furnished/vacant, paint choice e.g. Nippon/Dulux, ceiling requirement, calculator output) |
| status | varchar(30) | default 'new' ('new', 'contacted', 'qualified', 'quoted', 'won', 'lost', 'spam', 'closed')[cite: 2] |
| assigned_to | bigint unsigned | nullable FK admins (assigned staff/agent)[cite: 2] |
| source_page_url | varchar(255) | nullable lead landing URL[cite: 2] |
| utm | json | nullable UTM parameters[cite: 2] |
| consent | json | nullable consent log[cite: 2] |
| pricing_snapshot | json | nullable submitted package quote snapshot[cite: 2] |
| created_at/updated_at | timestamp | required[cite: 2] |
| deleted_at | timestamp | nullable (soft deletes)[cite: 2] |

Indexes: `status`, `created_at`, `assigned_to`, `user_id`, `location_id`[cite: 2].

### `lead_services`

`lead_id`, `service_id`, nullable `notes`, composite unique[cite: 2].

### `lead_attachments`

`id`, `lead_id`, `media_id`, timestamps[cite: 2]. Lead files must use private visibility[cite: 2].

### `lead_status_histories`

`id`, `lead_id`, nullable `changed_by` (FK admin/user), nullable `from_status`, `to_status`, nullable `reason`, `created_at`[cite: 2].

### `lead_notes`

`id`, `lead_id`, `user_id` (or `admin_id`), `note`, `visible_to_user`, timestamps, soft deletes[cite: 2].

## 11. SEO, redirects and tracking

### `seo_metas`

`id`, polymorphic `seoable`, nullable `meta_title`, nullable `meta_description`, nullable `canonical_url`, `robots`, nullable `og_title`, nullable `og_description`, nullable `og_media_id`, nullable `schema_overrides` JSON, `include_in_sitemap`, nullable `sitemap_priority` decimal(2,1), nullable `sitemap_changefreq`, timestamps[cite: 2].

Unique: (`seoable_type`, `seoable_id`)[cite: 2].

### `redirects`

`id`, unique `from_path`, `to_url`, `status_code` default 301, `hits` default 0, `is_active`, nullable `last_hit_at`, timestamps[cite: 2].

Allowed status: 301, 302, 307, 308[cite: 2].

### `tracking_providers`

`id`, unique `provider`, nullable `public_identifier`, nullable encrypted `secret`, nullable JSON encrypted `config`, `is_enabled`, `test_mode`, timestamps[cite: 2].

Providers: `meta_pixel`, `meta_capi`, `ga4`, `gtm`, `tiktok`[cite: 2].

### `tracking_event_rules`

`id`, `tracking_provider_id`, `internal_event`, `provider_event`, JSON `parameter_map`, `requires_marketing_consent`, `is_enabled`, timestamps[cite: 2].

Unique: (`tracking_provider_id`, `internal_event`)[cite: 2].

## 12. Operations

### `audit_logs`

`id`, nullable `user_id`, nullable `admin_id`, `action`, `auditable_type`, nullable `auditable_id`, nullable JSON `old_values`, nullable JSON `new_values`, nullable `ip_address`, nullable `user_agent`, `created_at`[cite: 2].

Sensitive values and secrets must be redacted before insert[cite: 2].

Laravel default operational tables:
- `password_reset_tokens`[cite: 2]
- `sessions`[cite: 2]
- `jobs`[cite: 2]
- `job_batches`[cite: 2]
- `failed_jobs`[cite: 2]
- `cache`[cite: 2]
- `cache_locks`[cite: 2]
- `notifications`[cite: 2]

## 13. Migration order

1. `admins` and `users` tables, plus published Spatie Permission tables and role/permission seeders[cite: 2]
2. Published Spatie Media Library `media` table[cite: 2]
3. Settings and menus[cite: 2]
4. Pages and sections[cite: 2]
5. Services and locations[cite: 2]
6. Pricing[cite: 2]
7. Galleries and videos[cite: 2]
8. Testimonials, testimonialables and FAQs[cite: 2]
9. Blog[cite: 2]
10. Contact channels[cite: 2]
11. Leads and lead attachments[cite: 2]
12. SEO, redirects and tracking[cite: 2]
13. Audit and operational tables[cite: 2]
14. Campaigns and campaign sections[cite: 2]

## 14. Dynamic hero/media mapping

| Owner model | Collection | Cardinality | Purpose |
|---|---|---:|---|
| Page[cite: 2] | `hero_desktop`[cite: 2] | 1 or ordered many[cite: 2] | Home/normal page desktop hero[cite: 2] |
| Page[cite: 2] | `hero_mobile`[cite: 2] | 1 or ordered many[cite: 2] | Home/normal page mobile hero[cite: 2] |
| Service[cite: 2] | `hero_desktop`[cite: 2] | 1[cite: 2] | Service-specific desktop hero[cite: 2] |
| Service[cite: 2] | `hero_mobile`[cite: 2] | 1[cite: 2] | Service mobile hero[cite: 2] |
| Location[cite: 2] | `hero_desktop`[cite: 2] | 1[cite: 2] | Location desktop hero[cite: 2] |
| Location[cite: 2] | `hero_mobile`[cite: 2] | 1[cite: 2] | Location mobile hero[cite: 2] |
| Testimonial | `testimonial_screenshot` | 1 | WhatsApp chat proof screenshot |
| Branding owner[cite: 2] | `default_hero`[cite: 2] | 1[cite: 2] | Final fallback[cite: 2] |

`hero_config` stores heading, overlay, focal position, CTA and slider mode[cite: 2]. File metadata remains in Spatie's `media` table[cite: 2].

## 15. Campaign schema

### `campaigns`

| Column | Type | Rules |
|---|---|---|
| id | bigint unsigned | PK[cite: 2] |
| title | varchar(190) | required[cite: 2] |
| slug | varchar(190) | unique[cite: 2] |
| custom_route | varchar(255) | nullable unique, validated against reserved routes[cite: 2] |
| summary | text | nullable[cite: 2] |
| status | varchar(30) | draft, published, inactive, archived[cite: 2] |
| hero_config | json | heading, overlay, rating, CTA and playback configuration[cite: 2] |
| published_at | timestamp | nullable, indexed[cite: 2] |
| starts_at | timestamp | nullable[cite: 2] |
| ends_at | timestamp | nullable[cite: 2] |
| created_by/updated_by | bigint unsigned | nullable FK admins/users[cite: 2] |
| created_at/updated_at/deleted_at | timestamp | soft deletes[cite: 2] |

`Campaign` implements Spatie `HasMedia` with collections: `hero_images`, `hero_video`, `hero_video_poster`, `social_image`[cite: 2].

### `campaign_settings`

Singleton row (`id = 1`): `id`, `default_campaign_id` (FK campaigns), `fallback_mode`, `fallback_page_id`, `updated_by`, timestamps[cite: 2].

### `campaign_sections`

`id`, `campaign_id`, `section_key`, `heading`, `subheading`, JSON `payload`, `is_enabled`, `sort_order`, timestamps, soft deletes[cite: 2].

Index: (`campaign_id`, `is_enabled`, `sort_order`)[cite: 2].

Supported `section_key` values: `hero`, `hero_benefits`, `service_grid`, `pricing`, `gallery`, `video_gallery`, `testimonials`, `whatsapp_reviews`, `faq`, `cta`, `lead_form`[cite: 2].