<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2">
                <h5 class="modal-title font-weight-bold text-primary mb-0" id="modal-title">FAQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>

@push('css')
<style>
    #content-wrapper.loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .2s ease-in-out;
    }

    .faq-rich-content img {
        max-width: 100%;
        height: auto;
    }

    #ajaxModal .modal-content {
        max-height: calc(100vh - 30px);
    }

    #ajaxModal .modal-body {
        overflow-y: auto;
    }

    #ajaxModal .faq-mapping-card {
        border: 1px solid #e3e6f0;
        border-radius: .4rem;
        background: #f8f9fc;
        padding: 1rem;
    }

    #ajaxModal .faq-select2 + .select2-container {
        width: 100% !important;
    }

    /* Keep multi-select controls the same height before and after selection. */
    #ajaxModal .select2-container--default .select2-selection--multiple {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        height: 38px !important;
        min-height: 38px !important;
        max-height: 38px !important;
        padding: 1px 30px 1px 5px !important;
        overflow: hidden;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        background-color: #fff;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    #ajaxModal .select2-container--default.select2-container--focus .select2-selection--multiple,
    #ajaxModal .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .15);
    }

    #ajaxModal .select2-container--default .select2-selection--multiple::after {
        content: '\f107';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        right: 10px;
        z-index: 2;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    /* AdminLTE + Select2 may force the inline search to 100% width.
       Keeping the rendered list on one row prevents the field from growing. */
    #ajaxModal .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex: 1 1 auto;
        flex-wrap: nowrap !important;
        align-items: center;
        width: 100%;
        min-width: 0;
        height: 34px;
        max-height: 34px;
        padding: 0 !important;
        margin: 0 !important;
        overflow-x: auto;
        overflow-y: hidden;
        list-style: none;
        white-space: nowrap;
        scrollbar-width: none;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-selection__rendered::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-selection__choice {
        flex: 0 0 auto;
        max-width: 180px;
        margin: 2px 4px 2px 0 !important;
        overflow: hidden;
        border: 1px solid #b8daff;
        border-radius: .25rem;
        background: #e7f3ff;
        color: #0c5460;
        line-height: 26px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #495057;
        border-right-color: #b8daff;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #dc3545;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-search--inline {
        display: block !important;
        flex: 1 1 70px !important;
        width: auto !important;
        min-width: 70px !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #ajaxModal .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        box-sizing: border-box;
        width: 100% !important;
        min-width: 60px !important;
        height: 30px !important;
        margin: 0 !important;
        padding: 0 4px !important;
        border: 0 !important;
        line-height: 30px !important;
        color: #495057;
        outline: 0;
    }

    #ajaxModal .faq-select2.is-invalid + .select2-container .select2-selection--multiple {
        border-color: #dc3545 !important;
        box-shadow: none;
    }

    .select2-container--open {
        z-index: 1065;
    }

    .select2-dropdown {
        border-color: #80bdff;
        box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .12);
    }

    @media (max-width: 991.98px) {
        #ajaxModal .faq-mapping-card {
            padding: .85rem;
        }
    }
</style>
@endpush

