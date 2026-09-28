# Dynamic Service Website — Project Overview

## 1. Document purpose

This document defines the development baseline for a Laravel-based, conversion-focused service website inspired by the reviewed painting and flooring websites. It is a functional reference, not a licence to copy third-party branding, images, text, customer reviews, or source code.

## 2. Product vision

Build a fast, SEO-friendly and fully dynamic service website where administrators can manage the header, footer, pages, reusable sections, services, prices, images, videos, testimonials, FAQs, location landing pages, WhatsApp numbers, tracking pixels and enquiries without editing source code.

## 3. Recommended technical baseline

- PHP 8.3+
- Laravel 12
- MySQL 8+
- Blade + Alpine.js
- Tailwind CSS or Bootstrap 5 (choose one and use consistently)
- Laravel authentication with policies
- `spatie/laravel-permission` for Admin/User roles, permissions, middleware and permission cache
- `spatie/laravel-medialibrary` for image/file/video associations, named collections, conversions and responsive images
- Redis for production cache/queue where available
- Local public disk during development; S3-compatible storage in production
- FFmpeg worker only if uploaded videos require transcoding/thumbnails

The exact Laravel and PHP versions must be confirmed before implementation.

## 4. Actors and roles

### 4.1 Guest

- Browse all public pages.
- View services, pricing, gallery, videos, testimonials, FAQs and blog posts.
- Open the floating contact widget.
- Select an administrator-configured WhatsApp contact.
- Call, email or submit a quotation request.
- Accept or reject optional analytics/marketing cookies where required.

### 4.2 User

- Register, log in and reset password if the customer portal is enabled.
- Manage own profile.
- Submit quotation requests with attachments.
- View own enquiry history and status.
- Read administrator replies or notes explicitly marked visible to the user.

### 4.3 Admin

- Access the administration panel.
- Manage users and roles.
- Manage global site settings, header, footer and menus.
- Manage pages and reusable page sections.
- Manage services, pricing, add-ons and locations.
- Manage images, galleries, before/after pairs and videos.
- Manage testimonials, FAQs, posts and categories.
- Manage WhatsApp/contact channels and floating widget behaviour.
- Manage leads and status history.
- Manage SEO, redirects, sitemap and tracking integrations.
- View a dashboard with lead and content summaries.

## 5. Public website information architecture

### 5.1 Global UI

- Announcement/contact top bar.
- Dynamic header with logo, primary menu, dropdowns and CTA.
- Dynamic footer with logo, description, menus, contact details, social links, legal links and location/service links.
- Floating contact launcher.
- Floating back-to-top button.
- Cookie/consent banner where tracking rules require it.

### 5.2 Required pages

- Home
- Services listing
- Service details
- Pricing or discounted pricing
- Locations listing (optional but recommended)
- Location landing pages
- Gallery/before-and-after
- Blog listing
- Blog article
- About
- Contact/quotation
- Privacy policy
- Terms (recommended)
- HTML sitemap
- Login/register/profile/enquiries when the user portal is enabled

### 5.3 Home page sections

All sections are individually manageable, sortable and toggleable:

1. Hero/banner slider or static hero
2. Main value proposition and CTAs
3. Key benefits
4. Service/product cards carousel
5. Customer review integration/manual testimonials
6. Audience/problem qualification section
7. Pricing packages
8. Optional add-ons
9. Included services/benefits
10. Before/after gallery
11. Video showcase
12. Why choose us
13. Guarantees
14. FAQs
15. Final WhatsApp/call/quotation CTA
16. Contact/quotation form

## 6. Dynamic header requirements

### 6.1 Page-specific header/hero image

The header navigation and the visual hero/banner are separate components. Each content context can have its own dynamic hero media:

- Home page can show its own desktop and mobile hero image or slider.
- Plastering can show a different hero image.
- Hacking can show another hero image.
- False Ceiling, each Service, Location and normal Page can have independent hero media.
- Admin can upload, replace, remove, reorder and preview hero images.
- Each hero supports heading, subheading, overlay colour/opacity, text alignment, CTA buttons, alt text and optional focal point.
- Desktop and mobile assets use Spatie Media Library collections named `hero_desktop` and `hero_mobile`.
- Optional multi-slide heroes use ordered media plus section payload; a single-image page does not need slider JavaScript.
- Fallback order: current entity hero → reusable/default service hero → global default hero.
- The current page must never accidentally reuse another service's hero because of a stale cache key.

