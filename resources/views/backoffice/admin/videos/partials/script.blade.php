<script>
$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    const $page = $('#video-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    function destroyEditors() {
        if (!window.tinymce) return;
        $modalBody.find('textarea.tinymce-editor').each(function() {
            const editor = this.id ? tinymce.get(this.id) : null;
            if (editor) editor.remove();
            delete this.dataset.tinymceInitialized;
        });
    }

    function showToast(icon, message, timer = 2500) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true
        });
    }

    function requestError(xhr, fallback = 'Request failed.') {
        let message = fallback;
        if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    function buildFilterUrl() {
        const $form = $('#filterForm');
        const action = $form.attr('action') || $page.data('index-url');
        const query = $form.serialize();
        return query ? action + '?' + query : action;
    }

    function loadData(url = null) {
        const requestUrl = url || buildFilterUrl();
        $wrapper.addClass('loading');
        $.ajax({
            url: requestUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function(res) {
            if (res && typeof res.html !== 'undefined') {
                $wrapper.html(res.html);
                $('#checkAll').prop('checked', false);
                return;
            }
            requestError({
                responseJSON: {
                    message: 'Invalid AJAX response.'
                }
            }, 'Invalid AJAX response.');
        }).fail(function(xhr) {
            requestError(xhr, 'Failed to load videos.');
        }).always(function() {
            $wrapper.removeClass('loading');
        });
    }

    function refreshTable() {
        loadData($page.data('index-url') + '?' + $('#filterForm').serialize());
    }

    function openModal(url, title) {
        destroyEditors();
        if (!$modal.length || !$modalBody.length) {
            Swal.fire('Error', 'Video modal container not found.', 'error');
            return;
        }
        if (!url) {
            Swal.fire('Error', 'Video form URL not found.', 'error');
            return;
        }
        $('#modal-title').text(title);
        $modalBody.html(
            '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted mb-0">Loading...</p></div>'
            );
        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function(res) {
            if (!res || typeof res.html === 'undefined') {
                $modalBody.html('<div class="alert alert-danger mb-0">Invalid server response.</div>');
                return;
            }
            $modalBody.html(res.html);
            document.dispatchEvent(new CustomEvent('admin:content-updated'));
        }).fail(function(xhr) {
            let message = 'Failed to load Video form.';
            if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
            $modalBody.html('<div class="alert alert-danger mb-0">' + message + '</div>');
        });
    }

    function confirmed(result) {
        return result && (result.isConfirmed === true || result.value === true);
    }

    function confirmation(action, count = 1) {
        const total = Number(count) || 1;
        const bulk = total > 1;
        if (action === 'delete') return Swal.fire({
            icon: 'warning',
            title: bulk ? 'Confirm Bulk Trash' : 'Move to Trash?',
            text: bulk ? 'Move ' + total + ' video(s) to trash?' :
                'You can restore this video later.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Move to trash',
            cancelButtonText: 'Cancel'
        });
        if (action === 'restore') return Swal.fire({
            icon: 'question',
            title: bulk ? 'Confirm Bulk Restore' : 'Restore Video?',
            text: bulk ? 'Restore ' + total + ' video(s)?' : 'This video will be restored.',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Restore',
            cancelButtonText: 'Cancel'
        });
        if (action === 'force_delete') return Swal.fire({
            icon: 'warning',
            title: bulk ? 'Confirm Permanent Delete' : 'Permanently Delete?',
            text: 'This action cannot be undone. Owned video/poster media will also be permanently removed.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete permanently',
            cancelButtonText: 'Cancel',
            focusCancel: true
        });
        return Promise.resolve({
            isConfirmed: true,
            value: true
        });
    }
    $(document).on('click', '#btnAddRecord', function(e) {
        e.preventDefault();
        openModal($page.data('create-url'), 'Create Video');
    });
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        openModal($(this).data('url'), 'Edit Video');
    });
    $(document).on('click', '.btn-show', function(e) {
        e.preventDefault();
        openModal($(this).data('url'), 'Video Details');
    });
    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        loadData();
        return false;
    });
    $(document).on('click', '#btnSearch', function(e) {
        e.preventDefault();
        e.stopPropagation();
        loadData();
    });
    $(document).on('keydown', '#table_search', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            clearTimeout(searchTimer);
            loadData();
            return false;
        }
    });
    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadData();
        }, 500);
    });
    $(document).on('change', '#filter_source_type,#filter_status,#filter_processing_status', function() {
        loadData();
    });
    $(document).on('click', '#btnResetFilter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_source_type').val('');
        $('#filter_processing_status').val('');
        $('#filter_status').val('');
        $('#table_search').val('');
        clearTimeout(searchTimer);
        loadData();
    });
    $(document).on('click', '#content-wrapper .pagination a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = $(this).attr('href');
        if (url) loadData(url);
        return false;
    });
    $(document).on('submit', '#ajax-form', function(e) {
        e.preventDefault();
        if (window.tinymce) window.tinymce.triggerSave();
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
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function(res) {
            $modal.modal('hide');
            showToast('success', res.message || 'Video saved successfully.');
            refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');
                    $form.find('[name="' + baseField + '"],[name="' + baseField +
                        '[]"]').addClass('is-invalid');
                    $form.find('.error-' + errorClass + ',.error-' + baseField).first()
                        .text(messages[0]).show();
                });
                return;
            }
            requestError(xhr);
        }).always(function() {
            $button.prop('disabled', false);
        });
    });
    $(document).on('click', '.btn-toggle', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        if (!url) return;
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json'
        }).done(function(res) {
            showToast('success', res.message || 'Status updated successfully.');
            refreshTable();
        }).fail(function(xhr) {
            requestError(xhr);
        });
    });
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        if (!url) {
            Swal.fire('Error', 'Delete URL not found.', 'error');
            return;
        }
        confirmation('delete').then(function(result) {
            if (!confirmed(result)) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json'
            }).done(function(res) {
                showToast('success', res.message || 'Video moved to trash.');
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr);
            });
        });
    });
    $(document).on('click', '.btn-restore', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = $(this).data('url');
        if (!url) {
            Swal.fire('Error', 'Restore URL not found.', 'error');
            return;
        }
        confirmation('restore').then(function(result) {
            if (!confirmed(result)) return;
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(res) {
                showToast('success', res.message || 'Video restored successfully.');
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr, 'Failed to restore video.');
            });
        });
    });
    $(document).on('click', '.btn-force-delete', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = $(this).data('url');
        if (!url) {
            Swal.fire('Error', 'Permanent delete URL not found.', 'error');
            return;
        }
        confirmation('force_delete').then(function(result) {
            if (!confirmed(result)) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(res) {
                showToast('success', res.message || 'Video permanently deleted.');
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr, 'Failed to permanently delete video.');
            });
        });
    });
    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });
    $(document).on('click', '#btnApplyBulk', function(e) {
        e.preventDefault();
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        if (!action) {
            Swal.fire('Notice', 'Please select a bulk action.', 'info');
            return;
        }
        if (!ids.length) {
            Swal.fire('Notice', 'Please select at least one video.', 'info');
            return;
        }
        const submitBulk = function() {
            $.ajax({
                url: $page.data('bulk-url'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    ids: ids
                },
                dataType: 'json'
            }).done(function(res) {
                $('#bulk_action').val('');
                $('#checkAll').prop('checked', false);
                showToast('success', res.message || 'Bulk action completed.');
                refreshTable();
            }).fail(function(xhr) {
                requestError(xhr, 'Bulk action failed.');
            });
        };
        if (['delete', 'restore', 'force_delete'].includes(action)) {
            confirmation(action, ids.length).then(function(result) {
                if (confirmed(result)) submitBulk();
            });
            return;
        }
        submitBulk();
    });
    $modal.on('hidden.bs.modal', function() {
        destroyEditors();
        $modalBody.empty();
    });
});
</script>