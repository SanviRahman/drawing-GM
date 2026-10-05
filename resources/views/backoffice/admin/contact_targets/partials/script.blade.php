<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    const $page = $('#page-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    function showToast(icon, message) {
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

    function requestError(xhr, fallback = 'Something went wrong.') {
        showToast('error', xhr.responseJSON?.message || fallback);
    }

    function currentParams() {
        return {
            contact_channel_id: $('#filter_channel').val() || '',
            target_type: $('#filter_target_type').val() || '',
            search: $('#table_search').val() || ''
        };
    }

    function loadData(url = $page.data('index-url')) {
        $wrapper.addClass('loading');

        $.get(url, currentParams())
            .done(function (res) {
                $wrapper.html(res.html);
                $('#checkAll').prop('checked', false);
            })
            .fail(function (xhr) {
                requestError(xhr, 'Failed to load contact targets.');
            })
            .always(function () {
                $wrapper.removeClass('loading');
            });
    }

    function refreshTable() {
        loadData($page.data('index-url'));
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $modalBody.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $modal.modal('show');

        $.get(url)
            .done(function (res) {
                $modalBody.html(res.html);
                document.dispatchEvent(new CustomEvent('admin:content-updated'));
            })
            .fail(function (xhr) {
                $modalBody.html(
                    '<div class="alert alert-danger m-3">' +
                    (xhr.responseJSON?.message || 'Failed to load content.') +
                    '</div>'
                );
            });
    }

    function confirmation(action, count = 1) {
        const total = Number(count) || 1;
        const bulk = total > 1;

        if (action === 'delete') {
            return Swal.fire({
                icon: 'warning',
                title: bulk ? 'Confirm Bulk Trash' : 'Move Contact Target to Trash?',
                text: bulk ? 'Move ' + total + ' contact target mapping(s) to trash?' : 'You can restore this mapping later.',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Move to trash',
                cancelButtonText: 'Cancel'
            });
        }

        if (action === 'restore') {
            return Swal.fire({
                icon: 'question',
                title: bulk ? 'Confirm Bulk Restore' : 'Restore Contact Target?',
                text: bulk ? 'Restore ' + total + ' contact target mapping(s)?' : 'The related channel and target must both be available.',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Restore',
                cancelButtonText: 'Cancel'
            });
        }

        if (action === 'force_delete') {
            return Swal.fire({
                icon: 'warning',
                title: bulk ? 'Confirm Permanent Delete' : 'Permanently Delete Contact Target?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete permanently',
                cancelButtonText: 'Cancel',
                focusCancel: true
            });
        }

        return Promise.resolve({isConfirmed: true});
    }

    $('#btnAddRecord').on('click', function () {
        openModal($page.data('create-url'), 'Create Contact Target');
    });

    $(document).on('click', '.btn-edit', function () {
        openModal($(this).data('url'), 'Edit Contact Target');
    });

    $(document).on('click', '.btn-show', function () {
        openModal($(this).data('url'), 'Contact Target Details');
    });

    $(document).on('submit', '#filterForm', function (e) {
        e.preventDefault();
        loadData();
    });

    $(document).on('change', '#filter_channel, #filter_target_type', function () {
        loadData();
    });

    $(document).on('input', '#table_search', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadData();
        }, 400);
    });

    $('#btnResetFilter').on('click', function () {
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_channel, #filter_target_type').val('');
        $('#table_search').val('');
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadData(url);
    });

    $(document).on('submit', '#ajax-form', function (e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const $button = $form.find('button[type="submit"]');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[class*="error-"]').text('');
        $button.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false
        }).done(function (res) {
            $modal.modal('hide');
            showToast('success', res.message);
            refreshTable();
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function ([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');
                    const $field = $form.find('[name="' + baseField + '"]');

                    $field.addClass('is-invalid');
                    $form.find('.error-' + errorClass + ', .error-' + baseField)
                        .first()
                        .text(messages[0])
                        .show();
                });
                return;
            }

            requestError(xhr);
        }).always(function () {
            $button.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url');

        confirmation('delete').then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {_token: '{{ csrf_token() }}'}
            }).done(function (res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function (xhr) {
                requestError(xhr);
            });
        });
    });

    $(document).on('click', '.btn-restore', function () {
        const url = $(this).data('url');

        confirmation('restore').then(function (result) {
            if (!result.isConfirmed) return;

            $.post(url, {_token: '{{ csrf_token() }}'})
                .done(function (res) {
                    showToast('success', res.message);
                    refreshTable();
                })
                .fail(function (xhr) {
                    requestError(xhr);
                });
        });
    });

    $(document).on('click', '.btn-force-delete', function () {
        const url = $(this).data('url');

        confirmation('force_delete').then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {_token: '{{ csrf_token() }}'}
            }).done(function (res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function (xhr) {
                requestError(xhr);
            });
        });
    });

    $(document).on('change', '#checkAll', function () {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#btnApplyBulk').on('click', function () {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action) {
            showToast('warning', 'Please select a bulk action.');
            return;
        }

        if (!ids.length) {
            showToast('warning', 'Please select at least one contact target.');
            return;
        }

        confirmation(action, ids.length).then(function (result) {
            if (!result.isConfirmed) return;

            $.post($page.data('bulk-url'), {
                _token: '{{ csrf_token() }}',
                action: action,
                ids: ids
            }).done(function (res) {
                $('#bulk_action').val('');
                $('#checkAll').prop('checked', false);
                showToast('success', res.message);
                refreshTable();
            }).fail(function (xhr) {
                requestError(xhr, 'Bulk action failed.');
            });
        });
    });

    $modal.on('hidden.bs.modal', function () {
        $modalBody.empty();
    });
});
</script>