### 6.2 Navigation/header configuration

Admin-configurable fields:

- Header variant/theme
- Logo and mobile logo
- Top-bar text, phone and link
- Primary menu and nested menu items
- CTA label, URL, target and style
- Sticky header toggle
- Social links toggle
- Header visibility by page/device if needed

Rules:

- Header must be responsive and keyboard accessible.
- Mobile navigation must trap focus while open and close on Escape.
- External links may open in a new tab with `rel="noopener noreferrer"`.
- Menu changes must not require deployment.

## 7. Dynamic footer requirements

Admin-configurable fields:

- Footer logo and description
- One or more footer menu groups
- Phone, email, address and business hours
- Social links
- Service and location links
- Copyright text
- Privacy/terms links
- Optional newsletter or CTA block
- Layout/theme and visibility controls

## 8. Floating contact widget

### 8.1 Collapsed state

- Fixed at the configured bottom-left or bottom-right position.
- Shows a circular plus/contact icon.
- Includes an accessible label: `Open contact shortcuts`.

### 8.2 Expanded state

- Plus icon changes to a close/X icon.
- Shows configured channels such as email, phone and WhatsApp.
- Clicking the WhatsApp icon opens a list/popover of every active WhatsApp number configured in the admin panel.
- Each number displays label, region/team name, optional availability and icon.
- A selected number opens `https://wa.me/{international_number}?text={encoded_message}`.
- On small screens, the WhatsApp list should open as an accessible bottom sheet or compact popover.
- Clicking outside, pressing Escape, or clicking X closes the widget.

### 8.3 Admin-managed contact data

Each channel supports:

- Type: WhatsApp, phone, email or custom URL
- Display label
- Region/team label
- International number/address/URL
- Prefilled WhatsApp message
- Icon or icon key
- Active state
- Default state
- Sort order
- Page/service/location targeting (optional)
- Click tracking toggle

### 8.4 Back-to-top control

- Hidden near the top of the page.
- Appears after a configurable threshold, default 400 px.
- Fixed opposite or above the contact widget.
- Smoothly scrolls to the document top.
- Uses `aria-label="Back to top"` and supports keyboard activation.
- Respects `prefers-reduced-motion` by disabling animation.

## 9. Content and media management

Media management will use `spatie/laravel-medialibrary`. Models that own files implement `HasMedia` and `InteractsWithMedia`; each use case has a named collection and registered conversions.

### 9.1 Images

- Spatie Media Library-backed central media management.
- Named collections such as `logo`, `hero_desktop`, `hero_mobile`, `featured`, `gallery`, `before`, `after`, `avatar`, `video_file`, `video_poster` and `lead_attachments`.
- Upload validation and size limits.
- Alt text, caption, focal point and attribution fields.
- Responsive derivatives and WebP/AVIF where supported.
- Hero, card, gallery and before/after usage types.
- Safe replacement without breaking existing page references.

### 9.2 Videos

Supported sources:

- YouTube embed
- Vimeo embed
- Directly uploaded MP4/WebM
- Optional external video URL

Stored metadata:

- Title, caption and poster image
- Source type and provider
- Embed/video URL or storage path
- Duration, MIME type and file size
- Autoplay, muted, controls and loop settings
- Sort order and active state

Uploaded binary files must be stored on a filesystem/object-storage disk, not inside the database.

## 10. Main dynamic domains

- Global settings
- Navigation/menu
- Pages and section blocks
- Services and service features
- Locations and local landing pages
- Pricing packages, items and add-ons
- Media, galleries and before/after items
- Videos
- Testimonials/reviews
- FAQs
- Blog posts/categories
- Contact channels
- Leads/enquiries and attachments
- SEO metadata and redirects
- Tracking providers/events
- Consent settings

## 10.1 Campaign and landing-page management

Campaigns are first-class dynamic landing pages managed from the admin panel.