@push('js')
<script>
$(function() {
    const $page = $('#faq-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    $(document).on('focusin.faqTinymce', function(e) {
        if ($(e.target).closest('.tox-tinymce, .tox-tinymce-aux, .tox-dialog-wrap').length) {
            e.stopImmediatePropagation();
        }
    });

    function destroyModalComponents() {
        if (window.tinymce) {
            $modalBody.find('textarea.tinymce-editor').each(function() {
                const editor = this.id ? window.tinymce.get(this.id) : null;
                if (editor) editor.remove();
                delete this.dataset.tinymceInitialized;
            });
        }

        if ($.fn.select2) {
            $modalBody.find('.faq-select2.select2-hidden-accessible').each(function() {
                $(this).select2('destroy');
            });
        }
    }

    function initModalComponents() {
        if ($.fn.select2) {
            $modalBody.find('.faq-select2').each(function() {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    dropdownParent: $modal,
                    placeholder: $select.data('placeholder') || 'Select options',
                    allowClear: false,
                    closeOnSelect: false
                });
            });
        }

        document.dispatchEvent(new CustomEvent('admin:content-updated'));
    }

    $(document).on('select2:select select2:unselect select2:clear', '.faq-select2', function() {
        const $select = $(this);
        const fieldName = String($select.attr('name') || '').replace('[]', '');

        $select.removeClass('is-invalid');
        if (fieldName) {
            $select.closest('[class*="col-"]').find('.error-' + fieldName).text('').hide();
        }
    });

    function showToast(icon, message, timer = 2500) {
        Swal.fire({toast:true, position:'top-end', icon:icon, title:message, showConfirmButton:false, timer:timer, timerProgressBar:true});
    }

    function requestError(xhr, fallback = 'Request failed.') {
        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : fallback;
        Swal.fire({icon:'error', title:'Error', text:message});
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

        $.ajax({url:requestUrl, type:'GET', dataType:'json'}).done(function(res) {
            $wrapper.html(res.html);
            $('#checkAll').prop('checked', false);
            if (updateBrowserUrl && window.history && window.history.replaceState) {
                window.history.replaceState({}, '', requestUrl);
            }
        }).fail(function(xhr) {
            requestError(xhr, 'Failed to load FAQs.');
        }).always(function() {
            $wrapper.removeClass('loading');
        });
    }

    function refreshTable() {
        loadData(null, false);
    }

    function openModal(url, title) {
        destroyModalComponents();
        $('#modal-title').text(title);
        $modalBody.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-2"></i><div class="text-muted">Loading...</div></div>');
        $modal.modal({backdrop:'static', keyboard:false, show:true});

        $.get(url).done(function(res) {
            $modalBody.html(res.html);
            initModalComponents();
        }).fail(function(xhr) {
            $modalBody.html('<div class="alert alert-danger mb-0">' + (xhr.responseJSON?.message || 'Failed to load content.') + '</div>');
        });
    }

    function confirmation(action, count = 1) {
        const total = Number(count) || 1;
        const bulk = total > 1;

        if (action === 'delete') {
            return Swal.fire({icon:'warning', title:bulk ? 'Confirm Bulk Trash' : 'Move FAQ to Trash?', text:bulk ? 'Move ' + total + ' FAQ(s) to trash?' : 'Mappings are preserved and return after restore.', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Move to trash'});
        }
        if (action === 'restore') {
            return Swal.fire({icon:'question', title:bulk ? 'Confirm Bulk Restore' : 'Restore FAQ?', text:bulk ? 'Restore ' + total + ' FAQ(s)?' : 'The FAQ and its mappings will become available again.', showCancelButton:true, confirmButtonColor:'#28a745', confirmButtonText:'Restore'});
        }
        if (action === 'force_delete') {
            return Swal.fire({icon:'warning', title:bulk ? 'Confirm Permanent Delete' : 'Permanently Delete FAQ?', text:'This removes the FAQ and its faqable mappings permanently.', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete permanently', focusCancel:true});
        }
        return Promise.resolve({isConfirmed:true});
    }

    $('#btnAddRecord').on('click', function() {
        const navId = $('#filter_menu_item_id').val();
        const pageId = $('#filter_page_id').val();
        const params = new URLSearchParams();
        if (navId) params.set('menu_item_id', navId);
        if (pageId) params.set('page_id', pageId);
        const url = $page.data('create-url') + (params.toString() ? '?' + params.toString() : '');
        openModal(url, 'Create New FAQ');
    });

    $(document).on('click', '.btn-edit', function() {
        openModal($(this).data('url'), 'Edit FAQ');
    });

    $(document).on('click', '.btn-show', function() {
        openModal($(this).data('url'), 'FAQ Details');
    });

    function setSelectedTarget(type, id, label) {
        $('#filter_menu_item_id').val(type === 'nav' ? id : '');
        $('#filter_page_id').val(type === 'page' ? id : '');
        $('#faq-target-selection-label').text(label || 'Selected');
        $('#faq-target-selection').prop('hidden', false);
        $('#faq-nav-sections .btn-nav-filter, #faq-page-sections .btn-page-filter')
            .removeClass('btn-primary').addClass('btn-outline-primary');
        $('#faq-nav-sections .faq-nav-card, #faq-page-sections .faq-page-card')
            .removeClass('border-primary bg-light');
        loadData();
    }

    $(document).on('click', '.btn-nav-filter', function() {
        setSelectedTarget('nav', String($(this).data('id')), $(this).closest('.faq-nav-card').find('strong').first().text());
        $(this).closest('.faq-nav-card').addClass('border-primary bg-light');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });

    $(document).on('click', '.btn-page-filter', function() {
        setSelectedTarget('page', String($(this).data('id')), String($(this).data('label')));
        $(this).closest('.faq-page-card').addClass('border-primary bg-light');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });

    function clearTargetSelection() {
        $('#filter_menu_item_id, #filter_page_id').val('');
        $('#faq-target-selection').prop('hidden', true);
        $('#faq-nav-sections .btn-nav-filter, #faq-page-sections .btn-page-filter')
            .removeClass('btn-primary').addClass('btn-outline-primary');
        $('#faq-nav-sections .faq-nav-card, #faq-page-sections .faq-page-card')
            .removeClass('border-primary bg-light');
    }

    $(document).on('click', '#btnClearTarget', function() {
        clearTargetSelection();
        loadData();
    });

    $(document).on('click', '.btn-faq-section-toggle', function() {
        const $btn = $(this);
        $btn.prop('disabled', true);
        $.post($btn.data('url'), {_token:'{{ csrf_token() }}'}).done(function(res) {
            showToast('success', res.message);
            $btn.text(res.enabled ? 'Section ON' : 'Section OFF')
                .toggleClass('btn-outline-success', !!res.enabled)
                .toggleClass('btn-outline-secondary', !res.enabled);
        }).fail(function(xhr) { requestError(xhr); }).always(function() { $btn.prop('disabled', false); });
    });

    $(document).on('submit', '#filterForm', function(e) {
        e.preventDefault();
        loadData();
    });

    $(document).on('change', '#filter_status, #filter_target_type', function() {
        loadData();
    });

    $(document).on('input', '#table_search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() { loadData(); }, 400);
    });

    $('#btnResetFilter').on('click', function() {
        const form = $('#filterForm')[0];
        if (form) form.reset();
        $('#filter_status, #filter_target_type, #table_search').val('');
        clearTargetSelection();
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

        $.ajax({
            url:$form.attr('action'),
            type:'POST',
            data:new FormData(form),
            processData:false,
            contentType:false,
            dataType:'json'
        }).done(function(res) {
            $modal.modal('hide');
            showToast('success', res.message || 'FAQ saved successfully.');
            refreshTable();
        }).fail(function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                    const baseField = field.split('.')[0];
                    const errorClass = field.replace(/\./g, '_');
                    $form.find('[name="' + baseField + '"], [name="' + baseField + '[]"]').addClass('is-invalid');
                    $form.find('.error-' + errorClass + ', .error-' + baseField).first().text(messages[0]).show();
                });
                return;
            }
            requestError(xhr);
        }).always(function() {
            $button.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-toggle', function() {
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
            if (!(result.isConfirmed || result.value)) return;
            $.ajax({url:url, type:'DELETE', data:{_token:'{{ csrf_token() }}'}}).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) { requestError(xhr); });
        });
    });

    $(document).on('click', '.btn-restore', function() {
        const url = $(this).data('url');
        confirmation('restore').then(function(result) {
            if (!(result.isConfirmed || result.value)) return;
            $.post(url, {_token:'{{ csrf_token() }}'}).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) { requestError(xhr); });
        });
    });

    $(document).on('click', '.btn-force-delete', function() {
        const url = $(this).data('url');
        confirmation('force_delete').then(function(result) {
            if (!(result.isConfirmed || result.value)) return;
            $.ajax({url:url, type:'DELETE', data:{_token:'{{ csrf_token() }}'}}).done(function(res) {
                showToast('success', res.message);
                refreshTable();
            }).fail(function(xhr) { requestError(xhr); });
        });
    });

    $(document).on('change', '#checkAll', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });

    $('#btnApplyBulk').on('click', function() {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function() { return this.value; }).get();

        if (!action) return Swal.fire('Notice', 'Please select a bulk action.', 'info');
        if (!ids.length) return Swal.fire('Notice', 'Please select at least one FAQ.', 'info');

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
                if (result.isConfirmed || result.value) submitBulk();
            });
            return;
        }

        submitBulk();
    });

    $modal.on('hidden.bs.modal', function() {
        destroyModalComponents();
        $modalBody.empty();
    });
});
</script>
@endpush
