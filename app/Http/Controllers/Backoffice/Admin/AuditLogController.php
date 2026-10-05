<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('audit_log_list');

        $query = AuditLog::query()->with('actor');
        $this->applyFilters($query, $request);
        $auditLogs = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.audit_logs.partials.table', [
                    'auditLogs' => $auditLogs,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.audit_logs.index', [
            'auditLogs' => $auditLogs,
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'title' => 'Audit Logs',
            'breadcrumb' => [
                ['text' => 'Operations', 'url' => null],
                ['text' => 'Audit Logs', 'url' => route('admin.audit_logs.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('audit_log_list');

        $query = AuditLog::query()->select('id', 'action', 'auditable_type', 'auditable_id', 'created_at');
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q->where('action', 'like', "%{$search}%")
                ->orWhere('auditable_type', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get()->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'text' => "#{$log->id} {$log->action}",
            ]),
        ]);
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        $this->ensurePermission('audit_log_view');
        abort_unless($request->ajax(), 404);

        $auditLog->load('actor');

        return response()->json([
            'html' => view('backoffice.admin.audit_logs.partials.show', compact('auditLog'))->render(),
        ]);
    }

    public function destroy(AuditLog $auditLog)
    {
        $this->ensurePermission('audit_log_delete');
        $auditLog->delete();

        return response()->json(['success' => true, 'message' => 'Audit log moved to trash.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'delete' => 'audit_log_delete',
            'restore' => 'audit_log_restore',
            'force_delete' => 'audit_log_force_delete',
        };
        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        $message = match ($validated['action']) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('audit_log_trash');

        $query = AuditLog::onlyTrashed()->with('actor');
        $this->applyFilters($query, $request);
        $auditLogs = $query->latest('deleted_at')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.audit_logs.partials.table', [
                    'auditLogs' => $auditLogs,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.audit_logs.trash', [
            'auditLogs' => $auditLogs,
            'actions' => AuditLog::withTrashed()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'title' => 'Audit Log Trash',
        ]);
    }

    public function restore(int $auditLog)
    {
        $this->ensurePermission('audit_log_restore');
        AuditLog::onlyTrashed()->findOrFail($auditLog)->restore();

        return response()->json(['success' => true, 'message' => 'Audit log restored.']);
    }

    public function forceDelete(int $auditLog)
    {
        $this->ensurePermission('audit_log_force_delete');
        AuditLog::onlyTrashed()->findOrFail($auditLog)->forceDelete();

        return response()->json(['success' => true, 'message' => 'Audit log permanently deleted.']);
    }

    public function export(Request $request)
    {
        $this->ensurePermission('audit_log_export');

        $query = AuditLog::query()->with('actor');
        $this->applyFilters($query, $request);

        $filename = 'audit-logs-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['ID', 'Date', 'Actor', 'Action', 'Auditable Type', 'Auditable ID', 'IP']);

            $query->ordered()->chunkById(500, function ($logs) use ($handle): void {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        optional($log->created_at)->toDateTimeString(),
                        $log->actor?->name ?? 'System',
                        $log->action,
                        class_basename($log->auditable_type),
                        $log->auditable_id,
                        $log->ip_address,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('actor_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('auditable_id', $search);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('actor_type')) {
            $query->where('actor_type', $request->string('actor_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }
    }

    private function bulkDelete(array $ids): string
    {
        AuditLog::query()->whereIn('id', $ids)->delete();
        return 'Selected audit logs moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        AuditLog::onlyTrashed()->whereIn('id', $ids)->restore();
        return 'Selected audit logs restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        AuditLog::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return 'Selected audit logs permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
