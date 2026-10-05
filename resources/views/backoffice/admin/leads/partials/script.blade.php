<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const $page = $('#page-manager');
    const $wrapper = $('#content-wrapper');
    const statuses = @json(\App\Models\Lead::STATUSES);

    const notify = (message, type = 'success') => window.showAlert(message, type);

    const confirmAction = (text, options = {}) => window.showConfirm({
        title: options.title || 'Are you sure?',
        text,
        icon: options.icon || 'warning',
        confirmButtonText: options.confirmButtonText || 'Yes, continue',
        cancelButtonText: options.cancelButtonText || 'Cancel'
    });

    function params() {
        return {
            search: $('#table_search').val(),
            status: $('#filter_status').length ? $('#filter_status').val() : '',
            assigned_to: $('#filter_assigned_to').length ? $('#filter_assigned_to').val() : '',
            location_id: $('#filter_location_id').length ? $('#filter_location_id').val() : ''
        };
    }

    function loadData(url = $page.data('index-url')) {
        $wrapper.addClass('loading');

        $.get(url, params()).done(function (res) {
            $wrapper.html(res.html).removeClass('loading');
            $('#checkAll').prop('checked', false);
        }).fail(function () {
            $wrapper.removeClass('loading');
            notify('Failed to load leads.', 'error');
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
            $('#modal-body').html('<div class="alert alert-danger">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>');
        });
    }

    $('#btnAddRecord').on('click', () => openModal($page.data('create-url'), 'Create Lead'));
    $(document).on('click', '.btn-edit', function () { openModal($(this).data('url'), 'Edit Lead'); });
    $(document).on('click', '.btn-show', function () { openModal($(this).data('url'), 'Lead Details'); });

    $('#filter-form').on('submit', function (e) { e.preventDefault(); loadData(); });
    $('#filter_status, #filter_assigned_to, #filter_location_id').on('change', () => loadData());

    $('#btnResetFilter').on('click', function () {
        $('#table_search, #filter_status, #filter_assigned_to, #filter_location_id').val('').trigger('change.select2');
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

        const form = this;
        const $form = $(form);
        const $button = $form.find('button[type="submit"]');
        const data = new FormData(form);

        $button.prop('disabled', true);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback, .text-danger[class*="error-"]').text('');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data,
            processData: false,
            contentType: false
        }).done(function (res) {
            $('#ajaxModal').modal('hide');
            notify(res.message, 'success');
            loadData();
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(([key, messages]) => {
                    const normalized = key.replace(/\.\d+/g, '').replace(/\./g, '-');
                    const baseName = key.replace(/\.\d+$/, '');
                    $form.find(`[name="${key}"], [name="${baseName}[]"]`).first().addClass('is-invalid');
                    $form.find(`.error-${normalized}`).first().text(messages[0]);
                });
            } else {
                notify(xhr.responseJSON?.message || 'Request failed.', 'error');
            }
        }).always(() => $button.prop('disabled', false));
    });

    function actionRequest(url, method, text) {
        confirmAction(text).then(function (result) {
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

    $(document).on('click', '.btn-delete', function () { actionRequest($(this).data('url'), 'DELETE', 'Move this lead to Trash?'); });
    $(document).on('click', '.btn-force-delete', function () { actionRequest($(this).data('url'), 'DELETE', 'Permanently delete this lead and its private attachments/history?'); });
    $(document).on('click', '.btn-restore', function () { actionRequest($(this).data('url'), 'POST', 'Restore this lead?'); });

    $(document).on('click', '.btn-change-status', function () {
        const url = $(this).data('url');
        const current = String($(this).data('status'));

        const options = Object.entries(statuses)
            .map(([key, label]) => `<option value="${key}" ${key === current ? 'selected' : ''}>${label}</option>`)
            .join('');

        window.Swal.fire({
            title: 'Change Lead Status',
            html: `
                <select id="swal-lead-status" class="form-control mb-2">${options}</select>
                <input id="swal-lead-reason" class="form-control" maxlength="1000" placeholder="Optional reason">
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            preConfirm: () => ({
                status: $('#swal-lead-status').val(),
                reason: $('#swal-lead-reason').val()
            })
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post(url, result.value)
                .done(function (res) { notify(res.message, 'success'); loadData(); })
                .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Status update failed.', 'error'); });
        });
    });

    $('#btnApplyBulk').on('click', function () {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function () { return this.value; }).get();

        if (!action || ids.length === 0) {
            notify('Choose a bulk action and at least one lead.', 'warning');
            return;
        }

        $.post($page.data('bulk-url'), { action, ids })
            .done(function (res) { notify(res.message, 'success'); loadData(); })
            .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Bulk action failed.', 'error'); });
    });

    $('#btnExport').on('click', function () {
        const url = new URL($page.data('export-url'), window.location.origin);
        Object.entries(params()).forEach(([key, value]) => {
            if (value) url.searchParams.set(key, value);
        });
        window.location.href = url.toString();
    });

    $(document).on('submit', '#lead-note-form', function (e) {
        e.preventDefault();

        if (window.tinymce) window.tinymce.triggerSave();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]').prop('disabled', true);

        $.post($form.attr('action'), $form.serialize())
            .done(function (res) {
                notify(res.message, 'success');
                // Reload the current details using the same modal URL when possible.
                const currentUrl = $('#ajaxModal').data('current-url');
                if (currentUrl) openModal(currentUrl, 'Lead Details');
            })
            .fail(function (xhr) {
                notify(xhr.responseJSON?.message || 'Unable to add note.', 'error');
            })
            .always(() => $button.prop('disabled', false));
    });

    $(document).on('click', '.btn-delete-note', function () {
        const url = $(this).data('url');
        $.ajax({ url, method:'DELETE' })
            .done(function (res) {
                notify(res.message, 'success');
                const currentUrl = $('#ajaxModal').data('current-url');
                if (currentUrl) openModal(currentUrl, 'Lead Details');
            })
            .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Unable to delete note.', 'error'); });
    });

    $(document).on('click', '.btn-restore-note', function () {
        const url = $(this).data('url');
        $.post(url)
            .done(function (res) {
                notify(res.message, 'success');
                const currentUrl = $('#ajaxModal').data('current-url');
                if (currentUrl) openModal(currentUrl, 'Lead Details');
            })
            .fail(function (xhr) { notify(xhr.responseJSON?.message || 'Unable to restore note.', 'error'); });
    });

    // Remember the currently opened URL so nested note actions can refresh the modal.
    $(document).on('click', '.btn-show, .btn-edit', function () {
        $('#ajaxModal').data('current-url', $(this).data('url'));
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
        $('#ajaxModal').removeData('current-url');
    });
});
</script>
