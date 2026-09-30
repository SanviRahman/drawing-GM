<div class="modal fade" id="crudModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header py-2"><h5 class="modal-title mb-0">Pricing Package</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-3" id="crudModalBody"></div></div></div></div>

@push('js')
<script>
(function() {
    const $modal = $('#crudModal');
    const $body = $('#crudModalBody');
    let searchTimer = null;

    function destroyEditors() {
        if (!window.tinymce) return;
        $body.find('textarea.tinymce-editor').each(function() { const editor = tinymce.get(this.id); if (editor) editor.remove(); });
    }

    function toast(icon, message, timer = 2500) {
        Swal.fire({toast:true, position:'top-end', icon:icon, title:message, showConfirmButton:false, timer:timer, timerProgressBar:true});
    }

    function loadModal(url, title) {
        destroyEditors();
        $body.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $modal.find('.modal-title').text(title);
        $modal.modal({backdrop:'static', keyboard:false, show:true});
        $.get(url).done(function(response) { $body.html(response.html); document.dispatchEvent(new CustomEvent('admin:content-updated')); }).fail(function(xhr) { $body.html('<div class="alert alert-danger mb-0">' + (xhr.responseJSON?.message || 'Unable to load content.') + '</div>'); });
    }

    function buildFilterUrl() { const $form = $('#filterForm'); const query = $form.serialize(); return query ? $form.attr('action') + '?' + query : $form.attr('action'); }

    function loadTable(url = null, updateUrl = true) {
        const requestUrl = url || buildFilterUrl();
        $('#tableContainer').css({'opacity':'0.55','pointer-events':'none'});
        $.get(requestUrl).done(function(response) {
            $('#tableContainer').html(response.html);
            if (updateUrl && history.replaceState) history.replaceState({}, '', requestUrl);
        }).fail(function(xhr) { toast('error', xhr.responseJSON?.message || 'Unable to load pricing packages.', 3500); }).always(function() { $('#tableContainer').css({'opacity':'1','pointer-events':'auto'}); });
    }

    function refreshTable() { loadTable(null, false); }

    function confirmAction(action, count = 1) {
        const bulk = count > 1;
        let title = 'Are you sure?';
        let text = 'This action will be applied.';
        let confirmButtonText = 'Yes, proceed';
        if (action === 'delete') { title = bulk ? 'Confirm Bulk Trash' : 'Move to Trash?'; text = bulk ? 'Move ' + count + ' pricing package(s) to trash?' : 'You can restore this package later.'; confirmButtonText = 'Move to trash'; }
        if (action === 'restore') { title = bulk ? 'Confirm Bulk Restore' : 'Restore Pricing Package?'; text = bulk ? 'Restore ' + count + ' pricing package(s)?' : 'This package will be restored.'; confirmButtonText = 'Restore'; }
        if (action === 'force_delete') { title = bulk ? 'Confirm Permanent Delete' : 'Permanently Delete?'; text = 'This action cannot be undone.'; confirmButtonText = 'Delete permanently'; }
        return Swal.fire({title:title, text:text, icon:'warning', showCancelButton:true, confirmButtonText:confirmButtonText, cancelButtonText:'Cancel', confirmButtonColor: action === 'restore' ? '#28a745' : '#dc3545'});
    }

    $(document).on('click', '.btn-create', function() { loadModal($(this).data('url'), 'Create Pricing Package'); });
    $(document).on('click', '.btn-edit', function() { loadModal($(this).data('url'), 'Edit Pricing Package'); });
    $(document).on('click', '.btn-show', function() { loadModal($(this).data('url'), 'Pricing Package Details'); });
    $(document).on('submit', '#filterForm', function(e) { e.preventDefault(); loadTable(); });
    $(document).on('change', '#filter_service, #filter_location, #filter_status, #filter_featured', function() { loadTable(); });
    $(document).on('input', '#table_search', function() { clearTimeout(searchTimer); searchTimer = setTimeout(function() { loadTable(); }, 400); });
    $(document).on('click', '.btn-reset-filter', function() { const form = $('#filterForm')[0]; if (form) form.reset(); loadTable(); });
    $(document).on('click', '#tableContainer .pagination a', function(e) { e.preventDefault(); const url = $(this).attr('href'); if (url) loadTable(url); });

    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();
        if (window.tinymce) tinymce.triggerSave();
        const $form = $(this); const $submit = $form.find('button[type="submit"]');
        $form.find('.is-invalid').removeClass('is-invalid'); $form.find('[class*="error-"]').text(''); $submit.prop('disabled', true);
        $.ajax({url:$form.attr('action'), method:$form.find('input[name="_method"]').val() || 'POST', data:new FormData(this), processData:false, contentType:false}).done(function(response) {
            $modal.modal('hide'); toast('success', response.message); refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([key, messages]) { const field = key.replace(/\./g, '_'); $('.error-' + field).text(messages[0]); $('[name="' + key + '"]').addClass('is-invalid'); });
                return;
            }
            toast('error', xhr.responseJSON?.message || 'Request failed.', 3500);
        }).always(function() { $submit.prop('disabled', false); });
    });

    $(document).on('click', '.btn-toggle, .btn-duplicate', function() { $.post($(this).data('url'), {_token:'{{ csrf_token() }}'}).done(function(response) { toast('success', response.message); refreshTable(); }).fail(function(xhr) { toast('error', xhr.responseJSON?.message || 'Request failed.', 3500); }); });

    $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function() {
        const $button = $(this); const url = $button.data('url'); const action = $button.hasClass('btn-restore') ? 'restore' : ($button.hasClass('btn-force-delete') ? 'force_delete' : 'delete');
        confirmAction(action).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({url:url, method: action === 'restore' ? 'POST' : 'DELETE', data:{_token:'{{ csrf_token() }}'}}).done(function(response) { toast('success', response.message); refreshTable(); }).fail(function(xhr) { toast('error', xhr.responseJSON?.message || 'Request failed.', 3500); });
        });
    });

    $(document).on('change', '#checkAll', function() { $('.row-checkbox').prop('checked', this.checked); });
    $(document).on('click', '#applyBulk', function() {
        const action = $('#bulkAction').val(); const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!action || !ids.length) { Swal.fire('Warning', 'Select an action and at least one pricing package.', 'warning'); return; }
        const submit = function() { $.post('{{ route('admin.pricing_packages.multiple_action') }}', {_token:'{{ csrf_token() }}', action:action, ids:ids}).done(function(response) { $('#bulkAction').val(''); toast('success', response.message); refreshTable(); }).fail(function(xhr) { toast('error', xhr.responseJSON?.message || 'Bulk action failed.', 3500); }); };
        if (['delete','restore','force_delete'].includes(action)) { confirmAction(action, ids.length).then(function(result) { if (result.isConfirmed) submit(); }); return; }
        submit();
    });

    $modal.on('hidden.bs.modal', function() { destroyEditors(); $body.empty(); });
})();
</script>
@endpush
