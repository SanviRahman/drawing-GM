<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('redirect_list');

        $query = Redirect::query();
        $this->applyFilters($query, $request);

        $redirects = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.redirects.partials.table', [
                    'redirects' => $redirects,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.redirects.index', [
            'redirects' => $redirects,
            'statusCodes' => Redirect::STATUS_CODES,
            'title' => 'Redirects Management',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Redirects', 'url' => route('admin.redirects.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('redirect_list');

        $query = Redirect::query()->select('id', 'from_path', 'to_url', 'status_code', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q
                ->where('from_path', 'like', "%{$search}%")
                ->orWhere('to_url', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('redirect_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.redirects.partials.form', [
                'statusCodes' => Redirect::STATUS_CODES,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('redirect_create');

        $validated = $this->validateRedirect($request);
        $fromPath = $this->normalizeFromPath((string) $validated['from_path']);
        $toUrl = $this->normalizeToUrl((string) $validated['to_url']);
        $this->assertSafePair($fromPath, $toUrl);

        $redirect = DB::transaction(function () use ($validated, $fromPath, $toUrl): Redirect {
            $existing = Redirect::withTrashed()
                ->where('from_path', $fromPath)
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                throw ValidationException::withMessages([
                    'from_path' => 'A redirect already exists for this source path.',
                ]);
            }

            $record = $existing ?: new Redirect;

            $record->fill([
                'from_path' => $fromPath,
                'to_url' => $toUrl,
                'status_code' => (int) $validated['status_code'],
                'is_active' => (bool) $validated['is_active'],
            ]);

            if ($existing?->trashed()) {
                $existing->restore();
            }

            $record->save();

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'Redirect created successfully.',
            'data' => ['id' => $redirect->id],
        ]);
    }

    public function show(Request $request, Redirect $redirect)
    {
        $this->ensurePermission('redirect_view');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.redirects.partials.show', compact('redirect'))->render(),
        ]);
    }

    public function edit(Request $request, Redirect $redirect)
    {
        $this->ensurePermission('redirect_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.redirects.partials.form', [
                'redirect' => $redirect,
                'statusCodes' => Redirect::STATUS_CODES,
            ])->render(),
        ]);
    }

    public function update(Request $request, Redirect $redirect)
    {
        $this->ensurePermission('redirect_update');

        $validated = $this->validateRedirect($request, $redirect);
        $fromPath = $this->normalizeFromPath((string) $validated['from_path']);
        $toUrl = $this->normalizeToUrl((string) $validated['to_url']);
        $this->assertSafePair($fromPath, $toUrl);

        $conflict = Redirect::withTrashed()
            ->where('from_path', $fromPath)
            ->where('id', '!=', $redirect->id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'from_path' => 'Another redirect already uses this source path, including Trash.',
            ]);
        }

        $redirect->update([
            'from_path' => $fromPath,
            'to_url' => $toUrl,
            'status_code' => (int) $validated['status_code'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        return response()->json(['success' => true, 'message' => 'Redirect updated successfully.']);
    }

    public function destroy(Redirect $redirect)
    {
        $this->ensurePermission('redirect_delete');
        $redirect->delete();

        return response()->json(['success' => true, 'message' => 'Redirect moved to trash.']);
    }

    public function toggle(Redirect $redirect)
    {
        $this->ensurePermission('redirect_toggle');
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return response()->json([
            'success' => true,
            'message' => $redirect->fresh()->is_active ? 'Redirect activated.' : 'Redirect deactivated.',
        ]);
    }

    public function resetHits(Redirect $redirect)
    {
        $this->ensurePermission('redirect_update');

        $redirect->update([
            'hits' => 0,
            'last_hit_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Redirect hit counter reset.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'activate', 'deactivate' => 'redirect_toggle',
            'delete' => 'redirect_delete',
            'restore' => 'redirect_restore',
            'force_delete' => 'redirect_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        $message = match ($validated['action']) {
            'activate' => $this->bulkStatus($ids, true),
            'deactivate' => $this->bulkStatus($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('redirect_trash');

        $query = Redirect::onlyTrashed()->latest('deleted_at');
        $this->applyFilters($query, $request);

        $redirects = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.redirects.partials.table', [
                    'redirects' => $redirects,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.redirects.trash', [
            'redirects' => $redirects,
            'statusCodes' => Redirect::STATUS_CODES,
            'title' => 'Trashed Redirects',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Redirects', 'url' => route('admin.redirects.index')],
                ['text' => 'Trash', 'url' => null],
            ],
        ]);
    }

    public function restore(int $redirect)
    {
        $this->ensurePermission('redirect_restore');

        $record = Redirect::onlyTrashed()->findOrFail($redirect);

        if (Redirect::query()->where('from_path', $record->from_path)->exists()) {
            throw ValidationException::withMessages([
                'redirect' => 'An active redirect already uses this source path.',
            ]);
        }

        $record->restore();

        return response()->json(['success' => true, 'message' => 'Redirect restored successfully.']);
    }

    public function forceDelete(int $redirect)
    {
        $this->ensurePermission('redirect_force_delete');
        Redirect::onlyTrashed()->findOrFail($redirect)->forceDelete();

        return response()->json(['success' => true, 'message' => 'Redirect permanently deleted.']);
    }

    public function export()
    {
        $this->ensurePermission('redirect_export');

        $fileName = 'redirects-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['from_path', 'to_url', 'status_code', 'is_active', 'hits', 'last_hit_at']);

            Redirect::query()->ordered()->chunk(500, function ($rows) use ($out): void {
                foreach ($rows as $redirect) {
                    fputcsv($out, [
                        $redirect->from_path,
                        $redirect->to_url,
                        $redirect->status_code,
                        $redirect->is_active ? 1 : 0,
                        $redirect->hits,
                        optional($redirect->last_hit_at)->toIso8601String(),
                    ]);
                }
            });

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $this->ensurePermission('redirect_import');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'Unable to read the CSV file.']);
        }

        $header = fgetcsv($handle);
        $expected = ['from_path', 'to_url', 'status_code', 'is_active'];

        if (! is_array($header) || array_slice(array_map('trim', $header), 0, 4) !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages([
                'file' => 'CSV header must start with: from_path,to_url,status_code,is_active',
            ]);
        }

        $count = 0;

        DB::transaction(function () use ($handle, &$count): void {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 4) {
                    continue;
                }

                [$from, $to, $code, $active] = $row;

                $from = $this->normalizeFromPath((string) $from);
                $to = $this->normalizeToUrl((string) $to);
                $code = (int) $code;

                if (! array_key_exists($code, Redirect::STATUS_CODES)) {
                    throw ValidationException::withMessages(['file' => "Invalid status code for {$from}."]);
                }

                $this->assertSafePair($from, $to);

                $record = Redirect::withTrashed()->where('from_path', $from)->first();

                if (! $record) {
                    $record = new Redirect(['from_path' => $from]);
                }

                $record->fill([
                    'to_url' => $to,
                    'status_code' => $code,
                    'is_active' => filter_var($active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((int) $active === 1),
                ]);

                if ($record->exists && $record->trashed()) {
                    $record->restore();
                }

                $record->save();
                $count++;
            }
        });

        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "{$count} redirect row(s) imported successfully.",
        ]);
    }

    private function validateRedirect(Request $request, ?Redirect $redirect = null): array
    {
        return $request->validate([
            'from_path' => ['required', 'string', 'max:500'],
            'to_url' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', 'integer', Rule::in(array_keys(Redirect::STATUS_CODES))],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q
                ->where('from_path', 'like', "%{$search}%")
                ->orWhere('to_url', 'like', "%{$search}%"));
        }

        if ($request->filled('status_code') && array_key_exists((int) $request->status_code, Redirect::STATUS_CODES)) {
            $query->where('status_code', (int) $request->status_code);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    private function normalizeFromPath(string $path): string
    {
        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';

        return $path !== '/' ? rtrim($path, '/') : '/';
    }

    private function normalizeToUrl(string $url): string
    {
        $url = trim($url);

        if (Str::startsWith($url, '/')) {
            $url = '/' . ltrim($url, '/');
            return $url !== '/' ? rtrim($url, '/') : '/';
        }

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! Str::startsWith(strtolower($url), ['http://', 'https://'])) {
            throw ValidationException::withMessages([
                'to_url' => 'Destination must be an internal path or an absolute HTTP/HTTPS URL.',
            ]);
        }

        return $url;
    }

    private function assertSafePair(string $fromPath, string $toUrl): void
    {
        $reserved = ['/admin', '/storage', '/vendor'];

        foreach ($reserved as $prefix) {
            if ($fromPath === $prefix || str_starts_with($fromPath, $prefix . '/')) {
                throw ValidationException::withMessages([
                    'from_path' => 'Admin, storage and vendor paths cannot be redirected.',
                ]);
            }
        }

        if ($fromPath === $toUrl) {
            throw ValidationException::withMessages([
                'to_url' => 'The destination cannot be the same as the source path.',
            ]);
        }
    }

    private function bulkStatus(array $ids, bool $active): string
    {
        Redirect::query()->whereIn('id', $ids)->update(['is_active' => $active]);
        return $active ? 'Selected redirects activated.' : 'Selected redirects deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        Redirect::query()->whereIn('id', $ids)->delete();
        return 'Selected redirects moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $records = Redirect::onlyTrashed()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            if (Redirect::query()->where('from_path', $record->from_path)->exists()) {
                throw ValidationException::withMessages([
                    'redirect' => "Cannot restore {$record->from_path}; an active redirect already uses that path.",
                ]);
            }
        }

        $records->each->restore();

        return 'Selected redirects restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Redirect::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return 'Selected redirects permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
