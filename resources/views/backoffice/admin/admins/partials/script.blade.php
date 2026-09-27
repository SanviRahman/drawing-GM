<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        const $page = $('#page-manager');
        const $wrapper = $('#content-wrapper');

        function loadData(url = $page.data('index-url')) {
            $wrapper.addClass('loading');

            let params = {
                search: $('#table_search').val(),
                role: $('#filter_role').length ? $('#filter_role').val() : ''
            };

            $.get(url, params, function(res) {
                $wrapper.html(res.html).removeClass('loading');
                $('#checkAll').prop('checked', false);
            }).fail(function() {
                $wrapper.removeClass('loading');
                Swal.fire('Error', 'Failed to load data.', 'error');
            });
        }

        $('#btnSearch, #filter_role').on('click change', function() { loadData(); });
        $('#table_search').on('keypress', function(e) { if (e.which === 13) loadData(); });

        $('#btnClearSearch').on('click', function() {
            $('#table_search').val('');
            loadData();
        });

        $('#btnResetFilter').on('click', function() {
            $('#table_search').val('');
            if ($('#filter_role').length) $('#filter_role').val('');
            loadData();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            loadData($(this).attr('href'));
        });

        function openModal(url, title) {
            $('#modal-title').text(title);
            $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted">Loading...</p></div>');
            $('#ajaxModal').modal('show');

            $.get(url, function(res) {
                $('#modal-body').html(res.html);
            }).fail(function() {
                $('#modal-body').html('<div class="alert alert-danger m-3">Failed to load content!</div>');
            });
        }

        $('#btnAddRecord').on('click', function() { openModal($page.data('create-url'), 'Create New Admin'); });
        $(document).on('click', '.btn-edit', function() { openModal($(this).data('url'), 'Edit Admin'); });
        $(document).on('click', '.btn-show', function() { openModal($(this).data('url'), 'Admin Details'); });

        $(document).on('submit', '#ajax-form', function(e) {
            e.preventDefault();

            let $form = $(this);
            let $btn = $form.find('button[type="submit"]');
            let formData = new FormData(this);

            $btn.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin ml-1"></i>');
            $('.invalid-feedback, .text-danger[class*="error-"]').text('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#ajaxModal').modal('hide');
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 });
                    loadData();
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).find('.fa-spinner').remove();

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(field, err) {
                            let baseField = field.split('.')[0];
                            let errorClass = field.replace(/\./g, '_');
                            $(`[name="${baseField}"], [name="${baseField}[]"]`).addClass('is-invalid');
                            $(`.error-${errorClass}, .error-${baseField}`).first().text(err[0]).show();
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    } else {
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                }
            });
        });

        $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function() {
            let url = $(this).data('url');
            let isForce = $(this).hasClass('btn-force-delete');
            let method = $(this).hasClass('btn-restore') ? 'POST' : 'DELETE';

            Swal.fire({
                title: isForce ? 'Permanently Delete?' : 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((res) => {
                if (res.value) {
                    $.ajax({
                        url: url,
                        type: method,
                        success: function(res) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 });
                            loadData();
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Action failed.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });

        $(document).on('click', '#checkAll', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $('#btnApplyBulk').on('click', function() {
            let action = $('#bulk_action').val();
            let ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();

            if (! action) return Swal.fire('Notice', 'Please select a bulk action.', 'info');
            if (ids.length === 0) return Swal.fire('Notice', 'Please select at least one row.', 'info');

            Swal.fire({
                title: 'Confirm Bulk Action',
                text: `Apply this action on ${ids.length} item(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Proceed'
            }).then((result) => {
                if (result.value) {
                    $.post($page.data('bulk-url'), { action: action, ids: ids }, function(res) {
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 });
                            $('#bulk_action').val('');
                            loadData();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }).fail(function(xhr) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Bulk action failed.';
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        });
    });
</script>
