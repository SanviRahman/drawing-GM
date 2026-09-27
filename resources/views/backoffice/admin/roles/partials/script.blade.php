<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        const $page = $('#page-manager');
        const $wrapper = $('#content-wrapper');
        const selectedPermissions = new Set();

        function loadData(url = $page.data('index-url')) {
            $wrapper.addClass('loading');

            let params = {
                search: $('#table_search').val(),
                guard_name: $('#filter_guard').length ? $('#filter_guard').val() : ''
            };

            $.get(url, params, function (res) {
                $wrapper.html(res.html).removeClass('loading');
                $('#checkAll').prop('checked', false);
            }).fail(function () {
                $wrapper.removeClass('loading');
                Swal.fire('Error', 'Failed to load data.', 'error');
            });
        }

        function initializeSelectedPermissions() {
            selectedPermissions.clear();

            $('.permission-checkbox:checked').each(function () {
                selectedPermissions.add($(this).val());
            });
        }

        function syncVisiblePermissionState() {
            $('.permission-checkbox').each(function () {
                const name = $(this).val();

                if (this.checked) {
                    selectedPermissions.add(name);
                } else {
                    selectedPermissions.delete(name);
                }
            });
        }

        $(document).on('click', '#btnSearch', function () { loadData(); });
        $(document).on('change', '#filter_guard', function () { loadData(); });
        $(document).on('keypress', '#table_search', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                loadData();
            }
        });

        $(document).on('click', '#btnClearSearch', function () {
            $('#table_search').val('');
            loadData();
        });

        $(document).on('click', '#btnResetFilter', function () {
            $('#table_search').val('');
            if ($('#filter_guard').length) $('#filter_guard').val('');
            loadData();
        });

        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            loadData($(this).attr('href'));
        });

        function openModal(url, title) {
            selectedPermissions.clear();
            $('#modal-title').text(title);
            $('#modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="font-weight-bold text-muted">Loading...</p></div>');
            $('#ajaxModal').modal('show');

            $.get(url, function (res) {
                $('#modal-body').html(res.html);
                initializeSelectedPermissions();
            }).fail(function () {
                $('#modal-body').html('<div class="alert alert-danger m-3">Failed to load content!</div>');
            });
        }

        $(document).on('click', '#btnAddRecord', function () {
            let createUrl = $page.data('create-url');
            if (createUrl) openModal(createUrl, 'Create New Role');
        });

        $(document).on('click', '.btn-edit', function () {
            let url = $(this).data('url');
            if (url) openModal(url, 'Edit Role');
        });

        $(document).on('click', '.btn-show', function () {
            let url = $(this).data('url');
            if (url) openModal(url, 'Role Details');
        });

        function fetchPermissions() {
            let guardName = $('#guard_name').val();
            let search = $('#permission_search').val();
            let roleId = $('#ajax-form').data('role-id');
            let url = "{{ route('admin.roles.get_permissions') }}";

            $('#permissions-container').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');

            $.get(url, {
                guard_name: guardName,
                search: search,
                role_id: roleId,
                selected_permissions: Array.from(selectedPermissions)
            }, function (res) {
                if (res.success) {
                    $('#permissions-container').html(res.html);
                }
            }).fail(function () {
                $('#permissions-container').html('<div class="alert alert-danger mb-0">Failed to load permissions.</div>');
            });
        }

        $(document).on('change', '#guard_name', function () {
            selectedPermissions.clear();
            $('#permission_search').val('');
            fetchPermissions();
        });

        let searchTimer;
        $(document).on('keyup', '#permission_search', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchPermissions, 300);
        });

        $(document).on('change', '.permission-checkbox', function () {
            const name = $(this).val();

            if (this.checked) {
                selectedPermissions.add(name);
            } else {
                selectedPermissions.delete(name);
            }

            let $card = $(this).closest('.permission-group-card');
            let total = $card.find('.permission-checkbox').length;
            let checked = $card.find('.permission-checkbox:checked').length;

            $card.find('.group-select-all').prop('checked', total > 0 && total === checked);
        });

        $(document).on('change', '.group-select-all', function () {
            let $checkboxes = $(this).closest('.card').find('.permission-checkbox');

            $checkboxes.prop('checked', this.checked).each(function () {
                const name = $(this).val();

                if (this.checked) {
                    selectedPermissions.add(name);
                } else {
                    selectedPermissions.delete(name);
                }
            });
        });

        $(document).on('click', '#btnSelectAll', function () {
            $('.permission-checkbox:visible').prop('checked', true).each(function () {
                selectedPermissions.add($(this).val());
            });

            $('.group-select-all').prop('checked', true);
        });

        $(document).on('click', '#btnDeselectAll', function () {
            $('.permission-checkbox:visible').prop('checked', false).each(function () {
                selectedPermissions.delete($(this).val());
            });

            $('.permission-group-card').each(function () {
                let total = $(this).find('.permission-checkbox').length;
                let checked = $(this).find('.permission-checkbox:checked').length;
                $(this).find('.group-select-all').prop('checked', total > 0 && total === checked);
            });
        });

        $(document).on('submit', '#ajax-form', function (e) {
            e.preventDefault();

            let $form = $(this);
            let $btn = $form.find('button[type="submit"]');

            syncVisiblePermissionState();

            let formData = new FormData(this);
            formData.delete('permissions[]');

            selectedPermissions.forEach(function (permission) {
                formData.append('permissions[]', permission);
            });

            $btn.prop('disabled', true).append(' <i class="fas fa-spinner fa-spin ml-1"></i>');
            $('.invalid-feedback').text('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#ajaxModal').modal('hide');
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 });
                    loadData();
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).find('.fa-spinner').remove();

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function (field, err) {
                            let fieldName = field.replace(/\./g, '_');

                            if (field.startsWith('permissions')) {
                                $('.error-permissions').text(err[0]).show();
                                return;
                            }

                            $(`[name="${field}"]`).addClass('is-invalid');
                            $(`.error-${fieldName}`).text(err[0]).show();
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    } else {
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                }
            });
        });

        $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function () {
            let url = $(this).data('url');
            if (!url) return;

            let isRestore = $(this).hasClass('btn-restore');
            let isForce = $(this).hasClass('btn-force-delete');
            let method = isRestore ? 'POST' : 'DELETE';

            Swal.fire({
                title: isRestore ? 'Restore Role?' : (isForce ? 'Permanently Delete?' : 'Are you sure?'),
                text: isRestore ? 'This role will be moved back to the active roles list.' : 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isRestore ? '#28a745' : '#d33',
                confirmButtonText: isRestore ? 'Yes, restore!' : 'Yes, proceed!'
            }).then((res) => {
                if (res.value) {
                    $.ajax({
                        url: url,
                        type: method,
                        success: function (response) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.message, showConfirmButton: false, timer: 3000 });
                            loadData();
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Action failed.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });

        $(document).on('click', '#checkAll', function () {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $(document).on('click', '#btnApplyBulk', function () {
            let action = $('#bulk_action').val();
            let ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();

            if (!action) return Swal.fire('Notice', 'Please select a bulk action.', 'info');
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
                    $.post($page.data('bulk-url'), { action: action, ids: ids }, function (res) {
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 3000 });
                            $('#bulk_action').val('');
                            loadData();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }).fail(function (xhr) {
                        let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Bulk action failed.';
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        });
    });
</script>
