@php
    $isEdit = isset($menu);
    $actionUrl = $isEdit ? route('admin.menus.update', $menu->id) : route('admin.menus.store');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-7 mb-3">
            <label class="font-weight-bold">Menu Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $isEdit ? $menu->name : old('name') }}" required placeholder="e.g. Header Primary Menu" maxlength="150">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-5 mb-3">
            <label class="font-weight-bold">Theme Location <span class="text-danger">*</span></label>
            <select name="location" class="form-control" required>
                <option value="">-- Select Location --</option>
                @foreach(\App\Models\Menu::LOCATIONS as $location)
                    <option value="{{ $location }}" {{ ($isEdit && $menu->location === $location) ? 'selected' : '' }}>
                        {{ \App\Models\Menu::locationLabel($location) }} ({{ $location }})
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-location"></div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Each location can only be used by one menu.</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ (!$isEdit || $menu->is_active == 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($isEdit && $menu->is_active == 0) ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Disabled menus and their items do not render on the website.</small>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Menu' : 'Save Menu' }}
        </button>
    </div>
</form>
