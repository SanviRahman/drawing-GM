<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\SiteSetting;
use App\Services\SiteSettingMediaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function __construct(private readonly SiteSettingMediaService $mediaService)
    {}

    public function index(Request $request)
    {
        $query = SiteSetting::query()->with('media');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('setting_key', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%")
                    ->orWhere('setting_value', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group')) {
            $query->where('group_name', trim((string) $request->group));
        }

        $settings = $query->ordered()->paginate(15)->withQueryString();

        $groups = SiteSetting::query()->distinct()->orderBy('group_name')->pluck('group_name');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.settings.partials.table', compact('settings'))->render(),
            ]);
        }

        $title      = 'Site Settings Management';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Site Settings', 'url' => route('admin.settings.index')],
        ];

        return view('backoffice.admin.settings.index', compact('settings', 'groups', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = SiteSetting::query()->select('id', 'group_name', 'setting_key', 'value_type');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('setting_key', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.settings.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateModel($request);

        $setting = SiteSetting::create([
            'group_name'    => $validated['group_name'],
            'setting_key'   => $validated['setting_key'],
            'setting_value' => SiteSetting::prepareValue($validated['setting_value'] ?? null, $validated['value_type']),
            'value_type'    => $validated['value_type'],
            'is_public'     => $validated['is_public'],
        ]);

        $this->mediaService->syncFromRequest($request, $setting);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully.',
        ]);
    }

    public function show(Request $request, SiteSetting $setting)
    {
        abort_unless($request->ajax(), 404);

        $setting->load('media');

        return response()->json([
            'html' => view('backoffice.admin.settings.partials.show', compact('setting'))->render(),
        ]);
    }

    public function edit(Request $request, SiteSetting $setting)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.settings.partials.form', compact('setting'))->render(),
        ]);
    }

    public function update(Request $request, SiteSetting $setting)
    {
        $validated = $this->validateModel($request, $setting);

        $data = [
            'group_name'  => $validated['group_name'],
            'setting_key' => $validated['setting_key'],
            'value_type'  => $validated['value_type'],
            'is_public'   => $validated['is_public'],
        ];

        $keepEncryptedValue = $validated['value_type'] === 'encrypted'
        && ($validated['setting_value'] ?? '') === ''
        && $setting->value_type === 'encrypted';

        if (! $keepEncryptedValue) {
            $data['setting_value'] = SiteSetting::prepareValue($validated['setting_value'] ?? null, $validated['value_type']);
        }

        $setting->update($data);

        $this->mediaService->syncFromRequest($request, $setting);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully.',
        ]);
    }

    public function destroy(SiteSetting $setting)
    {
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['public', 'private', 'delete', 'restore', 'force_delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $message = match ($validated['action']) {
            'public'       => $this->bulkPublic($ids, true),
            'private'      => $this->bulkPublic($ids, false),
            'delete'       => $this->bulkDelete($ids),
            'restore'      => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $query = SiteSetting::onlyTrashed()->with('media')->latest('deleted_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('setting_key', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        $settings = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.settings.partials.table', [
                    'settings' => $settings,
                    'isTrash'  => true,
                ])->render(),
            ]);
        }

        $title      = 'Trashed Site Settings';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Site Settings', 'url' => route('admin.settings.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.settings.trash', compact('settings', 'title', 'breadcrumb'));
    }

    public function restore(int $setting)
    {
        SiteSetting::onlyTrashed()->findOrFail($setting)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Setting restored successfully.',
        ]);
    }

    public function forceDelete(int $setting)
    {
        $model = SiteSetting::onlyTrashed()->findOrFail($setting);

        $this->mediaService->purgeAll($model);
        $model->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Setting permanently deleted.',
        ]);
    }

    private function validateModel(Request $request, ?SiteSetting $setting = null): array
    {
        $validated = $request->validate([
            'group_name'            => ['required', 'string', 'max:80'],
            'setting_key'           => [
                'required',
                'string',
                'max:150',
                'regex:/^[a-z0-9]+(?:[._][a-z0-9_]+)*$/',
                Rule::unique('site_settings', 'setting_key')->ignore($setting?->id),
            ],
            'setting_value'         => ['nullable', 'string'],
            'value_type'            => ['required', 'string', Rule::in(SiteSetting::VALUE_TYPES)],
            'is_public'             => ['required', 'boolean'],
            'site_logo'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'site_logo_media_id'    => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
            'site_favicon'          => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:1024'],
            'site_favicon_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
            'default_hero'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'default_hero_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
        ]);

        if (($validated['value_type'] ?? '') === 'json' && ! empty($validated['setting_value'])) {
            json_decode($validated['setting_value']);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'setting_value' => 'The setting value must be a valid JSON string when value type is json.',
                ]);
            }
        }

        return $validated;
    }

    private function bulkPublic(array $ids, bool $isPublic): string
    {
        SiteSetting::whereIn('id', $ids)->update(['is_public' => $isPublic]);

        return $isPublic ? 'Selected settings marked as public.' : 'Selected settings marked as private.';
    }

    private function bulkDelete(array $ids): string
    {
        SiteSetting::whereIn('id', $ids)->delete();

        return 'Selected settings moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        SiteSetting::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected settings restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        SiteSetting::onlyTrashed()->whereIn('id', $ids)->get()->each(function (SiteSetting $setting) {
            $this->mediaService->purgeAll($setting);
            $setting->forceDelete();
        });

        return 'Selected settings permanently deleted.';
    }
    private function reusableImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $media = Media::query()->find((int) $value);

            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                $fail('The selected media must be an active reusable image.');
            }
        };
    }

}
