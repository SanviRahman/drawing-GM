<div class="modal fade" id="crudModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header py-2"><h5 class="modal-title mb-0">Service Feature</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-3" id="crudModalBody"></div></div></div></div>

@push('js')
<script>
(function() {
    const $modal = $('#crudModal');
    const $body = $('#crudModalBody');
    let searchTimer = null;

    function destroyModalEditors() {
        if (!window.tinymce) return;

        $body.find('textarea.tinymce-editor').each(function() {
            const editor = tinymce.get(this.id);

            if (editor) {
                editor.remove();
            }
        });
    }

    function loadModal(url, title) {
        destroyModalEditors();

        $body.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="text-muted small mt-2">Loading...</div></div>');
        $modal.find('.modal-title').text(title || 'Service Feature');
        $modal.modal({backdrop: 'static', keyboard: false, show: true});

        $.get(url).done(function(response) {
            $body.html(response.html);
            document.dispatchEvent(new CustomEvent('admin:content-updated'));
        }).fail(function(xhr) {
            $body.html('<div class="alert alert-danger mb-0">' + (xhr.responseJSON?.message || 'Unable to load content.') + '</div>');
        });
    }

    function buildFilterUrl() {
        const $form = $('#filterForm');
        const action = $form.attr('action') || window.location.pathname;
        const query = $form.serialize();

        return query ? action + '?' + query : action;
    }

    function setTableLoading() {
        $('#tableContainer').css({'opacity': '0.55', 'pointer-events': 'none'});
    }

    function removeTableLoading() {
        $('#tableContainer').css({'opacity': '1', 'pointer-events': 'auto'});
    }

    function loadTable(url = null, updateBrowserUrl = true) {
        const requestUrl = url || buildFilterUrl();

        setTableLoading();

        $.ajax({
            url: requestUrl,
            method: 'GET',
            dataType: 'json'
        }).done(function(response) {
            $('#tableContainer').html(response.html);

            if (updateBrowserUrl && window.history && window.history.replaceState) {
                window.history.replaceState({}, '', requestUrl);
            }
        }).fail(function(xhr) {
            showError(xhr, 'Unable to load service features.');
        }).always(function() {
            removeTableLoading();
        });
    }

    function refreshTable() {
        loadTable(null, false);
    }

    function showToast(icon, message, timer = 2500) {
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

    function showSuccess(message) {
        showToast('success', message, 2500);
    }

    function showError(xhr, fallback = 'Request failed.') {
        showToast('error', xhr.responseJSON?.message || fallback, 3500);
    }

    function confirmationConfig(action, count = 1) {
        const total = Number(count) || 1;
        const isBulk = total > 1;

        if (action === 'delete') {
            return {
                icon: 'warning',
                title: isBulk ? 'Confirm Bulk Action' : 'Move to Trash?',
                text: isBulk ? 'Move ' + total + ' service feature(s) to trash?' : 'This service feature will be moved to trash. You can restore it later.',
                confirmButtonText: isBulk ? 'Yes, proceed' : 'Yes, move to trash',
                confirmButtonColor: '#dc3545'
            };
        }

        if (action === 'restore') {
            return {
                icon: 'question',
                title: isBulk ? 'Confirm Bulk Restore' : 'Restore Service Feature?',
                text: isBulk ? 'Restore ' + total + ' service feature(s)?' : 'This service feature will be restored.',
                confirmButtonText: isBulk ? 'Yes, restore' : 'Yes, restore',
                confirmButtonColor: '#28a745'
            };
        }

        if (action === 'force_delete') {
            return {
                icon: 'warning',
                title: isBulk ? 'Confirm Permanent Delete' : 'Permanently Delete?',
                text: isBulk ? 'Permanently delete ' + total + ' service feature(s)? This action cannot be undone.' : 'This action cannot be undone.',
                confirmButtonText: 'Delete permanently',
                confirmButtonColor: '#dc3545'
            };
        }

        return null;
    }

    function confirmAction(action, count = 1) {
        const config = confirmationConfig(action, count);

        if (!config) {
            return Promise.resolve({isConfirmed: true});
        }

        return Swal.fire({
            icon: config.icon,
            title: config.title,
            text: config.text,
            showCancelButton: true,
            confirmButtonText: config.confirmButtonText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: config.confirmButtonColor,
            cancelButtonColor: '#6c757d',
            reverseButtons: false,
            focusCancel: action === 'force_delete'
        });
    }

    $(document).on('click', '.btn-create', function() {
        loadModal($(this).data('url'), 'Create New Service Feature');
    });

    $(document).on('click', '.btn-edit', function() {
        loadModal($(this).data('url'), 'Edit Service Feature');
    });

    $(document).on('click', '.btn-show', function() {
        loadModal($(this).data('url'), 'Service Feature Details');
    });

    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadTable();
    });

    $(document).on('change', '#filter_service, #filter_status', function() {
        loadTable();
    });

    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function() {
            loadTable();
        }, 400);
    });

    $(document).on('click', '.btn-reset-filter', function() {
        const $form = $('#filterForm');

        if ($form.length) {
            $form[0].reset();
        }

        $('#filter_service').val('');
        $('#filter_status').val('');
        $('#table_search').val('');

        loadTable();
    });

    $(document).on('click', '#tableContainer .pagination a', function(e) {
        e.preventDefault();

        const url = $(this).attr('href');

        if (url) {
            loadTable(url);
        }
    });

    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();

        if (window.tinymce) {
            tinymce.triggerSave();
        }

        const form = this;
        const $form = $(form);
        const $submit = $form.find('button[type="submit"]');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[class*="error-"]').text('');
        $submit.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            method: $form.find('input[name="_method"]').val() || 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false
        }).done(function(response) {
            $modal.modal('hide');
            showSuccess(response.message);
            refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([key, messages]) {
                    const field = key.replace(/\./g, '_');

                    $('.error-' + field).text(messages[0]);
                    $('[name="' + key + '"]').addClass('is-invalid');
                });

                return;
            }

            showError(xhr);
        }).always(function() {
            $submit.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-toggle, .btn-duplicate', function() {
        const url = $(this).data('url');

        $.post(url, {_token: '{{ csrf_token() }}'}).done(function(response) {
            showSuccess(response.message);
            refreshTable();
        }).fail(function(xhr) {
            showError(xhr);
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const url = $(this).data('url');

        confirmAction('delete').then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {_token: '{{ csrf_token() }}'}
            }).done(function(response) {
                showSuccess(response.message);
                refreshTable();
            }).fail(function(xhr) {
                showError(xhr);
            });
        });
    });

    $(document).on('click', '.btn-restore', function() {
        const url = $(this).data('url');

        confirmAction('restore').then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.post(url, {_token: '{{ csrf_token() }}'}).done(function(response) {
                showSuccess(response.message);
                refreshTable();
            }).fail(function(xhr) {
                showError(xhr);
            });
        });
    });

    $(document).on('click', '.btn-force-delete', function() {
        const url = $(this).data('url');

        confirmAction('force_delete').then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {_token: '{{ csrf_token() }}'}
            }).done(function(response) {
                showSuccess(response.message);
                refreshTable();
            }).fail(function(xhr) {
                showError(xhr);
            });
        });
    });

    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '#applyBulk', function() {
        const action = $('#bulkAction').val();
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!action || !ids.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Selection Required',
                text: 'Select an action and at least one service feature.',
                confirmButtonText: 'OK'
            });

            return;
        }

        const submitBulkAction = function() {
            $.post('{{ route('admin.service_features.multiple_action') }}', {
                _token: '{{ csrf_token() }}',
                action: action,
                ids: ids
            }).done(function(response) {
                $('#bulkAction').val('');
                $('#checkAll').prop('checked', false);

                showSuccess(response.message);
                refreshTable();
            }).fail(function(xhr) {
                showError(xhr);
            });
        };

        if (['delete', 'restore', 'force_delete'].includes(action)) {
            confirmAction(action, ids.length).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                submitBulkAction();
            });

            return;
        }

        submitBulkAction();
    });

    $modal.on('hidden.bs.modal', function() {
        destroyModalEditors();
        $body.empty();
    });
})();
</script>
@endpush