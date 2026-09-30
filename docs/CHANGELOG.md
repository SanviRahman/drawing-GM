# Specification Reconciliation Changelog

## 2026-09-30 — Booking form refinement

- Booking fixed fields are now Name (required), WhatsApp/phone (required) and Email (optional).
- Admin can create any number of ordered active booking select fields below Email, with configurable labels/placeholders/choices.
- Dynamic submissions are snapshotted through `lead_form_answers`.
- The booking form no longer includes the Add-ons Required checklist or Preferred Date field in the current baseline.
- Pricing add-ons remain in the pricing domain and are not automatically injected into booking.
- Updated `database-schema.md`, `Database-erd.md`, `architecture.md`, `folder-structure.md`, `prd.md`, `project-overview.md` and `admin-frontend-mapping.md`.

## Updated decisions

1. **Authentication unified:** one `users` table and one Laravel `web` guard. `admin` and `user` are Spatie roles; the separate `admins` table/guard was removed from the baseline specification.
2. **Media ownership unified:** Spatie Media Library is the canonical owner for avatars, post images, gallery item media, video file/poster, testimonial photo/screenshot, SEO social image and lead attachments. Duplicate direct media foreign keys/pivots were removed from the specification where direct ownership is sufficient.
3. **Section registry completed:** normal page components now explicitly include `whatsapp_reviews`, `paint_calculator`, `safe_embed` and `spacer`; campaign registry includes `category_brand` and `whatsapp_reviews` with matching Blade filenames.
4. **Location URL policy clarified:** typed `/locations/{slug}` remains the default canonical route; legacy/marketing root paths are explicit aliases/redirects rather than unrestricted catch-all routes.
5. **Admin/frontend mapping added:** `admin-frontend-mapping.md` documents exactly where each admin module is consumed on the public site/customer portal.

## Files revised

- `architecture.md`
- `Database-erd.md`
- `database-schema.md`
- `folder-structure.md`
- `prd.md`
- `project-overview.md`
- Added `admin-frontend-mapping.md`
