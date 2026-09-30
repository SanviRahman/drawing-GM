<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    const $page = $('#pricing-item-manager');
    const $wrapper = $('#content-wrapper');
    let searchTimer = null;

    function showToast(icon, message, timer = 3000) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: function(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    function requestError(xhr, fallback = 'Request failed.') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: xhr.responseJSON?.message || fallback
        });
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

        $.ajax({
            url: requestUrl,
            type: 'GET',
            dataType: 'json'
        }).done(function(res) {
            $wrapper.html(res.html).removeClass('loading');
            $('#checkAll').prop('checked', false);

            if (updateBrowserUrl && window.history && window.history.replaceState) {
                window.history.replaceState({}, '', requestUrl);
            }
        }).fail(function(xhr) {
            $wrapper.removeClass('loading');
            requestError(xhr, 'Failed to load pricing items.');
        });
    }

    function refreshTable() {
        loadData(null, false);
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $('#modal-body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="text-muted small mt-2">Loading...</div></div>');
        $('#ajaxModal').modal({backdrop: 'static', keyboard: false, show: true});

        $.get(url).done(function(res) {
            $('#modal-body').html(res.html);
            document.dispatchEvent(new CustomEvent('admin:content-updated'));
        }).fail(function(xhr) {
            $('#modal-body').html('<div class="alert alert-danger mb-0">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>');
        });
    }

    function confirmation(action, count = 1) {
        const total = Number(count) || 1;
        const bulk = total > 1;

        if (action === 'delete') {
            return Swal.fire({
                icon: 'warning',
                title: bulk ? 'Confirm Bulk Trash' : 'Move to Trash?',
                text: bulk ? 'Move ' + total + ' pricing item(s) to trash?' : 'You can restore this pricing item later.',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: bulk ? 'Yes, proceed' : 'Move to trash',
                cancelButtonText: 'Cancel'
            });
        }

        if (action === 'restore') {
            return Swal.fire({
                icon: 'question',
                title: bulk ? 'Confirm Bulk Restore' : 'Restore Pricing Item?',
                text: bulk ? 'Restore ' + total + ' pricing item(s)?' : 'This pricing item will be restored.',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, restore',
                cancelButtonText: 'Cancel'
            });
        }

        if (action === 'force_delete') {
            return Swal.fire({
                icon: 'warning',
                title: bulk ? 'Confirm Permanent Delete' : 'Permanently Delete?',
                text: bulk ? 'Permanently delete ' + total + ' pricing item(s)? This action cannot be undone.' : 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete permanently',
                cancelButtonText: 'Cancel',
                focusCancel: true
            });
        }

        return Promise.resolve({isConfirmed: true});
    }

    $('#btnAddRecord').on('click', function() {
        openModal($page.data('create-url'), 'Create New Pricing Item');
    });

    $(document).on('click', '.btn-edit', function() {
        openModal($(this).data('url'), 'Edit Pricing Item');
    });

    $(document).on('click', '.btn-show', function() {
        openModal($(this).data('url'), 'Pricing Item Details');
    });

    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadData();
    });

    $(document).on('change', '#filter_package, #filter_type, #filter_status', function() {
        loadData();
    });

    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadData();
        }, 400);
    });

    $('#btnClearSearch').on('click', function() {
        $('#table_search').val('');
        loadData();
    });

    $('#btnResetFilter').on('click', function() {
        const $form = $('#filterForm');

        if ($form.length) {
            $form[0].reset();
        }

        $('#filter_package').val('');
        $('#filter_type').val('');
        $('#filter_status').val('');
        $('#table_search').val('');
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');

        if (url) {
            loadData(url);
        }
    });

    $(document).on('submit', '#ajax-form', function(e) {
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
        }).done(function(res) {
            $('#ajaxModal').modal('hide');
            showToast('success', res.message);
            refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');

                    $(`[name="${baseField}"], [name="${baseField}[]"]`).addClass('is-invalid');
                    $(`.error-${errorClass}, .error-${baseField}`).first().text(messages[0]).show();
                });

                return;
            }

            requestError(xhr);
        }).always(function() {
            $button.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-toggle, .btn-duplicate', function() {
        $.post($(this).data('url')).done(function(res) {
            showToast('success', res.message);
            refreshTable();
        }).fail(function(xhr) {
            requestError(xhr);
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const url = $(this).data('url');

        confirmation('delete').then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({url: url, type: 'DELETE'}).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr);
            });
        });
    });

    $(document).on('click', '.btn-restore', function() {
        const url = $(this).data('url');

        confirmation('restore').then(function(result) {
            if (!result.isConfirmed) return;

            $.post(url).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr);
            });
        });
    });

    $(document).on('click', '.btn-force-delete', function() {
        const url = $(this).data('url');

        confirmation('force_delete').then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({url: url, type: 'DELETE'}).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr);
            });
        });
    });

    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#btnApplyBulk').on('click', function() {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!action) {
            Swal.fire('Notice', 'Please select a bulk action.', 'info');
            return;
        }

        if (!ids.length) {
            Swal.fire('Notice', 'Please select at least one pricing item.', 'info');
            return;
        }

        const submitBulk = function() {
            $.post($page.data('bulk-url'), {
                action: action,
                ids: ids
            }).done(function(res) {
                $('#bulk_action').val('');
                $('#checkAll').prop('checked', false);
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr, 'Bulk action failed.');
            });
        };

        if (['delete', 'restore', 'force_delete'].includes(action)) {
            confirmation(action, ids.length).then(function(result) {
                if (!result.isConfirmed) return;
                submitBulk();
            });

            return;
        }

        submitBulk();
    });
});
</script>
