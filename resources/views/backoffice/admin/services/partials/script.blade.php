<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const $page = $('#service-manager');
    const $wrapper = $('#content-wrapper');

    function filters() { return { search: $('#table_search').val() || '', status: $('#filter_status').val() || '', featured: $('#filter_featured').val() || '' }; }

    function loadData(url = $page.data('index-url')) {
        $wrapper.addClass('loading');
        $.get(url, filters(), function(res) { $wrapper.html(res.html).removeClass('loading'); $('#checkAll').prop('checked', false); }).fail(function(xhr) { $wrapper.removeClass('loading'); Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load services.', 'error'); });
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted">Loading...</p></div>');
        $('#ajaxModal').modal('show');
        $.get(url, function(res) { $('#modal-body').html(res.html); document.dispatchEvent(new CustomEvent('admin:content-updated')); }).fail(function(xhr) { $('#modal-body').html('<div class="alert alert-danger m-3">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>'); });
    }

    $('#btnSearch, #filter_status, #filter_featured').on('click change', function() { loadData(); });
    $('#table_search').on('keypress', function(e) { if (e.which === 13) loadData(); });
    $('#btnClearSearch').on('click', function() { $('#table_search').val(''); loadData(); });
    $('#btnResetFilter').on('click', function() { $('#table_search, #filter_status, #filter_featured').val(''); loadData(); });
    $(document).on('click', '.pagination a', function(e) { e.preventDefault(); loadData($(this).attr('href')); });

    $('#btnAddRecord').on('click', function() { openModal($page.data('create-url'), 'Create New Service'); });
    $(document).on('click', '.btn-edit', function() { openModal($(this).data('url'), 'Edit Service'); });
    $(document).on('click', '.btn-show', function() { openModal($(this).data('url'), 'Service Details'); });

    $('#ajaxModal').on('hidden.bs.modal', function() {
        if (window.tinymce) {
            this.querySelectorAll('textarea.tinymce-editor').forEach(function(textarea) {
                const editor = textarea.id ? window.tinymce.get(textarea.id) : null;
                if (editor) editor.destroy();
                delete textarea.dataset.tinymceInitialized;
            });
        }
        $('#modal-body').empty();
    });

    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();
        if (window.tinymce) window.tinymce.triggerSave();
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const formData = new FormData(this);
        $button.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin ml-1"></i>');
        $('.invalid-feedback, .text-danger[class*="error-"]').text('');
        $('.form-control').removeClass('is-invalid');

        $.ajax({
            url: $form.attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) { $('#ajaxModal').modal('hide'); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); },
            error: function(xhr) {
                $button.prop('disabled', false).find('.fa-spinner').remove();
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function(field, errors) { const base = field.split('.')[0]; const errorClass = field.replace(/\./g, '_'); $(`[name="${base}"], [name="${base}[]"]`).addClass('is-invalid'); $(`.error-${errorClass}, .error-${base}`).first().text(errors[0]).show(); });
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'An unexpected error occurred.', 'error');
                }
            }
        });
    });

    $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function() {
        const $button = $(this);
        const isRestore = $button.hasClass('btn-restore');
        const isForce = $button.hasClass('btn-force-delete');
        Swal.fire({ title: isForce ? 'Permanently Delete?' : (isRestore ? 'Restore Service?' : 'Move Service to Trash?'), text: isForce ? 'This permanently removes the service and its owned service media. Referenced services are blocked from force deletion.' : 'Please confirm this action.', icon: 'warning', showCancelButton: true, confirmButtonColor: isForce ? '#d33' : '#3085d6', confirmButtonText: 'Yes, proceed' }).then(function(result) {
            if (!result.value) return;
            $.ajax({ url: $button.data('url'), type: isRestore ? 'POST' : 'DELETE', success: function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); }, error: function(xhr) { const message = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})?.[0]?.[0] || 'Action failed.'; Swal.fire('Error', message, 'error'); } });
        });
    });

    $(document).on('click', '.btn-status-action', function() {
        const $button = $(this);
        Swal.fire({ title: $button.data('label') + '?', text: 'Apply this status change to the service?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes' }).then(function(result) { if (!result.value) return; $.post($button.data('url'), {}, function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); }).fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Status update failed.', 'error'); }); });
    });

    $(document).on('click', '.btn-duplicate', function() {
        const $button = $(this);
        Swal.fire({ title: 'Duplicate Service?', text: 'A new draft copy will be created with copied hero and gallery media.', icon: 'question', showCancelButton: true, confirmButtonText: 'Duplicate' }).then(function(result) { if (!result.value) return; $.post($button.data('url'), {}, function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); }).fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Duplicate failed.', 'error'); }); });
    });

    $(document).on('click', '#checkAll', function() { $('.row-checkbox').prop('checked', this.checked); });

    $('#btnApplyBulk').on('click', function() {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!action) return Swal.fire('Notice', 'Please select a bulk action.', 'info');
        if (!ids.length) return Swal.fire('Notice', 'Please select at least one service.', 'info');
        Swal.fire({ title: 'Confirm Bulk Action', text: `Apply "${action}" to ${ids.length} service(s)?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, proceed' }).then(function(result) { if (!result.value) return; $.post($page.data('bulk-url'), { action: action, ids: ids }, function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); $('#bulk_action').val(''); loadData(); }).fail(function(xhr) { const message = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})?.[0]?.[0] || 'Bulk action failed.'; Swal.fire('Error', message, 'error'); }); });
    });
});
</script>
