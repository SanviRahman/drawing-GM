# Admin Module to Frontend / User Panel Mapping

## 1. Purpose

This document is the implementation map between administration modules and the public website/customer portal. It prevents an admin feature from being built without a defined frontend consumer and prevents frontend values from being hard-coded when they should be CMS-managed.

## 2. Mapping

| Admin module | Primary data/models | Public/frontend usage | Customer user-panel usage |
|---|---|---|---|
| Dashboard | Lead/content/media aggregates | None directly | None |
| Admins / Roles / Permissions | Admin + Spatie roles/permissions (`admin` guard) | Controls backoffice access only | None |
| Users / User access | User (`web` guard); optional web-side Spatie roles later | No public content output | Profile/account access; users see only their own data |
| Site Settings | SiteSetting + branding media | Site name, logos, top bar, footer data, locale/timezone/currency, widget settings, consent | Shared account layout/branding |
| Menus | Menu, MenuItem | Header navigation, dropdowns, footer link groups | Optional account navigation may use a dedicated menu location |
| Pages | Page | Home, About, Contact, Privacy, Terms and other CMS pages | None unless a page is intentionally linked from account UI |
| Page Sections | PageSection, SectionDefinition | Hero, rich text, benefits, service carousel, pricing, gallery, before/after, videos, testimonials, WhatsApp review proof, paint calculator, FAQ, CTA, contact form | No direct CRUD; published output may be reused in account shell only when explicitly configured |
| Services | Service, ServiceFeature | Services listing/detail, cards, hero, related services, quote choices | Selected/requested services in enquiry details |
| Locations | Location, service_location | Location landing pages, localized hero/content/pricing/contact/SEO | Selected property/service location in enquiries |
| Pricing | PricingPackage, PricingItem, PricingAddon | Pricing sections/pages, service/location pricing and add-ons | Submitted pricing snapshot in enquiry when captured |
| Media | Spatie Media | Supplies logos, heroes, cards, galleries, post images, testimonials and campaign assets | Authorized private enquiry attachments; future user avatar if implemented |
| Media Picker (admin tool) | Existing Spatie `media` rows; no new table in v1 | No direct public output; it helps Admin choose assets for CMS fields | None |
| Galleries / Before-After | Gallery, GalleryItem + Spatie media | Gallery page, service/location sections, before/after comparison | None |
| Videos | Video + Spatie media | Video showcase/hero candidates where configured | None |
| Testimonials / Reviews | Testimonial + testimonialables | Star reviews, customer proof, WhatsApp screenshot proof | None |
| FAQs | Faq + faqables | Page/service/location FAQ accordions + valid FAQ schema | None |
| Blog / Categories | Post, Category | Blog listing/detail, related posts, SEO content | None |
| Contact Channels | ContactChannel, ContactTarget | Floating widget, WhatsApp list, phone/email CTA, page/service/location targeting | Shared account contact shortcuts if desired |
| Leads / Enquiries | Lead, lead_services, status history, notes, private media | Public quote/contact forms create leads | `/account/enquiries`, enquiry detail, visible notes/status, authorized attachment download |
| SEO | SeoMeta + optional social image | Meta title/description, canonical, OG/Twitter, schema, sitemap flags | Account pages should normally be noindex; no customer editing |
| Redirects | Redirect | 301/302/307/308 handling for changed/legacy public URLs | None |
| Tracking & Consent | TrackingProvider, TrackingEventRule, settings | Page/contact/WhatsApp/call/pricing/quote events according to consent | Account pages should avoid marketing tracking unless explicitly justified/consented |
| Campaigns | Campaign, CampaignSection, CampaignSetting + media | Campaign landing routes and optionally a configured home/public campaign slot | Optional default campaign slot in account dashboard if product requirement keeps this behavior |
| Audit Logs | AuditLog | None | None |

## 3. Ownership rules

- Admin modules are management surfaces; public Blade components consume only published/active/authorized data.
- A customer user never receives admin CRUD endpoints.
- Admin and customer accounts are separate: `Admin`/`admin` guard for backoffice and `User`/`web` guard for customer access. Spatie permissions are guard-specific.
- Lead attachments are private Spatie media owned by Lead and downloaded only after policy authorization. Generic Media Picker must exclude them.
- Public phone/WhatsApp/email values must originate from Contact Channels or approved Site Settings; do not hard-code them in Blade/JavaScript.
- Each dynamic page/campaign section key must map to a known validation schema and Blade component.

## 4. User-panel baseline routes

- `/account/profile` — authenticated user's own profile.
- `/account/enquiries` — authenticated user's own enquiry list.
- `/account/enquiries/{lead}` — own enquiry detail only; policy prevents IDOR.
- Authorized attachment download route — verifies the media belongs to a Lead visible to the authenticated user.
- Optional account dashboard campaign slot — uses only the resolved valid default campaign/fallback and never exposes draft/inactive content.
