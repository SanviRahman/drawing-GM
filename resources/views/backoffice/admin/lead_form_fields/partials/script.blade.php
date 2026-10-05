<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const $page = $('#page-manager');
    const $wrapper = $('#content-wrapper');

    const notify = (message, type = 'success') => window.showAlert(message, type);

    const confirmAction = (text, options = {}) => window.showConfirm({
        title: options.title || 'Are you sure?',
        text,
        icon: options.icon || 'warning',
        confirmButtonText: options.confirmButtonText || 'Yes, continue',
        cancelButtonText: options.cancelButtonText || 'Cancel'
    });

    function loadData(url = $page.data('index-url')) {
        $wrapper.addClass('loading');

        $.get(url, {
            search: $('#table_search').val(),
            status: $('#filter_status').length ? $('#filter_status').val() : ''
        }).done(function (res) {
            $wrapper.html(res.html).removeClass('loading');
            $('#checkAll').prop('checked', false);
        }).fail(function () {
            $wrapper.removeClass('loading');
            notify('Failed to load booking fields.', 'error');
        });
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i></div>');
        $('#ajaxModal').modal('show');

        $.get(url).done(function (res) {
            $('#modal-body').html(res.html);
            document.dispatchEvent(new CustomEvent('admin:content-updated'));
        }).fail(function (xhr) {
            $('#modal-body').html('<div class="alert alert-danger">' + (xhr.responseJSON?.message || 'Failed to load form.') + '</div>');
        });
    }

    $('#btnAddRecord').on('click', () => openModal($page.data('create-url'), 'Create Booking Field'));
    $(document).on('click', '.btn-edit', function () { openModal($(this).data('url'), 'Edit Booking Field'); });
    $(document).on('click', '.btn-show', function () { openModal($(this).data('url'), 'Booking Field Details'); });

    $('#filter-form').on('submit', function (e) { e.preventDefault(); loadData(); });
    $('#filter_status').on('change', () => loadData());
    $('#btnResetFilter').on('click', function () {
        $('#table_search, #filter_status').val('');
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function (e) {
        e.preventDefault();
        loadData($(this).attr('href'));
    });

    $(document).on('change', '#checkAll', function () {
        $('.row-checkbox').prop('checked', this.checked);
    });

    $(document).on('submit', '#ajax-form', function (e) {
        e.preventDefault();

        if (window.tinymce) window.tinymce.triggerSave();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true);

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback, .text-danger.error-options').text('');

        $.ajax({
            url: $form.attr('action'),
            method: $form.find('input[name="_method"]').val() || 'POST',
            data: $form.serialize()
        }).done(function (res) {
            $('#ajaxModal').modal('hide');
            notify(res.message, 'success');
            loadData();
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(([key, messages]) => {
                    const normalized = key.replace(/\.\d+/g, '');
                    $form.find(`[name="${key}"], [name="${normalized}[]"]`).first().addClass('is-invalid');
                    $form.find(`.error-${normalized.replace(/\./g, '-')}`).first().text(messages[0]);
                });
            } else {
                notify(xhr.responseJSON?.message || 'Request failed.', 'error');
            }
        }).always(() => $button.prop('disabled', false));
    });

    function postAction(url, method, confirmText) {
        confirmAction(confirmText).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url,
                method,
                data: { _token: $('meta[name="csrf-token"]').attr('content') }
            })
                .done(function (res) {
                    notify(res.message, 'success');
                    loadData();
                })
                .fail(function (xhr) {
                    notify(xhr.responseJSON?.message || 'Action failed.', 'error');
                });
        });
    }

    $(document).on('click', '.btn-delete', function () { postAction($(this).data('url'), 'DELETE', 'Move this field to Trash?'); });
    $(document).on('click', '.btn-force-delete', function () { postAction($(this).data('url'), 'DELETE', 'Permanently delete this field? Historical lead answers keep snapshots.'); });
    $(document).on('click', '.btn-restore', function () { postAction($(this).data('url'), 'POST', 'Restore this field?'); });
    $(document).on('click', '.btn-toggle', function () { postAction($(this).data('url'), 'POST', 'Change this field status?'); });

    $('#btnApplyBulk').on('click', function () {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function () { return this.value; }).get();

        if (!action || ids.length === 0) {
            notify('Choose a bulk action and at least one record.', 'warning');
            return;
        }

        $.post($page.data('bulk-url'), { action, ids })
            .done(function (res) { notify(res.message, 'success'); loadData(); })
            .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Bulk action failed.', 'error'); });
    });

    $('#btnSaveOrder').on('click', function () {
        const items = $('#content-wrapper tbody tr[data-id]').map(function () {
            return {
                id: $(this).data('id'),
                sort_order: $(this).find('.field-sort-order').val()
            };
        }).get();

        if (!items.length) return;

        $.post($page.data('reorder-url'), { items })
            .done(function (res) { notify(res.message, 'success'); loadData(); })
            .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Reorder failed.', 'error'); });
    });

    $('#ajaxModal').on('hidden.bs.modal', function () {
        if (window.tinymce) {
            this.querySelectorAll('textarea.tinymce-editor').forEach(function (textarea) {
                const editor = window.tinymce.get(textarea.id);
                if (editor) editor.destroy();
                delete textarea.dataset.tinymceInitialized;
            });
        }

        $('#modal-body').empty();
    });
});
</script>
