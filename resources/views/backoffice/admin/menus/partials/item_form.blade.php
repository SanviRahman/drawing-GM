@php
    $isEdit = isset($item) && $item instanceof \App\Models\MenuItem;
    $actionUrl = $isEdit
        ? route('admin.menus.item_update', [$menu->id, $item->id])
        : route('admin.menus.item_store', $menu->id);
    $reloadUrl = route('admin.menus.show', $menu->id);
    $linkableModels = \App\Models\MenuItem::linkableModels();
@endphp

<form id="ajax-form"
      action="{{ $actionUrl }}"
      method="POST"
      data-reload-modal="{{ $reloadUrl }}"
      data-reload-title="Menu Details & Items">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Label <span class="text-danger">*</span></label>
            <input type="text" name="label" class="form-control" value="{{ $isEdit ? $item->label : old('label') }}" required placeholder="e.g. Our Services" maxlength="150">
            <div class="invalid-feedback error-label"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Parent Item</label>
            <select name="parent_id" class="form-control">
                <option value="">— Root level —</option>
                @foreach($parentOptions as $id => $label)
                    <option value="{{ $id }}" {{ ($isEdit && (string) $item->parent_id === (string) $id) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-parent_id"></div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Nested items render as dropdown sub-menus.</small>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Link Type <span class="text-danger">*</span></label>
            <select name="link_type" id="link_type" class="form-control" required>
                @foreach(\App\Models\MenuItem::LINK_TYPES as $type => $typeLabel)
                    <option value="{{ $type }}" {{ ($isEdit ? $item->link_type : 'url') === $type ? 'selected' : '' }}>{{ $typeLabel }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-link_type"></div>
        </div>

        <div class="col-md-6 mb-3" id="link-url-block">
            <label class="font-weight-bold">Custom URL <span class="text-danger url-required d-none">*</span></label>
            <input type="text" name="url" id="url" class="form-control" value="{{ $isEdit ? $item->url : old('url') }}" placeholder="e.g. /services or https://example.com" maxlength="500">
            <div class="invalid-feedback error-url"></div>
        </div>

        <div class="col-md-6 mb-3" id="link-linkable-block">
            <label class="font-weight-bold">Internal Target</label>
            @if(count($linkableModels) > 0)
                <select name="linkable_type" id="linkable_type" class="form-control">
                    <option value="">-- Select Type --</option>
                    @foreach($linkableModels as $class => $label)
                        <option value="{{ $class }}" {{ ($isEdit && $item->linkable_type === $class) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback error-linkable_type"></div>
            @else
                <input type="text" class="form-control" value="No internal linkable content types available yet" disabled>
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Enable by adding models to <code>MenuItem::linkableModels()</code>.</small>
            @endif
        </div>

        <div class="col-md-6 mb-3" id="link-linkable-id-block">
            <label class="font-weight-bold">Internal ID <span class="text-danger linkable-required d-none">*</span></label>
            <input type="number" name="linkable_id" id="linkable_id" class="form-control" value="{{ $isEdit ? $item->linkable_id : old('linkable_id') }}" min="1" placeholder="Linked record ID">
            <div class="invalid-feedback error-linkable_id"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Icon Class</label>
            <input type="text" name="icon" class="form-control" value="{{ $isEdit ? $item->icon : old('icon') }}" placeholder="e.g. fas fa-paint-roller" maxlength="100">
            <div class="invalid-feedback error-icon"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">CSS Class</label>
            <input type="text" name="css_class" class="form-control" value="{{ $isEdit ? $item->css_class : old('css_class') }}" placeholder="Optional extra classes" maxlength="150">
            <div class="invalid-feedback error-css_class"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Link Target <span class="text-danger">*</span></label>
            <select name="target" class="form-control" required>
                <option value="_self" {{ ($isEdit ? $item->target : '_self') === '_self' ? 'selected' : '' }}>Same Window (_self)</option>
                <option value="_blank" {{ ($isEdit && $item->target === '_blank') ? 'selected' : '' }}>New Window (_blank)</option>
            </select>
            <div class="invalid-feedback error-target"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $isEdit ? $item->sort_order : ($nextSortOrder ?? 1) }}" min="0" max="9999">
            <div class="invalid-feedback error-sort_order"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ (!$isEdit || $item->is_active == 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($isEdit && $item->is_active == 0) ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Menu Item' : 'Save Menu Item' }}
        </button>
    </div>
</form>

<script>
    function toggleLinkBlocks() {
        let type = $('#link_type').val();

        $('#link-url-block').toggle(type === 'url');
        $('#link-linkable-block, #link-linkable-id-block').toggle(type === 'linkable');

        $('.url-required').toggleClass('d-none', type !== 'url');
        $('.linkable-required').toggleClass('d-none', type !== 'linkable');

        $('#url').prop('required', type === 'url');
        $('#linkable_id').prop('required', type === 'linkable' && $('#linkable_type').length > 0);
    }

    $('#link_type').on('change', toggleLinkBlocks);
    toggleLinkBlocks();
</script>
