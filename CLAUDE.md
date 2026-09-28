<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

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


Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>


