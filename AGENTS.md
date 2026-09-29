# AGENTS.md — AI Developer Guidelines & Project Context

## 1. Project Overview & Role
You are an expert Senior PHP & Laravel Developer Assistant working on a high-converting, fully dynamic home-service website and lead management platform inspired by Singapore home service portals (such as budgetpainting.sg)[cite: 6, 7].

### Core Stack & Technical Baseline
- **Framework**: Laravel ^13.17 on PHP ^8.3[cite: 6, 11]
- **Database**: MySQL 8+ (InnoDB, `utf8mb4`, strict constraints)[cite: 6, 9]
- **Frontend**: AdminLTE 3 + Bootstrap/jQuery for backoffice; Blade + Vite/Tailwind for public UI; Alpine.js may be added where useful[cite: 6, 11]
- **Package Standards**:
  - `spatie/laravel-permission` for multi-guard RBAC (`admin` and `web`)
  - `spatie/laravel-medialibrary` as the canonical media management system[cite: 8, 9, 11]
  - `jeroennoten/laravel-adminlte` for Admin backoffice interface

---

## 2. Mandatory Source of Truth Documentation
Before generating or modifying any code, migrations, controllers, models, or views, you MUST strictly read and follow the documents in `docs/`:

1. `docs/database-schema.md` — Authoritative table specifications, column data types, indexes, and migration orders.
2. `docs/Database-erd.md` — Mermaid relationship diagrams, foreign keys, cascade rules, and delete behaviors[cite: 10].
3. `docs/folder-structure.md` — Clean architecture boundaries, directory layout, naming conventions, and file placement.
4. `docs/architecture.md` — Technical workflow, Spatie integration patterns, dynamic hero resolution, contact routing, caching, and tracking bus[cite: 11].
5. `docs/prd.md` — Business requirements, user journeys, functional criteria, and acceptance requirements[cite: 7].
6. `docs/project-overview.md` — General domain scope, frontend sections, and high-level delivery plan[cite: 6].

---

## 3. Strict Architectural Rules & Guardrails

### 3.1 Controller Boundaries
- Controllers must remain thin.
- Never write heavy Eloquent queries, inline validation, tracking API calls, or image processing inside controllers.
- Admin controller flow must be: authorize with granular permission, validate through a focused private controller validation method (the current backoffice convention intentionally does not use external Form Request classes), delegate reusable business logic to Actions/Services, then return the response. Future public/account modules may use dedicated Form Requests when their implementation convention explicitly requires them.

### 3.2 Spatie Permission & Multi-Guard Rules
- Dual guard architecture: `admin` guard for `App\Models\Admin` and `web` guard for `App\Models\User`.
- Granular permissions must always be checked (e.g. `permission_list`, `admin_create`, `service_update`) rather than loose role strings[cite: 8, 9, 11].
- Always reset cached permissions (`php artisan permission:cache-reset`) after role/permission seeding or alterations.

### 3.2.1 Global Media Picker (Implemented)
- `App\Models\Media` extends Spatie Media and uses soft deletes for Trash/Restore; `deleted_at` is added through the project migration.
- `MediaController` + `MediaLibraryService` power the global AdminLTE picker and Media Management screens.
- `MediaPicker.open(callback, options)` is the reusable JavaScript contract for all current/future admin modules.
- Generic picker results must exclude private lead attachments/private disks.
- Current Admin avatar/Profile and Site Setting branding fields use single image selection; future galleries/sections may use multiple selection.
- Soft delete retains physical files; force delete is the only permanent file-removal operation.

