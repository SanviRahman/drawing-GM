# Documentation Alignment Changelog

## 2026-09-29 — Actual project reconciliation

The documentation was reconciled against the uploaded Laravel repository before continuing model development.

### Canonical decisions

- Framework baseline is Laravel `^13.17` on PHP `^8.3`.
- Admin authentication stays separate: `App\Models\Admin`, `admins`, guard/provider `admin`/`admins`.
- Customer authentication stays `App\Models\User`, `users`, guard/provider `web`/`users`.
- AdminLTE 3 is the current backoffice UI convention.
- Spatie Permission remains guard-specific; custom Role/Permission tables include timestamps/soft deletes and Permission `group_name`.
- Spatie Media Library remains the only generic media table.
- Implemented media collections are `Admin.avatars` and `SiteSetting.site_logo`, `site_favicon`, `default_hero`.
- Legacy `admins.photo` is compatibility-only; new avatar storage uses Spatie.
- Actor fields in future shared history/audit tables must account for separate Admin and User models.

### Next milestone before Page models

Media Picker v1 is documented as the next task. Existing forms already reference `MediaPicker.open(...)` and submit `*_media_id`, while the global picker is still missing.

Version 1 will browse/select authorized existing Spatie media with search/filter/pagination, hide private media, enforce media permissions and preserve source ownership by copying selected files into target collections. It requires no new database table.

### Files updated

- `architecture.md`
- `Database-erd.md`
- `database-schema.md`
- `folder-structure.md`
- `prd.md`
- `project-overview.md`
- `admin-frontend-mapping.md`
