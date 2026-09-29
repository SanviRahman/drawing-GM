@can('media_list')
<script>
(() => {
    'use strict';

    const config = {
        listUrl: @json(route('admin.media.list')),
        uploadUrl: @json(route('admin.media.store')),
        bulkUrl: @json(route('admin.media.multiple_action')),
        restoreBase: @json(url('/admin/media-management/restore')),
        forceDeleteBase: @json(url('/admin/media-management/force-delete')),
        deleteBase: @json(url('/admin/media-management')),
        canUpload: @json(auth('admin')->user()?->can('media_upload') ?? false),
        canDelete: @json(auth('admin')->user()?->can('media_delete') ?? false),
        canRestore: @json(auth('admin')->user()?->can('media_restore') ?? false),
        canForceDelete: @json(auth('admin')->user()?->can('media_force_delete') ?? false),
    };

    const state = {
        callback: null,
        options: {},
        selected: new Map(),
        currentItems: [],
        page: 1,
        trash: false,
        loading: false,
        filtersLoaded: false,
    };

    const $modal = $('#globalMediaPickerModal');
    if (!$modal.length) return;

    const escapeHtml = (value) => $('<div>').text(value ?? '').html();

    const csrf = () => $('meta[name="csrf-token"]').attr('content');

    const allowedByOptions = (media) => {
        const type = state.options.type || '';
        if (type && type !== 'all' && media.type !== type) return false;
        if (Array.isArray(state.options.collections) && state.options.collections.length && !state.options.collections.includes(media.collection)) return false;
        return true;
    };

    const iconFor = (media) => {
        if (media.type === 'video') return '<i class="fas fa-video media-picker-file-icon text-warning"></i>';
        if ((media.mime_type || '').includes('pdf')) return '<i class="fas fa-file-pdf media-picker-file-icon text-danger"></i>';
        return '<i class="fas fa-file media-picker-file-icon"></i>';
    };

    const renderGrid = (items) => {
        const $grid = $('#mediaPickerGrid').empty();
        state.currentItems = items;

        items.forEach((media) => {
            const selected = state.selected.has(String(media.id));
            const preview = media.type === 'image' && media.url
                ? `<img src="${escapeHtml(media.url)}" alt="${escapeHtml(media.alt_text || media.name)}" loading="lazy">`
                : iconFor(media);

            let actions = '';
            if (state.trash) {
                if (config.canRestore) actions += `<button type="button" class="btn btn-success btn-xs picker-item-restore" data-id="${media.id}" title="Restore"><i class="fas fa-undo"></i></button> `;
                if (config.canForceDelete) actions += `<button type="button" class="btn btn-danger btn-xs picker-item-force" data-id="${media.id}" title="Force delete"><i class="fas fa-fire"></i></button>`;
            } else if (config.canDelete) {
                actions = `<button type="button" class="btn btn-danger btn-xs picker-item-delete" data-id="${media.id}" title="Move to trash"><i class="fas fa-trash-alt"></i></button>`;
            }

            const disabled = !state.trash && !allowedByOptions(media);
            const cardClass = `media-picker-card ${selected ? 'is-selected' : ''} ${disabled ? 'opacity-50' : ''}`;

            $grid.append(`
                <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-3">
                    <div class="${cardClass}" data-media-id="${media.id}" data-disabled="${disabled ? '1' : '0'}">
                        <span class="media-picker-select-mark"><i class="fas fa-check fa-xs"></i></span>
                        <div class="media-picker-card-actions">${actions}</div>
                        <div class="media-picker-card-preview">${preview}</div>
                        <div class="media-picker-card-body">
                            <div class="media-picker-card-name" title="${escapeHtml(media.name)}">${escapeHtml(media.name)}</div>
                            <div class="media-picker-card-meta mt-1">
                                <div>${escapeHtml(media.owner_label)}</div>
                                <div>${escapeHtml(media.collection)} · ${escapeHtml(media.size_human)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        $('#mediaPickerEmpty').toggleClass('d-none', items.length !== 0);
        updateSelectionUi();
    };

    const renderPagination = (meta) => {
        const $pagination = $('#mediaPickerPagination').empty();
        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);

        const addPage = (page, label, disabled = false, active = false) => {
            $pagination.append(`
                <li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                    <button class="page-link media-picker-page" type="button" data-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>
                </li>
            `);
        };

        addPage(Math.max(1, current - 1), '&laquo;', current <= 1);

        const start = Math.max(1, current - 2);
        const end = Math.min(last, current + 2);
        for (let page = start; page <= end; page++) addPage(page, page, false, page === current);

        addPage(Math.min(last, current + 1), '&raquo;', current >= last);
    };

    const populateFilters = (filters) => {
        const collectionValue = $('#mediaPickerCollection').val();
        const ownerValue = $('#mediaPickerOwner').val();

        const $collection = $('#mediaPickerCollection').html('<option value="">All Collections</option>');
        (filters.collections || []).forEach((value) => $collection.append(`<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`));
        $collection.val(collectionValue);

        const $owner = $('#mediaPickerOwner').html('<option value="">All Owners</option>');
        (filters.owners || []).forEach((item) => $owner.append(`<option value="${escapeHtml(item.value)}">${escapeHtml(item.label)}</option>`));
        $owner.val(ownerValue);
    };

    const updateSelectionUi = () => {
        const count = state.selected.size;
        $('#mediaPickerSelectedCount').text(`${count} selected`);
        const canUse = !state.trash && count > 0 && (state.options.multiple || count === 1);
        $('#mediaPickerUseSelected')
            .prop('disabled', !canUse)
            .html(!state.options.multiple && count > 1
                ? '<i class="fas fa-mouse-pointer mr-1"></i>Select one to use'
                : '<i class="fas fa-check mr-1"></i>Use Selected');
        $('#mediaPickerBulkTrash, #mediaPickerBulkRestore, #mediaPickerBulkForceDelete').prop('disabled', count === 0);

        const selectableIds = state.currentItems
            .filter((item) => state.trash || allowedByOptions(item))
            .map((item) => String(item.id));
        const allSelected = selectableIds.length > 0 && selectableIds.every((id) => state.selected.has(id));
        $('#mediaPickerSelectPage').prop('checked', allSelected);
    };

    const loadMedia = (page = 1) => {
        if (state.loading) return;
        state.loading = true;
        state.page = page;
        $('#mediaPickerLoading').removeClass('d-none');
        $('#mediaPickerGrid').addClass('d-none');
        $('#mediaPickerEmpty').addClass('d-none');

        $.get(config.listUrl, {
            picker: 1,
            trash: state.trash ? 1 : 0,
            page,
            per_page: 24,
            search: $('#mediaPickerSearch').val(),
            type: $('#mediaPickerType').val(),
            collection: $('#mediaPickerCollection').val(),
            owner: $('#mediaPickerOwner').val(),
        }).done((response) => {
            populateFilters(response.filters || {});
            renderGrid(response.data || []);
            renderPagination(response.meta || {});
            const meta = response.meta || {};
            $('#mediaPickerMeta').text(meta.total ? `Showing ${meta.from || 0}-${meta.to || 0} of ${meta.total} files` : 'Showing 0 files');
        }).fail((xhr) => {
            const message = xhr.responseJSON?.message || 'Failed to load media.';
            window.showAlert ? window.showAlert(message, 'error') : Swal.fire('Error', message, 'error');
        }).always(() => {
            state.loading = false;
            $('#mediaPickerLoading').addClass('d-none');
            $('#mediaPickerGrid').removeClass('d-none');
        });
    };

    const reset = () => {
        state.selected.clear();
        state.currentItems = [];
        state.page = 1;
        state.trash = false;
        $('#mediaPickerSearch').val('');
        $('#mediaPickerType').val(state.options.type && state.options.type !== 'all' ? state.options.type : '');
        $('#mediaPickerCollection').val('');
        $('#mediaPickerOwner').val('');
        $('#mediaPickerTrashToggle').attr('data-trash', '0').removeClass('btn-danger').addClass('btn-outline-danger').attr('title', 'Open trash');
        $('.media-picker-bulk-active').removeClass('d-none');
        $('.media-picker-bulk-trash').addClass('d-none');
        updateSelectionUi();
    };

    const runBulk = (action, ids) => {
        if (!ids.length) return;
        const permanent = action === 'force_delete';
        const title = permanent ? 'Permanently delete selected media?' : 'Confirm media action?';
        const text = permanent
            ? 'Physical files will also be removed. This cannot be undone.'
            : `Apply ${action.replace('_', ' ')} to ${ids.length} media item(s)?`;

        const confirm = window.showConfirm
            ? window.showConfirm({ title, text, confirmButtonText: permanent ? 'Force delete' : 'Continue' })
            : Swal.fire({ title, text, icon: 'warning', showCancelButton: true });

        confirm.then((result) => {
            if (!result.isConfirmed && !result.value) return;
            $.post(config.bulkUrl, { action, ids, _token: csrf() })
                .done((response) => {
                    state.selected.clear();
                    loadMedia(state.page);
                    window.showAlert ? window.showAlert(response.message, 'success') : Swal.fire('Success', response.message, 'success');
                    $(document).trigger('media:changed');
                })
                .fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'Media action failed.';
                    window.showAlert ? window.showAlert(message, 'error') : Swal.fire('Error', message, 'error');
                });
        });
    };

    const uploadFiles = (files) => {
        if (!config.canUpload || !files || !files.length) return;
        if (files.length > 10) {
            return window.showAlert ? window.showAlert('Maximum 10 files can be uploaded at once.', 'warning') : null;
        }

        const data = new FormData();
        Array.from(files).forEach((file) => data.append('files[]', file));
        data.append('_token', csrf());

        $('#mediaPickerUploadProgressWrap').removeClass('d-none');
        $('#mediaPickerUploadProgress').css('width', '5%');

        $.ajax({
            url: config.uploadUrl,
            method: 'POST',
            data,
            processData: false,
            contentType: false,
            xhr: function () {
                const xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', (event) => {
                        if (event.lengthComputable) $('#mediaPickerUploadProgress').css('width', `${Math.round((event.loaded / event.total) * 100)}%`);
                    });
                }
                return xhr;
            },
        }).done((response) => {
            $('#mediaPickerFiles').val('');
            window.showAlert ? window.showAlert(response.message, 'success') : Swal.fire('Success', response.message, 'success');
            loadMedia(1);
            $(document).trigger('media:changed');
        }).fail((xhr) => {
            let message = xhr.responseJSON?.message || 'Upload failed.';
            if (xhr.status === 422 && xhr.responseJSON?.errors) message = Object.values(xhr.responseJSON.errors).flat().join('\n');
            window.showAlert ? window.showAlert(message, 'error') : Swal.fire('Error', message, 'error');
        }).always(() => {
            $('#mediaPickerUploadProgress').css('width', '0%');
            $('#mediaPickerUploadProgressWrap').addClass('d-none');
        });
    };

    window.MediaPicker = {
        open(callback, options = {}) {
            state.callback = typeof callback === 'function' ? callback : null;
            state.options = Object.assign({
                type: 'all',
                multiple: false,
                max: null,
                title: 'Media Library',
                collections: [],
            }, options || {});

            reset();
            $('#globalMediaPickerTitle').html(`<i class="fas fa-photo-video text-primary mr-2"></i>${escapeHtml(state.options.title || 'Media Library')}`);
            $modal.modal({ backdrop: 'static', keyboard: false, show: true });
            loadMedia(1);
        },
        refresh() { loadMedia(state.page); },
        close() { $modal.modal('hide'); },
    };

    $(document).on('click', '.media-picker-card', function (event) {
        if ($(event.target).closest('.media-picker-card-actions').length) return;
        if (!state.trash && $(this).data('disabled') == 1) return;

        const id = String($(this).data('media-id'));
        const media = state.currentItems.find((item) => String(item.id) === id);
        if (!media) return;

        if (state.trash) {
            if (state.selected.has(id)) state.selected.delete(id); else state.selected.set(id, media);
        } else if (!state.options.multiple) {
            state.selected.clear();
            state.selected.set(id, media);
        } else if (state.selected.has(id)) {
            state.selected.delete(id);
        } else {
            if (state.options.max && state.selected.size >= Number(state.options.max)) {
                window.showAlert ? window.showAlert(`You can select maximum ${state.options.max} media item(s).`, 'warning') : null;
                return;
            }
            state.selected.set(id, media);
        }

        renderGrid(state.currentItems);
    });

    $('#mediaPickerUseSelected').on('click', function () {
        if (!state.callback || !state.selected.size || state.trash) return;
        const selected = Array.from(state.selected.values());
        state.callback(state.options.multiple ? selected : selected[0]);
        $modal.modal('hide');
    });

    $('#mediaPickerSelectPage').on('change', function () {
        const checked = this.checked;
        state.currentItems.forEach((item) => {
            if (!state.trash && !allowedByOptions(item)) return;
            const id = String(item.id);
            if (!checked) {
                state.selected.delete(id);
                return;
            }
            if (!state.trash && state.options.multiple && state.options.max && state.selected.size >= Number(state.options.max) && !state.selected.has(id)) return;
            state.selected.set(id, item);
        });
        renderGrid(state.currentItems);
    });

    $('#mediaPickerClearSelection').on('click', () => { state.selected.clear(); renderGrid(state.currentItems); });
    $('#mediaPickerRefresh').on('click', () => loadMedia(state.page));
    $('#mediaPickerClearSearch').on('click', () => { $('#mediaPickerSearch').val(''); loadMedia(1); });
    $('#mediaPickerSearch').on('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); loadMedia(1); } });
    $('#mediaPickerType, #mediaPickerCollection, #mediaPickerOwner').on('change', () => loadMedia(1));
    $(document).on('click', '.media-picker-page', function () { loadMedia(Number($(this).data('page') || 1)); });

    $('#mediaPickerTrashToggle').on('click', function () {
        state.trash = !state.trash;
        state.selected.clear();
        $(this).attr('data-trash', state.trash ? '1' : '0')
            .toggleClass('btn-outline-danger', !state.trash)
            .toggleClass('btn-danger', state.trash)
            .attr('title', state.trash ? 'Back to active media' : 'Open trash');
        $('.media-picker-bulk-active').toggleClass('d-none', state.trash);
        $('.media-picker-bulk-trash').toggleClass('d-none', !state.trash);
        loadMedia(1);
    });

    $('#mediaPickerBulkTrash').on('click', () => runBulk('delete', Array.from(state.selected.keys())));
    $('#mediaPickerBulkRestore').on('click', () => runBulk('restore', Array.from(state.selected.keys())));
    $('#mediaPickerBulkForceDelete').on('click', () => runBulk('force_delete', Array.from(state.selected.keys())));

    $(document).on('click', '.picker-item-delete', function (event) {
        event.stopPropagation();
        runBulk('delete', [$(this).data('id')]);
    });
    $(document).on('click', '.picker-item-restore', function (event) {
        event.stopPropagation();
        runBulk('restore', [$(this).data('id')]);
    });
    $(document).on('click', '.picker-item-force', function (event) {
        event.stopPropagation();
        runBulk('force_delete', [$(this).data('id')]);
    });

    const $dropzone = $('#mediaPickerDropzone');
    $dropzone.on('click keydown', function (event) {
        if (event.type === 'click' || event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            $('#mediaPickerFiles').trigger('click');
        }
    });
    $('#mediaPickerFiles').on('change', function () { uploadFiles(this.files); });
    $dropzone.on('dragover dragenter', function (event) { event.preventDefault(); event.stopPropagation(); $(this).addClass('is-dragging'); });
    $dropzone.on('dragleave dragend', function (event) { event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragging'); });
    $dropzone.on('drop', function (event) {
        event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragging');
        uploadFiles(event.originalEvent.dataTransfer.files);
    });

    $modal.on('shown.bs.modal', function () {
        $('.modal-backdrop').last().addClass('media-picker-backdrop');
        if ($('#ajaxModal.show').length) $('body').addClass('media-picker-nested-open');
    });
    $modal.on('hidden.bs.modal', function () {
        state.callback = null;
        state.selected.clear();
        if ($('#ajaxModal.show').length) $('body').addClass('modal-open');
        $('body').removeClass('media-picker-nested-open');
    });
})();
</script>
@endcan