- Admin can create, edit, preview, publish, deactivate and delete/soft-delete campaigns.
- Campaign list supports title/slug search, status filters, bulk actions and a default selector.
- Exactly one active published campaign may be selected as the default campaign.
- The default campaign is the campaign displayed in the configured user-panel/home campaign slot.
- Setting a new default automatically replaces the previous default in one database transaction.
- An inactive, draft, expired or deleted campaign cannot remain default.
- If no valid default exists, the application displays the normal home content or a configured fallback; it must not expose a draft.
- Each campaign owns an ordered set of sections. Every section has an individual Active/Inactive toggle.
- Disabled campaign sections are retained in the admin panel but are not rendered to users.
- Campaign sections include hero, benefits, services/products, categories/brands where applicable, pricing, gallery, videos, testimonials, FAQs, CTA and lead form.

### Campaign hero media priority

The campaign hero follows a deterministic priority:

1. Active valid embed video
2. Active image slider using the campaign's ordered Spatie `hero_images` collection
3. Active uploaded video from the campaign's Spatie `hero_video` collection
4. Campaign fallback image, then global default hero

Admin can enable/disable the Hero section and configure:

- Embed provider/URL
- Multiple reusable/uploaded images and their order
- Uploaded video and poster
- Autoplay on/off
- Muted/unmuted
- Controls on/off
- Loop on/off
- Heading, rating text, overlay and CTA

Browser autoplay policies are enforced: autoplay with sound is not reliable. When autoplay is enabled, the saved/rendered configuration must use muted playback or disable autoplay and show controls.

## 11. SEO and tracking overview

- Editable meta title, description, canonical URL and social image.
- Open Graph and Twitter metadata.
- Article, FAQPage, Service, BreadcrumbList, Organization and LocalBusiness structured data where valid.
- Automatically generated XML sitemap containing every indexable page.
- Unique service/location content to avoid thin or duplicated landing pages.
- Admin-configurable Meta Pixel, Google Analytics/GTM and TikTok Pixel.
- Track PageView, ViewContent, Contact, Lead, WhatsApp click, Call click, Pricing view and Quote submission.
- Optional server-side Meta Conversions API with event deduplication.
- Marketing scripts load only according to the configured consent policy.

## 12. Security baseline

- CSRF protection for all state-changing web requests.
- Form Request validation.
- Policies and role middleware.
- Password hashing and login throttling.
- Upload MIME/content validation, safe file names and maximum size.
- No administrator-supplied arbitrary JavaScript by default.
- Validate provider IDs instead of accepting raw tracking scripts.
- Escape frontend content unless a field explicitly uses sanitized rich text.
- Honeypot/rate limiting and optional CAPTCHA for public lead forms.
- Audit important admin actions.

## 13. Non-functional goals

- Responsive from 320 px upward.
- WCAG 2.1 AA-oriented controls and contrast.
- Core Web Vitals-conscious images, fonts and scripts.
- Cached public content with automatic invalidation after admin updates.
- Queue long-running media, notification and integration tasks.
- Daily database backup and object-storage lifecycle policy.
- Production error monitoring and structured logging.

## 14. Out of scope for the first release

Unless later approved:

- Online payment/checkout
- Contractor marketplace
- Real-time chat
- Multi-vendor management
- Native mobile application
- Automatic Google review scraping where provider terms do not permit it
- Full drag-and-drop visual page builder

## 15. Delivery phases

1. Confirm stack, branding and exact scope.
2. Authentication, roles and base schema.
3. Admin settings, menus, header/footer and media library.
4. Services, pricing, locations and reusable sections.
5. Public pages and responsive UI.
6. Blog, FAQs, testimonials and enquiries.
7. Video upload/embed and processing.
8. Tracking, consent, SEO and sitemap.
9. Security, performance, QA and deployment.

## 16. Definition of success

- Admin can update every visible public component without code changes.
- Every active WhatsApp number is rendered from the database.
- Floating contact widget and back-to-top control work on desktop and mobile.
- Pages have unique, editable SEO metadata and appear correctly in the sitemap.
- Uploaded media is validated, optimized and reusable.
- Leads are stored, protected and trackable.
- Role boundaries prevent users from accessing administration functions.
