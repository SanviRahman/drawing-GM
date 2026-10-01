<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialService $testimonialService)
    {}
    public function index(Request $request)
    {
        $this->permission('testimonial_list');
        $query        = $this->filteredQuery($request, Testimonial::query());
        $testimonials = $query->latest('id')->paginate(15)->withQueryString();
        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.testimonials.partials.table', ['testimonials' => $testimonials, 'isTrash' => false])->render()]);
        }

        $title      = 'Testimonials Management';
        $breadcrumb = [['text' => 'Content', 'url' => null], ['text' => 'Testimonials', 'url' => route('admin.testimonials.index')]];
        return view('backoffice.admin.testimonials.index', compact('testimonials', 'title', 'breadcrumb'));
    }
    public function list(Request $request)
    {
        $this->permission('testimonial_list');
        $query = $this->filteredQuery($request, Testimonial::query());
        return response()->json(['success' => true, 'data' => $query->latest('id')->limit(50)->get(['id', 'customer_name', 'type', 'source', 'is_active'])]);
    }

    public function create(Request $request)
{
$this->permission('testimonial_create');
abort_unless($request->ajax(),404);
$serviceOptions=DB::table('services')->whereNull('deleted_at')->orderBy('name')->pluck('name','id');
$locationOptions=DB::table('locations')->whereNull('deleted_at')->orderBy('name')->pluck('name','id');
return response()->json(['html'=>view('backoffice.admin.testimonials.partials.form',compact('serviceOptions','locationOptions'))->render()]);
}
    public function serviceOptions(Request $request)
    {
        $this->permissionAny(['testimonial_create', 'testimonial_update']);
        $search = trim((string) $request->input('search', ''));
        $query  = DB::table('services')->select('id', 'name')->whereNull('deleted_at');
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->orderBy('name')->limit(30)->get()->map(fn($item) => ['id' => (int) $item->id, 'text' => (string) $item->name]);
        return response()->json(['success' => true, 'data' => $data]);
    }
    public function locationOptions(Request $request)
    {
        $this->permissionAny(['testimonial_create', 'testimonial_update']);
        $search = trim((string) $request->input('search', ''));
        $query  = DB::table('locations')->select('id', 'name')->whereNull('deleted_at');
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->orderBy('name')->limit(30)->get()->map(fn($item) => ['id' => (int) $item->id, 'text' => (string) $item->name]);
        return response()->json(['success' => true, 'data' => $data]);
    }
    public function store(Request $request)
    {
        $this->permission('testimonial_create');
        $validated   = $this->validateTestimonial($request);
        $testimonial = $this->testimonialService->create($validated, $request);
        return response()->json(['success' => true, 'message' => 'Testimonial created successfully.', 'id' => $testimonial->id]);
    }
    public function show(Request $request, Testimonial $testimonial)
    {
        $this->permission('testimonial_view');
        abort_unless($request->ajax(), 404);
        $testimonial->load(['services', 'locations', 'media']);
        return response()->json(['html' => view('backoffice.admin.testimonials.partials.show', compact('testimonial'))->render()]);
    }


    public function edit(Request $request,Testimonial $testimonial)
{
$this->permission('testimonial_update');
abort_unless($request->ajax(),404);
$testimonial->load(['services:id,name','locations:id,name','media']);
$serviceOptions=DB::table('services')->whereNull('deleted_at')->orderBy('name')->pluck('name','id');
$locationOptions=DB::table('locations')->whereNull('deleted_at')->orderBy('name')->pluck('name','id');
return response()->json(['html'=>view('backoffice.admin.testimonials.partials.form',compact('testimonial','serviceOptions','locationOptions'))->render()]);
}
    public function update(Request $request, Testimonial $testimonial)
    {
        $this->permission('testimonial_update');
        $validated = $this->validateTestimonial($request, $testimonial);
        $this->testimonialService->update($testimonial, $validated, $request);
        return response()->json(['success' => true, 'message' => 'Testimonial updated successfully.']);
    }
    public function destroy(Testimonial $testimonial)
    {
        $this->permission('testimonial_delete');
        $testimonial->delete();
        return response()->json(['success' => true, 'message' => 'Testimonial moved to trash.']);
    }
    public function trash(Request $request)
    {
        $this->permission('testimonial_trash');
        $query        = $this->filteredQuery($request, Testimonial::onlyTrashed());
        $testimonials = $query->latest('deleted_at')->paginate(15)->withQueryString();
        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.testimonials.partials.table', ['testimonials' => $testimonials, 'isTrash' => true])->render()]);
        }

        $title      = 'Trashed Testimonials';
        $breadcrumb = [['text' => 'Content', 'url' => null], ['text' => 'Testimonials', 'url' => route('admin.testimonials.index')], ['text' => 'Trash', 'url' => null]];
        return view('backoffice.admin.testimonials.trash', compact('testimonials', 'title', 'breadcrumb'));
    }
    public function restore(int $testimonial)
    {
        $this->permission('testimonial_restore');
        Testimonial::onlyTrashed()->findOrFail($testimonial)->restore();
        return response()->json(['success' => true, 'message' => 'Testimonial restored successfully.']);
    }
    public function forceDelete(int $testimonial)
    {
        $this->permission('testimonial_force_delete');
        $model = Testimonial::onlyTrashed()->findOrFail($testimonial);
        $this->testimonialService->purgeMedia($model);
        $model->forceDelete();
        return response()->json(['success' => true, 'message' => 'Testimonial permanently deleted.']);
    }
    public function multipleAction(Request $request)
    {
        $validated = $request->validate(['action' => ['required', Rule::in(['active', 'inactive', 'featured', 'unfeatured', 'delete', 'restore', 'force_delete'])], 'ids' => ['required', 'array', 'min:1'], 'ids.*' => ['required', 'integer', 'distinct']]);
        $ids       = collect($validated['ids'])->map(fn($id) => (int) $id)->unique()->values()->all();
        $action    = $validated['action'];
        if (in_array($action, ['active', 'inactive', 'featured', 'unfeatured'], true)) {
            $this->permission('testimonial_update');
        }

        if ($action === 'delete') {
            $this->permission('testimonial_delete');
        }

        if ($action === 'restore') {
            $this->permission('testimonial_restore');
        }

        if ($action === 'force_delete') {
            $this->permission('testimonial_force_delete');
        }

        $message = match ($action) {'active' => $this->bulkUpdate($ids, ['is_active' => true], 'Selected testimonials activated.'), 'inactive' => $this->bulkUpdate($ids, ['is_active' => false], 'Selected testimonials deactivated.'), 'featured' => $this->bulkUpdate($ids, ['is_featured' => true], 'Selected testimonials marked as featured.'), 'unfeatured' => $this->bulkUpdate($ids, ['is_featured' => false], 'Selected testimonials removed from featured.'), 'delete' => $this->bulkDelete($ids), 'restore' => $this->bulkRestore($ids), 'force_delete' => $this->bulkForceDelete($ids)};
        return response()->json(['success' => true, 'message' => $message]);
    }
    private function validateTestimonial(Request $request, ?Testimonial $testimonial = null): array
    {
        $validated = $request->validate([
            'type'                            => ['required', Rule::in(Testimonial::TYPES)],
            'customer_name'                   => ['required', 'string', 'max:150'],
            'customer_title'                  => ['nullable', 'string', 'max:150'],
            'rating'                          => ['nullable', 'integer', 'between:1,5'],
            'review'                          => ['nullable', 'string'],
            'source'                          => ['required', Rule::in(Testimonial::SOURCES)],
            'source_url'                      => ['nullable', 'url', 'max:255'],
            'reviewed_at'                     => ['nullable', 'date'],
            'is_featured'                     => ['required', 'boolean'],
            'is_active'                       => ['required', 'boolean'],
            'sort_order'                      => ['nullable', 'integer', 'min:0'],
            'photo'                           => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'testimonial_screenshot'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_media_id'                  => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
            'testimonial_screenshot_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
            'photo_remove'                    => ['nullable', 'boolean'],
            'testimonial_screenshot_remove'   => ['nullable', 'boolean'],
            'service_ids'                     => ['nullable', 'array'],
            'service_ids.*'                   => ['integer', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'location_ids'                    => ['nullable', 'array'],
            'location_ids.*'                  => ['integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
        ]);
        if ($validated['type'] === 'text' && blank(trim(strip_tags((string) ($validated['review'] ?? ''))))) {
            throw ValidationException::withMessages(['review' => 'Review is required for text testimonials.']);
        }

        if ($validated['type'] === 'whatsapp_screenshot') {
            $hasExisting = $testimonial?->hasMedia('testimonial_screenshot') && ! $request->boolean('testimonial_screenshot_remove');
            $hasNew      = $request->hasFile('testimonial_screenshot') || $request->filled('testimonial_screenshot_media_id');
            if (! $hasExisting && ! $hasNew) {
                throw ValidationException::withMessages(['testimonial_screenshot' => 'A screenshot is required for WhatsApp screenshot testimonials.']);
            }

        }
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        return $validated;
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
    private function filteredQuery(Request $request, $query)
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {$q->where('customer_name', 'like', "%{$search}%")->orWhere('customer_title', 'like', "%{$search}%")->orWhere('review', 'like', "%{$search}%")->orWhere('source_url', 'like', "%{$search}%");});
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        return $query;
    }
    private function bulkUpdate(array $ids, array $data, string $message): string
    {
        Testimonial::whereIn('id', $ids)->update($data);
        return $message;
    }
    private function bulkDelete(array $ids): string
    {
        Testimonial::whereIn('id', $ids)->delete();
        return 'Selected testimonials moved to trash.';
    }
    private function bulkRestore(array $ids): string
    {
        Testimonial::onlyTrashed()->whereIn('id', $ids)->restore();
        return 'Selected testimonials restored.';
    }
    private function bulkForceDelete(array $ids): string
    {
        Testimonial::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Testimonial $testimonial) {$this->testimonialService->purgeMedia($testimonial); $testimonial->forceDelete();});
        return 'Selected testimonials permanently deleted.';
    }
    private function permission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
    private function permissionAny(array $permissions): void
    {
        $user = auth('admin')->user();
        abort_unless($user && collect($permissions)->contains(fn($permission) => $user->can($permission)), 403);
    }
}