### 3.3 Media Handling with Spatie MediaLibrary
- Never create a custom/generic uploads table. Spatie's `media` table is the only canonical source[cite: 8, 9, 11].
- Binary files must never be stored inside MySQL. Use storage disks[cite: 6, 10].
- Domain models implement `HasMedia` and `InteractsWithMedia`[cite: 6, 8, 11].
- Follow strict collection naming:
  - `hero_desktop`, `hero_mobile` (Page, Service, Location)
  - `site_logo`, `site_favicon`, `default_hero` (SiteSetting)[cite: 9, 11]
  - `testimonial_screenshot` (Testimonial WhatsApp proof)
  - `hero_images`, `hero_video`, `hero_video_poster` (Campaign)
  - `lead_attachments` (Private disk for Leads)[cite: 9, 10, 11]

### 3.4 Singapore Market & Painting Domain Specifics
- **Dual Testimonial Model**: Supports both standard star reviews (`source=google`) and real WhatsApp screenshot proof images (`type=whatsapp_screenshot`)[cite: 9].
- **Dynamic Leads & Calculator**: The `leads` table includes a nullable `metadata` JSON column to store Singapore HDB/Condo property conditions (furnished/vacant), paint preferences (Nippon/Dulux), and calculator values[cite: 9].
- **Multi-Agent WhatsApp Widget**: Phone numbers are dynamic, E.164 formatted, and targeted via `contact_channels` and `contact_targets`[cite: 6, 7, 9]. No numbers or WhatsApp links are hardcoded[cite: 7, 8].

### 3.5 Campaign Invariants
- `campaign_settings` is a singleton (`id = 1`) storing the single source of truth for `default_campaign_id`[cite: 9, 10, 11].
- Modifying the default campaign must always run through `SetDefaultCampaign` action with database locking and cache invalidation[cite: 8, 10, 11].
- Hero Media Priority: Valid Embed Video → Ordered `hero_images` → Uploaded `hero_video` → Fallback Image[cite: 7, 10, 11].
- If `video_autoplay = true`, force `video_muted = true`[cite: 7, 9, 11].

---

## 4. Execution Workflow for Agents

When implementing any feature:
1. **Scope One Domain at a Time**: Never attempt multi-domain builds in a single prompt or output[cite: 7, 8].
2. **Schema & Migration Verification**: Double check `docs/database-schema.md` to ensure correct column types (`DECIMAL(12,2)` for money, normalized E.164 for phone, soft deletes where specified)[cite: 9].
3. **Model & Relation Implementation**: Match the foreign keys and cascade rules defined in `docs/Database-erd.md`[cite: 10].
4. **Validation & Action**: Follow the current backoffice convention: validate in a focused private controller method and delegate reusable business logic to an Action/Service. Do not add external Form Requests for admin CRUD unless the project convention is intentionally changed.
5. **Quality Verification**: Execute migrations and verify that imports, traits, and namespace declarations are accurate.


### Strict Coding Standard & Architecture (Follow Admin Module Pattern)
1. **Namespace & Paths:** All admin controllers must be in `App\Http\Controllers\Backoffice\Admin\`. All admin views must be in `resources/views/backoffice/admin/`.
2. **NO Form Requests:** DO NOT create external Form Request classes. Handle validation inside the controller using a private method (e.g., `private function validateMenu(Request $request, ?Menu $menu = null): array`).
3. **Controller Pattern:** Every admin controller must strictly follow the `AdminController` blueprint. It must include methods for: `index` (with search/filters & AJAX pagination), `list` (for select2/ajax search), `create`, `store`, `show`, `edit`, `update`, `destroy` (soft delete), `multipleAction` (bulk active/inactive/delete/restore/force_delete), `trash`, `restore`, and `forceDelete`.
4. **View Pattern:** Every module must have:
   - `index.blade.php` & `trash.blade.php` (Using `#page-manager` with `data-urls` for AJAX).
   - `partials/table.blade.php` (Responsive table with `.row-checkbox` and action buttons).
   - `partials/form.blade.php` (Using `#ajax-form` for modal submissions).
   - `partials/show.blade.php` (Read-only modal view).
   - `partials/script.blade.php` (Containing the standard jQuery/AJAX CRUD and SweetAlert2 logic).