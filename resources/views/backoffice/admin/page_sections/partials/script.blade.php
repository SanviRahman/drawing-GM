<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const $page = $('#page-section-manager');
    const $wrapper = $('#content-wrapper');

    function filters() { return { search: $('#table_search').val() || '', page_id: $('#filter_page').val() || '', section_definition_id: $('#filter_definition').val() || '', active: $('#filter_active').length ? $('#filter_active').val() : '' }; }
    function loadData(url = $page.data('index-url')) { $wrapper.addClass('loading'); $.get(url, filters(), function(res) { $wrapper.html(res.html).removeClass('loading'); $('#checkAll').prop('checked', false); }).fail(function(xhr) { $wrapper.removeClass('loading'); Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load page sections.', 'error'); }); }
    function openModal(url, title) { $('#modal-title').text(title); $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted">Loading...</p></div>'); $('#ajaxModal').modal('show'); $.get(url, function(res) { $('#modal-body').html(res.html); document.dispatchEvent(new CustomEvent('admin:content-updated')); }).fail(function(xhr) { $('#modal-body').html('<div class="alert alert-danger m-3">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>'); }); }

    $('#btnSearch, #filter_page, #filter_definition, #filter_active').on('click change', function() { loadData(); });
    $('#table_search').on('keypress', function(e) { if (e.which === 13) loadData(); });
    $('#btnClearSearch').on('click', function() { $('#table_search').val(''); loadData(); });
    $('#btnResetFilter').on('click', function() { $('#table_search').val(''); $('#filter_page').val(''); $('#filter_definition').val(''); if ($('#filter_active').length) $('#filter_active').val(''); loadData(); });
    $(document).on('click', '.pagination a', function(e) { e.preventDefault(); loadData($(this).attr('href')); });
    $('#btnAddRecord').on('click', function() { const pageId = $('#filter_page').val(); const url = pageId ? $page.data('create-url') + '?page_id=' + encodeURIComponent(pageId) : $page.data('create-url'); openModal(url, 'Create Page Section'); });
    $(document).on('click', '.btn-edit', function() { openModal($(this).data('url'), 'Edit Page Section'); });
    $(document).on('click', '.btn-show', function() { openModal($(this).data('url'), 'Page Section Details'); });

    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();
        if (window.tinymce) window.tinymce.triggerSave();
        const $form = $(this); const $button = $form.find('button[type="submit"]'); const formData = new FormData(this);
        $button.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin ml-1"></i>');
        $('.invalid-feedback, .text-danger[class*="error-"]').text(''); $('.form-control').removeClass('is-invalid');
        $.ajax({ url: $form.attr('action'), type: 'POST', data: formData, processData: false, contentType: false, success: function(res) { $('#ajaxModal').modal('hide'); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); }, error: function(xhr) { $button.prop('disabled', false).find('.fa-spinner').remove(); if (xhr.status === 422 && xhr.responseJSON?.errors) { $.each(xhr.responseJSON.errors, function(field, errors) { const base = field.split('.')[0]; const errorClass = field.replace(/\./g, '_'); $(`[name="${base}"], [name="${base}[]"]`).addClass('is-invalid'); $(`.error-${errorClass}, .error-${base}`).first().text(errors[0]).show(); }); } else { Swal.fire('Error', xhr.responseJSON?.message || 'An unexpected error occurred.', 'error'); } } });
    });

    $(document).on('click', '.btn-toggle', function() { const $button = $(this); Swal.fire({ title: $button.data('label') + ' Page Section?', text: 'This changes whether the section can render when its schedule also allows it.', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes' }).then(function(result) { if (!result.value) return; $.post($button.data('url'), {}, function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2500 }); loadData(); }).fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Status update failed.', 'error'); }); }); });
    $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function() { const $button = $(this); const isRestore = $button.hasClass('btn-restore'); const isForce = $button.hasClass('btn-force-delete'); Swal.fire({ title: isForce ? 'Permanently Delete?' : (isRestore ? 'Restore Page Section?' : 'Move Page Section to Trash?'), text: isForce ? 'Permanent deletion is blocked if section media still references this page section.' : 'Please confirm this action.', icon: 'warning', showCancelButton: true, confirmButtonColor: isForce ? '#d33' : '#3085d6', confirmButtonText: 'Yes, proceed' }).then(function(result) { if (!result.value) return; $.ajax({ url: $button.data('url'), type: isRestore ? 'POST' : 'DELETE', success: function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); loadData(); }, error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Action failed.', 'error'); } }); }); });
    $(document).on('click', '#checkAll', function() { $('.row-checkbox').prop('checked', this.checked); });

    $('#btnApplyBulk').on('click', function() { const action = $('#bulk_action').val(); const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get(); if (!action) return Swal.fire('Notice', 'Please select a bulk action.', 'info'); if (!ids.length) return Swal.fire('Notice', 'Please select at least one page section.', 'info'); Swal.fire({ title: 'Confirm Bulk Action', text: `Apply "${action}" to ${ids.length} page section(s)?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, proceed' }).then(function(result) { if (!result.value) return; $.post($page.data('bulk-url'), { action: action, ids: ids }, function(res) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 }); $('#bulk_action').val(''); loadData(); }).fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Bulk action failed.', 'error'); }); }); });
});
</script>
