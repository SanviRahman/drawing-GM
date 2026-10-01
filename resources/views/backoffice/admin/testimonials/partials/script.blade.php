<script>
$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    const $page = $('#testimonial-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    function toast(icon, message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }

    function error(xhr, message = 'Request failed.') {
        const text = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : message;
        Swal.fire('Error', text, 'error');
    }

    function filterUrl() {
        const $form = $('#filterForm');
        const query = $form.serialize();
        return query ? $page.data('index-url') + '?' + query : $page.data('index-url');
    }

    function loadData(url = null) {
        $wrapper.addClass('loading').css({
            opacity: '.5',
            pointerEvents: 'none'
        });
        $.ajax({
            url: url || filterUrl(),
            type: 'GET',
            dataType: 'json'
        }).done(function(res) {
            if (res && typeof res.html !== 'undefined') {
                $wrapper.html(res.html);
                $('#checkAll').prop('checked', false);
                return;
            }
            Swal.fire('Error', 'Invalid AJAX response.', 'error');
        }).fail(function(xhr) {
            error(xhr, 'Failed to load testimonials.');
        }).always(function() {
            $wrapper.removeClass('loading').css({
                opacity: '1',
                pointerEvents: 'auto'
            });
        });
    }

    function destroyModalEditors() {
        if (!window.tinymce) return;
        $modalBody.find('textarea.tinymce-editor').each(function() {
            const editor = this.id ? window.tinymce.get(this.id) : null;
            if (editor) editor.remove();
            delete this.dataset.tinymceInitialized;
        });
    }

    function initModalComponents() {
        document.dispatchEvent(new CustomEvent('admin:content-updated'));
    }

    function openModal(url, title) {
        destroyModalEditors();
        $('#modal-title').text(title);
        $modalBody.html(
            '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-2"></i><div class="text-muted">Loading...</div></div>'
            );
        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json'
        }).done(function(res) {
            if (!res || typeof res.html === 'undefined') {
                $modalBody.html('<div class="alert alert-danger mb-0">Invalid server response.</div>');
                return;
            }
            $modalBody.html(res.html);
            initModalComponents();
        }).fail(function(xhr) {
            $modal.modal('hide');
            error(xhr, 'Failed to load testimonial form.');
        });
    }

    function previewLocalFile(input, target) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#' + target + '_preview').attr('src', e.target.result).removeClass('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
    $(document).on('click', '#btnAddRecord', function(e) {
        e.preventDefault();
        openModal($page.data('create-url'), 'Create Testimonial');
    });
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        openModal($(this).data('url'), 'Edit Testimonial');
    });
    $(document).on('click', '.btn-show', function(e) {
        e.preventDefault();
        openModal($(this).data('url'), 'Testimonial Details');
    });
    $(document).on('change', '#ajax-form #photo_file,#ajax-form #testimonial_screenshot_file', function() {
        const target = this.id.replace('_file', '');
        $('#' + target + '_media_id').val('');
        $('#' + target + '_remove').val('0');
        previewLocalFile(this, target);
    });
    $(document).on('click', '#ajax-form .choose-media', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const target = $(this).data('target');
        if (!window.MediaPicker || typeof window.MediaPicker.open !== 'function') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }
        window.MediaPicker.open(function(media) {
            const selected = Array.isArray(media) ? media[0] : media;
            if (!selected) return;
            $('#' + target + '_file').val('');
            $('#' + target + '_media_id').val(selected.id || '');
            $('#' + target + '_remove').val('0');
            if (selected.url) $('#' + target + '_preview').attr('src', selected.url)
                .removeClass('d-none');
        }, {
            type: 'image',
            multiple: false,
            title: target === 'photo' ? 'Choose Customer Photo' :
                'Choose Testimonial Screenshot'
        });
    });
    $(document).on('click', '#ajax-form .remove-media', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        $('#' + target + '_file').val('');
        $('#' + target + '_media_id').val('');
        $('#' + target + '_remove').val('1');
        $('#' + target + '_preview').removeAttr('src').addClass('d-none');
    });
    $(document).on('shown.bs.modal', '#globalMediaPickerModal', function() {
        $(this).css('z-index', 1080);
        $('.modal-backdrop').last().css('z-index', 1070);
    });
    $(document).on('hidden.bs.modal', '#globalMediaPickerModal', function() {
        if ($modal.hasClass('show')) $('body').addClass('modal-open');
    });
    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadData();
    });
    $(document).on('click', '#btnSearch', function(e) {
        e.preventDefault();
        loadData();
    });
    $(document).on('change', '#filter_type,#filter_source,#filter_status,#filter_featured', function() {
        loadData();
    });
    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadData();
        }, 450);
    });
    $(document).on('keydown', '#table_search', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            clearTimeout(searchTimer);
            loadData();
        }
    });
    $(document).on('click', '#btnResetFilter', function(e) {
        e.preventDefault();
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_type,#filter_source,#filter_status,#filter_featured,#table_search').val('');
        clearTimeout(searchTimer);
        loadData();
    });
    $(document).on('click', '#content-wrapper .pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadData(url);
    });
    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();
        if (window.tinymce) window.tinymce.triggerSave();
        const form = this;
        const $form = $(form);
        const $btn = $form.find('button[type="submit"]');
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[class*="error-"]').text('');
        $btn.prop('disabled', true);
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function(res) {
            $modal.modal('hide');
            toast('success', res.message || 'Testimonial saved successfully.');
            loadData();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                    const base = field.split('.')[0];
                    $form.find('[name="' + base + '"],[name="' + base + '[]"]')
                        .addClass('is-invalid');
                    $form.find('.error-' + field.replace(/\./g, '_') + ',.error-' +
                        base).first().text(messages[0]).show();
                });
                return;
            }
            error(xhr);
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Move to trash?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Move to trash'
        }).then(function(r) {
            if (!(r.isConfirmed || r.value)) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                dataType: 'json'
            }).done(function(res) {
                toast('success', res.message);
                loadData();
            }).fail(function(xhr) {
                error(xhr);
            });
        });
    });
    $(document).on('click', '.btn-restore', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Restore testimonial?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Restore'
        }).then(function(r) {
            if (!(r.isConfirmed || r.value)) return;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json'
            }).done(function(res) {
                toast('success', res.message);
                loadData();
            }).fail(function(xhr) {
                error(xhr);
            });
        });
    });
    $(document).on('click', '.btn-force-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Permanently delete?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete permanently'
        }).then(function(r) {
            if (!(r.isConfirmed || r.value)) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                dataType: 'json'
            }).done(function(res) {
                toast('success', res.message);
                loadData();
            }).fail(function(xhr) {
                error(xhr);
            });
        });
    });
    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });
    $(document).on('click', '#btnApplyBulk', function(e) {
        e.preventDefault();
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function() {
            return this.value;
        }).get();
        if (!action) return Swal.fire('Notice', 'Select a bulk action.', 'info');
        if (!ids.length) return Swal.fire('Notice', 'Select at least one testimonial.', 'info');
        const run = function() {
            $.ajax({
                url: $page.data('bulk-url'),
                type: 'POST',
                data: {
                    action: action,
                    ids: ids
                },
                dataType: 'json'
            }).done(function(res) {
                toast('success', res.message);
                $('#bulk_action').val('');
                $('#checkAll').prop('checked', false);
                loadData();
            }).fail(function(xhr) {
                error(xhr, 'Bulk action failed.');
            });
        };
        if (['delete', 'restore', 'force_delete'].includes(action)) {
            Swal.fire({
                title: 'Confirm action?',
                icon: 'warning',
                showCancelButton: true
            }).then(function(r) {
                if (r.isConfirmed || r.value) run();
            });
            return;
        }
        run();
    });
    $modal.on('hidden.bs.modal', function() {
        destroyModalEditors();
        $modalBody.empty();
    });
});
</script>