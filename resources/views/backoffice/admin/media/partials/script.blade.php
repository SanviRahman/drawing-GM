<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const $page = $('#page-manager');
    const $wrapper = $('#content-wrapper');
    const isTrash = $page.data('mode') === 'trash';

    const params = () => ({
        search: $('#table_search').val(),
        type: $('#filter_type').val(),
        collection: $('#filter_collection').val(),
        disk: $('#filter_disk').length ? $('#filter_disk').val() : '',
    });

    function loadData(url = $page.data('index-url')) {
        $wrapper.addClass('loading');
        $.get(url, params()).done((res) => {
            $wrapper.html(res.html);
            $('#checkAll').prop('checked', false);
        }).fail((xhr) => {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load media.', 'error');
        }).always(() => $wrapper.removeClass('loading'));
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#ajaxModal').modal({ backdrop: 'static', keyboard: false, show: true });
        $.get(url).done((res) => $('#modal-body').html(res.html)).fail(() => $('#modal-body').html('<div class="alert alert-danger">Failed to load content.</div>'));
    }

    $('#btnAddRecord').on('click', () => openModal($page.data('create-url'), 'Upload Media'));
    $(document).on('click', '.btn-show', function () { openModal($(this).data('url'), 'Media Details'); });
    $(document).on('click', '.btn-edit', function () { openModal($(this).data('url'), 'Edit Media Metadata'); });

    $('#btnOpenGlobalPicker').on('click', function () {
        if (!window.MediaPicker) return Swal.fire('Error', 'Global Media Picker is not available.', 'error');
        MediaPicker.open(() => {}, { multiple: true, title: 'Global Media Picker' });
    });

    $('#btnSearch, #filter_type, #filter_collection, #filter_disk').on('click change', () => loadData());
    $('#table_search').on('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); loadData(); } });
    $('#btnResetFilter').on('click', function () {
        $('#table_search').val(''); $('#filter_type, #filter_collection, #filter_disk').val(''); loadData();
    });
    $(document).on('click', '#content-wrapper .pagination a', function (e) { e.preventDefault(); loadData($(this).attr('href')); });

    $(document).on('submit', '#ajax-form', function (e) {
        e.preventDefault();
        const $form = $(this), data = new FormData(this), $button = $form.find('[type="submit"]');
        $button.prop('disabled', true);
        $('.invalid-feedback, .text-danger[class*="error-"]').text('');
        $.ajax({ url: $form.attr('action'), type: 'POST', data, processData: false, contentType: false })
            .done((res) => { $('#ajaxModal').modal('hide'); showAlert(res.message, 'success'); loadData(); $(document).trigger('media:changed'); })
            .fail((xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.entries(xhr.responseJSON.errors).forEach(([field, errors]) => {
                        const base = field.split('.')[0];
                        $(`.error-${base}`).first().text(errors[0]);
                    });
                } else Swal.fire('Error', xhr.responseJSON?.message || 'Media action failed.', 'error');
            }).always(() => $button.prop('disabled', false));
    });

    $(document).on('click', '#checkAll', function () { $('.row-checkbox').prop('checked', this.checked); });
    const selectedIds = () => $('.row-checkbox:checked').map(function () { return this.value; }).get();

    function bulk(action) {
        const ids = selectedIds();
        if (!ids.length) return Swal.fire('Notice', 'Select at least one media item.', 'info');
        const force = action === 'force_delete';
        showConfirm({ title: force ? 'Permanently delete selected media?' : 'Confirm media action?', text: force ? 'Physical files will be removed permanently.' : `Apply action to ${ids.length} selected item(s)?`, confirmButtonText: force ? 'Force delete' : 'Continue' })
            .then((result) => {
                if (!result.isConfirmed) return;
                $.post($page.data('bulk-url'), { action, ids }).done((res) => { showAlert(res.message, 'success'); loadData(); $(document).trigger('media:changed'); }).fail((xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'Bulk action failed.', 'error'));
            });
    }

    $('#btnBulkDelete').on('click', () => bulk('delete'));
    $('#btnBulkRestore').on('click', () => bulk('restore'));
    $('#btnBulkForceDelete').on('click', () => bulk('force_delete'));

    $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function () {
        const url = $(this).data('url');
        const restore = $(this).hasClass('btn-restore');
        const force = $(this).hasClass('btn-force-delete');
        showConfirm({ title: force ? 'Permanently delete media?' : (restore ? 'Restore media?' : 'Move media to trash?'), text: force ? 'The physical file will also be deleted.' : 'You can change this later from the media manager.', confirmButtonText: force ? 'Force delete' : 'Continue' })
            .then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({ url, type: restore ? 'POST' : 'DELETE' }).done((res) => { showAlert(res.message, 'success'); loadData(); $(document).trigger('media:changed'); }).fail((xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'Action failed.', 'error'));
            });
    });

    $(document).on('media:changed', function () {
        if (window.MediaPicker) MediaPicker.refresh();
    });
});
</script>
