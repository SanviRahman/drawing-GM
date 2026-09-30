<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header py-2"><h5 class="modal-title mb-0" id="modal-title">Pricing Add-on</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-3" id="modal-body"></div></div></div></div>

@push('js')
<script>
$(function() {
    const $page = $('#pricing-addon-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    function destroyEditors() {
        if (!window.tinymce) return;
        $modalBody.find('textarea.tinymce-editor').each(function() {
            const editor = tinymce.get(this.id);
            if (editor) editor.remove();
            delete this.dataset.tinymceInitialized;
        });
    }

    function showToast(icon, message, timer = 2500) {
        Swal.fire({toast:true, position:'top-end', icon:icon, title:message, showConfirmButton:false, timer:timer, timerProgressBar:true});
    }

    function requestError(xhr, fallback = 'Request failed.') {
        Swal.fire({icon:'error', title:'Error', text:xhr.responseJSON?.message || fallback});
    }

    function buildFilterUrl() {
        const $form = $('#filterForm');
        const action = $form.attr('action') || $page.data('index-url');
        const query = $form.serialize();
        return query ? action + '?' + query : action;
    }

    function loadData(url = null, updateBrowserUrl = true) {
        const requestUrl = url || buildFilterUrl();
        $wrapper.css({opacity:'0.55', pointerEvents:'none'});
        $.ajax({url:requestUrl, type:'GET', dataType:'json'}).done(function(res) {
            $wrapper.html(res.html);
            $('#checkAll').prop('checked', false);
            if (updateBrowserUrl && window.history && window.history.replaceState) window.history.replaceState({}, '', requestUrl);
        }).fail(function(xhr) {
            requestError(xhr, 'Failed to load pricing add-ons.');
        }).always(function() {
            $wrapper.css({opacity:'1', pointerEvents:'auto'});
        });
    }

    function refreshTable() {
        loadData(null, false);
    }

    function openModal(url, title) {
        destroyEditors();
        $('#modal-title').text(title);
        $modalBody.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><div class="text-muted small mt-2">Loading...</div></div>');
        $modal.modal({backdrop:'static', keyboard:false, show:true});
        $.get(url).done(function(res) {
            $modalBody.html(res.html);
            document.dispatchEvent(new CustomEvent('admin:content-updated'));
        }).fail(function(xhr) {
            $modalBody.html('<div class="alert alert-danger mb-0">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>');
        });
    }

    function confirmation(action, count = 1) {
        const total = Number(count) || 1;
        const bulk = total > 1;
        if (action === 'delete') return Swal.fire({icon:'warning', title:bulk ? 'Confirm Bulk Trash' : 'Move to Trash?', text:bulk ? 'Move ' + total + ' pricing add-on(s) to trash?' : 'You can restore this pricing add-on later.', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Move to trash', cancelButtonText:'Cancel'});
        if (action === 'restore') return Swal.fire({icon:'question', title:bulk ? 'Confirm Bulk Restore' : 'Restore Pricing Add-on?', text:bulk ? 'Restore ' + total + ' pricing add-on(s)?' : 'This pricing add-on will be restored.', showCancelButton:true, confirmButtonColor:'#28a745', confirmButtonText:'Restore', cancelButtonText:'Cancel'});
        if (action === 'force_delete') return Swal.fire({icon:'warning', title:bulk ? 'Confirm Permanent Delete' : 'Permanently Delete?', text:'This action cannot be undone.', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete permanently', cancelButtonText:'Cancel', focusCancel:true});
        return Promise.resolve({isConfirmed:true});
    }

    $('#btnAddRecord').on('click', function() {
        openModal($page.data('create-url'), 'Create Pricing Add-on');
    });

    $(document).on('click', '.btn-edit', function() {
        openModal($(this).data('url'), 'Edit Pricing Add-on');
    });

    $(document).on('click', '.btn-show', function() {
        openModal($(this).data('url'), 'Pricing Add-on Details');
    });

    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadData();
    });

    $(document).on('change', '#filter_type, #filter_status', function() {
        loadData();
    });

    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() { loadData(); }, 400);
    });

    $('#btnResetFilter').on('click', function() {
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_type').val('');
        $('#filter_status').val('');
        $('#table_search').val('');
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) loadData(url);
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

        $.ajax({url:$form.attr('action'), type:'POST', data:new FormData(form), processData:false, contentType:false}).done(function(res) {
            $modal.modal('hide');
            showToast('success', res.message);
            refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');
                    $form.find('[name="' + baseField + '"]').addClass('is-invalid');
                    $form.find('.error-' + errorClass + ', .error-' + baseField).first().text(messages[0]).show();
                });
                return;
            }
            requestError(xhr);
        }).always(function() {
            $button.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-toggle, .btn-duplicate', function() {
        $.post($(this).data('url'), {_token:'{{ csrf_token() }}'}).done(function(res) {
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
            $.ajax({url:url, type:'DELETE', data:{_token:'{{ csrf_token() }}'}}).done(function(res) {
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
            $.post(url, {_token:'{{ csrf_token() }}'}).done(function(res) {
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
            $.ajax({url:url, type:'DELETE', data:{_token:'{{ csrf_token() }}'}}).done(function(res) {
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
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!action) {
            Swal.fire('Notice', 'Please select a bulk action.', 'info');
            return;
        }
        if (!ids.length) {
            Swal.fire('Notice', 'Please select at least one pricing add-on.', 'info');
            return;
        }

        const submitBulk = function() {
            $.post($page.data('bulk-url'), {_token:'{{ csrf_token() }}', action:action, ids:ids}).done(function(res) {
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
                if (result.isConfirmed) submitBulk();
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
@endpush
