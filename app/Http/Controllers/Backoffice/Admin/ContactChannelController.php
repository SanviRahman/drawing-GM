<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactChannel;
use App\Services\ContactChannelService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactChannelController extends Controller
{
    public function __construct(
        private readonly ContactChannelService $contactChannelService,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('contact_channel_list');

        $query = ContactChannel::query();
        $this->applyFilters($query, $request);

        $contactChannels = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.contact_channels.partials.table', [
                    'contactChannels' => $contactChannels,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Contact Channels Management';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Contact Channels', 'url' => route('admin.contact_channels.index')],
        ];

        return view('backoffice.admin.contact_channels.index', [
            'contactChannels' => $contactChannels,
            'types' => ContactChannel::TYPES,
            'title' => $title,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('contact_channel_list');

        $query = ContactChannel::query()
            ->select('id', 'type', 'label', 'region', 'value', 'is_default', 'is_active', 'sort_order');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('label', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('display_value', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && array_key_exists((string) $request->type, ContactChannel::TYPES)) {
            $query->where('type', (string) $request->type);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('contact_channel_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.contact_channels.partials.form', $this->formOptions())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('contact_channel_create');

        $validated = $this->validateContactChannel($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = ((int) ContactChannel::query()->max('sort_order')) + 10;
        }

        $wantsDefault = (bool) $validated['is_default'];

        $contactChannel = DB::transaction(function () use ($validated, $wantsDefault): ContactChannel {
            $attributes = $this->contactChannelAttributes($validated);
            $attributes['is_default'] = false;

            $record = ContactChannel::create($attributes);

            if ($wantsDefault) {
                $this->contactChannelService->setDefault($record);
                $record->refresh();
            }

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact channel created successfully.',
            'data' => ['id' => $contactChannel->id],
        ]);
    }

    public function show(Request $request, ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_view');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.contact_channels.partials.show', compact('contactChannel'))->render(),
        ]);
    }

    public function edit(Request $request, ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.contact_channels.partials.form', array_merge(
                $this->formOptions(),
                ['contactChannel' => $contactChannel]
            ))->render(),
        ]);
    }

    public function update(Request $request, ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_update');

        $validated = $this->validateContactChannel($request, $contactChannel);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $contactChannel->sort_order;
        }

        $wantsDefault = (bool) $validated['is_default'];

        DB::transaction(function () use ($validated, $wantsDefault, $contactChannel): void {
            $attributes = $this->contactChannelAttributes($validated);
            $attributes['is_default'] = false;

            $contactChannel->update($attributes);

            if ($wantsDefault) {
                $this->contactChannelService->setDefault($contactChannel);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact channel updated successfully.',
        ]);
    }

    public function destroy(ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_delete');

        DB::transaction(function () use ($contactChannel): void {
            $this->contactChannelService->prepareForSoftDelete($contactChannel);
            $contactChannel->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact channel moved to trash.',
        ]);
    }

    public function toggle(ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_toggle');

        DB::transaction(function () use ($contactChannel): void {
            if ($contactChannel->is_active && $contactChannel->is_default) {
                $this->contactChannelService->clearDefault($contactChannel);
            }

            $contactChannel->update([
                'is_active' => ! $contactChannel->is_active,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => $contactChannel->fresh()->is_active
                ? 'Contact channel activated.'
                : 'Contact channel deactivated.',
        ]);
    }

    public function setDefault(ContactChannel $contactChannel)
    {
        $this->ensurePermission('contact_channel_set_default');

        $this->contactChannelService->setDefault($contactChannel);

        return response()->json([
            'success' => true,
            'message' => 'Default contact channel updated successfully.',
        ]);
    }

    public function reorder(Request $request)
    {
        $this->ensurePermission('contact_channel_reorder');

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('contact_channels', 'id')->whereNull('deleted_at'),
            ],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $item) {
                ContactChannel::query()
                    ->whereKey((int) $item['id'])
                    ->update(['sort_order' => (int) $item['sort_order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact channel order updated successfully.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'activate', 'deactivate' => 'contact_channel_toggle',
            'delete' => 'contact_channel_delete',
            'restore' => 'contact_channel_restore',
            'force_delete' => 'contact_channel_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'ids' => 'Please select at least one valid contact channel.',
            ]);
        }

        $message = match ($validated['action']) {
            'activate' => $this->bulkStatus($ids, true),
            'deactivate' => $this->bulkStatus($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('contact_channel_trash');

        $query = ContactChannel::onlyTrashed()->orderByDesc('deleted_at');
        $this->applyFilters($query, $request);

        $contactChannels = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.contact_channels.partials.table', [
                    'contactChannels' => $contactChannels,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Contact Channels';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Contact Channels', 'url' => route('admin.contact_channels.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.contact_channels.trash', [
            'contactChannels' => $contactChannels,
            'types' => ContactChannel::TYPES,
            'title' => $title,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function restore(int $contactChannel)
    {
        $this->ensurePermission('contact_channel_restore');

        $record = ContactChannel::onlyTrashed()->findOrFail($contactChannel);

        // A restored legacy record must never silently create a second default.
        $record->forceFill(['is_default' => false]);
        $record->restore();
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact channel restored successfully.',
        ]);
    }

    public function forceDelete(int $contactChannel)
    {
        $this->ensurePermission('contact_channel_force_delete');

        DB::transaction(function () use ($contactChannel): void {
            $record = ContactChannel::onlyTrashed()->findOrFail($contactChannel);
            $this->ensureForceDeletable([$record->id]);
            $record->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact channel permanently deleted.',
        ]);
    }

    private function validateContactChannel(Request $request, ?ContactChannel $contactChannel = null): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(ContactChannel::TYPES))],
            'label' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:255'],
            'display_value' => ['nullable', 'string', 'max:120'],
            'message_template' => ['nullable', 'string', 'max:60000'],
            'icon' => ['required', 'string', 'max:100', Rule::in(array_keys(ContactChannel::ICON_OPTIONS))],
            'colour' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    $isHex = (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
                    $isToken = array_key_exists($value, ContactChannel::COLOR_TOKENS);

                    if (! $isHex && ! $isToken) {
                        $fail('The colour must be a #RRGGBB hex value or an approved colour token.');
                    }
                },
            ],
            'availability_text' => ['nullable', 'string', 'max:120'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'track_clicks' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $validated['value'] = $this->contactChannelService->normalizeValue(
            (string) $validated['type'],
            (string) $validated['value']
        );

        $this->contactChannelService->assertValueIsValid(
            (string) $validated['type'],
            (string) $validated['value']
        );

        if ((bool) $validated['is_default'] && ! (bool) $validated['is_active']) {
            throw ValidationException::withMessages([
                'is_default' => 'A default contact channel must be active.',
            ]);
        }

        return $validated;
    }

    private function contactChannelAttributes(array $validated): array
    {
        return [
            'type' => (string) $validated['type'],
            'label' => trim((string) $validated['label']),
            'region' => $this->nullableTrim($validated['region'] ?? null),
            'value' => (string) $validated['value'],
            'display_value' => $this->nullableTrim($validated['display_value'] ?? null),
            'message_template' => $this->sanitizeRichText($validated['message_template'] ?? null),
            'icon' => (string) $validated['icon'],
            'colour' => trim((string) $validated['colour']),
            'availability_text' => $this->nullableTrim($validated['availability_text'] ?? null),
            'is_default' => (bool) $validated['is_default'],
            'is_active' => (bool) $validated['is_active'],
            'track_clicks' => (bool) $validated['track_clicks'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('label', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('display_value', 'like', "%{$search}%")
                    ->orWhere('availability_text', 'like', "%{$search}%")
                    ->orWhere('icon', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && array_key_exists((string) $request->type, ContactChannel::TYPES)) {
            $query->where('type', (string) $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('default')) {
            $query->where('is_default', $request->default === 'yes');
        }
    }

    private function formOptions(): array
    {
        return [
            'types' => ContactChannel::TYPES,
            'iconOptions' => ContactChannel::ICON_OPTIONS,
            'colorTokens' => ContactChannel::COLOR_TOKENS,
        ];
    }

    private function bulkStatus(array $ids, bool $active): string
    {
        if (! $active) {
            ContactChannel::query()
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => false,
                    'is_default' => false,
                ]);

            return 'Selected contact channels deactivated.';
        }

        ContactChannel::query()
            ->whereIn('id', $ids)
            ->update(['is_active' => true]);

        return 'Selected contact channels activated.';
    }

    private function bulkDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            ContactChannel::query()->whereIn('id', $ids)->get()->each(function (ContactChannel $channel): void {
                $this->contactChannelService->prepareForSoftDelete($channel);
                $channel->delete();
            });
        });

        return 'Selected contact channels moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            ContactChannel::onlyTrashed()->whereIn('id', $ids)->get()->each(function (ContactChannel $channel): void {
                $channel->forceFill(['is_default' => false]);
                $channel->restore();
                $channel->save();
            });
        });

        return 'Selected contact channels restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $records = ContactChannel::onlyTrashed()->whereIn('id', $ids)->get();
            $actualIds = $records->pluck('id')->all();

            $this->ensureForceDeletable($actualIds);

            $records->each(fn (ContactChannel $channel) => $channel->forceDelete());
        });

        return 'Selected contact channels permanently deleted.';
    }

    private function ensureForceDeletable(array $ids): void
    {
        if ($ids === [] || ! Schema::hasTable('contact_targets')) {
            return;
        }

        if (DB::table('contact_targets')->whereIn('contact_channel_id', $ids)->exists()) {
            throw ValidationException::withMessages([
                'contact_channel' => 'One or more contact channels still have targeting mappings. Permanently remove those mappings before permanently deleting the channel.',
            ]);
        }
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html = preg_replace_callback('/\s(href)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) {
                return '';
            }

            return ' href=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
