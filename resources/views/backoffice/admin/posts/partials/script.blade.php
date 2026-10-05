<script>
$(document).ready(function () {
    $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});

    const $page = $('#post-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    function destroyEditors() {
        if (!window.tinymce) return;

        $modalBody.find('textarea.tinymce-editor').each(function () {
            const editor = this.id ? window.tinymce.get(this.id) : null;
            if (editor) editor.destroy();
            delete this.dataset.tinymceInitialized;
        });
    }

    function toast(message, icon = 'success') {
        Swal.fire({toast:true, position:'top-end', icon:icon, title:message, showConfirmButton:false, timer:3000});
    }

    function requestError(xhr, fallback = 'Action failed.') {
        let message = xhr.responseJSON?.message || fallback;
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            message = Object.values(xhr.responseJSON.errors).flat().join('\n');
        }
        Swal.fire('Error', message, 'error');
    }

    function buildFilterUrl() {
        const $form = $('#filterForm');
        const action = $form.attr('action') || $page.data('index-url');
        const query = $form.serialize();
        return query ? action + '?' + query : action;
    }

    function loadData(url = null, updateBrowserUrl = true) {
        const requestUrl = url || buildFilterUrl();
        $wrapper.addClass('loading');

        $.ajax({url:requestUrl, type:'GET', dataType:'json'})
            .done(function (res) {
                $wrapper.html(res.html);
                $('#checkAll').prop('checked', false);
                if (updateBrowserUrl && window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', requestUrl);
                }
            })
            .fail(function (xhr) { requestError(xhr, 'Failed to load blog posts.'); })
            .always(function () { $wrapper.removeClass('loading'); });
    }

    function refreshTable() { loadData(null, false); }

    function openModal(url, title) {
        destroyEditors();
        $('#modal-title').text(title);
        $modalBody.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted">Loading...</p></div>');
        $modal.modal({backdrop:'static', keyboard:false, show:true});

        $.get(url)
            .done(function (res) {
                $modalBody.html(res.html);
                document.dispatchEvent(new CustomEvent('admin:content-updated'));
            })
            .fail(function (xhr) {
                $modalBody.html('<div class="alert alert-danger m-3">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>');
            });
    }

    $('#btnAddRecord').on('click', function () { openModal($page.data('create-url'), 'Create Blog Post'); });
    $(document).on('click', '.btn-edit', function () { openModal($(this).data('url'), 'Edit Blog Post'); });
    $(document).on('click', '.btn-show', function () { openModal($(this).data('url'), 'Blog Post Details'); });
    $(document).on('click', '.btn-preview', function () { openModal($(this).data('url'), 'Blog Post Preview'); });

    $(document).on('submit', '#filterForm', function (e) { e.preventDefault(); loadData(); });
    $(document).on('change', '#filter_status, #filter_category, #filter_author', function () { loadData(); });
    $(document).on('input', '#table_search', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadData(); }, 400);
    });

    $('#btnResetFilter').on('click', function () {
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_status, #filter_category, #filter_author, #table_search').val('');
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadData(url);
    });

    $(document).on('submit', '#ajax-form', function (e) {
        e.preventDefault();
        if (window.tinymce) window.tinymce.triggerSave();

        const form = this;
        const $form = $(form);
        const $button = $form.find('button[type="submit"]');
        const originalHtml = $button.html();

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[class*="error-"]').text('');
        $button.prop('disabled', true).html(originalHtml + ' <i class="fas fa-spinner fa-spin ml-1"></i>');

        $.ajax({
            url:$form.attr('action'),
            type:'POST',
            data:new FormData(form),
            processData:false,
            contentType:false
        }).done(function (res) {
            $modal.modal('hide');
            toast(res.message);
            refreshTable();
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function ([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');
                    const $field = $form.find('[name="' + baseField + '"], [name="' + baseField + '[]"]');
                    $field.addClass('is-invalid');
                    $form.find('.error-' + errorClass + ', .error-' + baseField).first().text(messages[0]).show();
                });
                return;
            }
            requestError(xhr);
        }).always(function () {
            $button.prop('disabled', false).html(originalHtml);
        });
    });

    $(document).on('click', '.btn-status-action', function () {
        const $button = $(this);
        Swal.fire({title:$button.data('label') + '?', text:'Apply this publication-state change?', icon:'question', showCancelButton:true, confirmButtonText:'Yes, proceed'})
            .then(function (result) {
                if (!result.isConfirmed && !result.value) return;
                $.post($button.data('url'), {})
                    .done(function (res) { toast(res.message); refreshTable(); })
                    .fail(function (xhr) { requestError(xhr, 'Status update failed.'); });
            });
    });

    $(document).on('click', '.btn-duplicate', function () {
        const $button = $(this);
        Swal.fire({title:'Duplicate Blog Post?', text:'A new draft copy will be created with copied featured/content images.', icon:'question', showCancelButton:true, confirmButtonText:'Duplicate'})
            .then(function (result) {
                if (!result.isConfirmed && !result.value) return;
                $.post($button.data('url'), {})
                    .done(function (res) { toast(res.message); refreshTable(); })
                    .fail(function (xhr) { requestError(xhr, 'Duplicate failed.'); });
            });
    });

    $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function () {
        const $button = $(this);
        const isRestore = $button.hasClass('btn-restore');
        const isForce = $button.hasClass('btn-force-delete');
        const title = isForce ? 'Permanently Delete Blog Post?' : (isRestore ? 'Restore Blog Post?' : 'Move Blog Post to Trash?');
        const text = isForce ? 'This permanently removes the post and all Post-owned media files.' : 'Please confirm this action.';

        Swal.fire({title:title, text:text, icon:'warning', showCancelButton:true, confirmButtonColor:isForce ? '#dc3545' : '#3085d6', confirmButtonText:'Yes, proceed'})
            .then(function (result) {
                if (!result.isConfirmed && !result.value) return;
                $.ajax({url:$button.data('url'), type:isRestore ? 'POST' : 'DELETE'})
                    .done(function (res) { toast(res.message); refreshTable(); })
                    .fail(function (xhr) { requestError(xhr); });
            });
    });

    $(document).on('change', '#checkAll', function () { $('.row-checkbox').prop('checked', $(this).prop('checked')); });

    $('#btnApplyBulk').on('click', function () {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (!action) return Swal.fire('Notice', 'Please select a bulk action.', 'info');
        if (!ids.length) return Swal.fire('Notice', 'Please select at least one blog post.', 'info');

        const permanent = action === 'force_delete';
        Swal.fire({
            title:'Confirm Bulk Action',
            text: permanent ? 'Permanent deletion also removes Post-owned media files and cannot be undone.' : 'Apply "' + action + '" to ' + ids.length + ' blog post(s)?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:permanent ? '#dc3545' : '#3085d6',
            confirmButtonText:'Yes, proceed'
        }).then(function (result) {
            if (!result.isConfirmed && !result.value) return;

            $.post($page.data('bulk-url'), {action:action, ids:ids})
                .done(function (res) {
                    $('#bulk_action').val('');
                    $('#checkAll').prop('checked', false);
                    toast(res.message);
                    refreshTable();
                })
                .fail(function (xhr) { requestError(xhr, 'Bulk action failed.'); });
        });
    });

    $modal.on('hidden.bs.modal', function () {
        destroyEditors();
        $modalBody.empty();
    });
});
</script>
