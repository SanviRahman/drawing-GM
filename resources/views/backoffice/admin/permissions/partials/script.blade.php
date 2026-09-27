<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const $page = $('#permission-page');
        const $wrapper = $('#content-wrapper');

        function toastSuccess(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        }

        function errorMessage(xhr, fallback = 'An unexpected error occurred.') {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                return xhr.responseJSON.message;
            }

            return fallback;
        }

        function loadData(url = $page.data('index-url')) {
            if (!$wrapper.length) {
                return;
            }

            $wrapper.addClass('loading');

            const params = {
                search: $('#table_search').val() || '',
                guard_name: $('#filter_guard').length ? ($('#filter_guard').val() || '') : '',
                group_name: $('#filter_group').length ? ($('#filter_group').val() || '') : ''
            };

            $.get(url, params)
                .done(function (res) {
                    if (!res || typeof res.html === 'undefined') {
                        Swal.fire('Error', 'Invalid server response.', 'error');
                        return;
                    }

                    $wrapper.html(res.html);
                    $('#checkAll').prop('checked', false);
                })
                .fail(function (xhr) {
                    Swal.fire(
                        'Error',
                        errorMessage(xhr, 'Failed to load data.'),
                        'error'
                    );
                })
                .always(function () {
                    $wrapper.removeClass('loading');
                });
        }

        $(document).on('click', '#btnSearch', function () {
            loadData();
        });

        $(document).on('change', '#filter_guard, #filter_group', function () {
            loadData();
        });

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

            if ($('#filter_guard').length) {
                $('#filter_guard').val('');
            }

            if ($('#filter_group').length) {
                $('#filter_group').val('');
            }

            loadData();
        });

        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();

            const url = $(this).attr('href');

            if (url) {
                loadData(url);
            }
        });

        function openModal(url, title) {
            if (!url) {
                return;
            }

            $('#modal-title').text(title);
            $('#modal-body').html(
                '<div class="text-center py-5">' +
                    '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +
                    '<p class="font-weight-bold text-muted">Loading...</p>' +
                '</div>'
            );
            $('#ajaxModal').modal('show');

            $.get(url)
                .done(function (res) {
                    $('#modal-body').html(res.html);
                })
                .fail(function (xhr) {
                    $('#modal-body').html(
                        '<div class="alert alert-danger m-3">' +
                            errorMessage(xhr, 'Failed to load content!') +
                        '</div>'
                    );
                });
        }

        $(document).on('click', '#btnAddPermission', function () {
            openModal(
                $page.data('create-url'),
                'Create New Permission'
            );
        });

        $(document).on('click', '.btn-edit', function () {
            openModal(
                $(this).data('url'),
                'Edit Permission'
            );
        });

        $(document).on('click', '.btn-show', function () {
            openModal(
                $(this).data('url'),
                'Permission Details'
            );
        });

        $(document).on('submit', '#permission-form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const formData = new FormData(this);

            $btn
                .prop('disabled', true)
                .append(' <i class="fas fa-spinner fa-spin ml-1 submit-spinner"></i>');

            $form.find('.invalid-feedback').text('').hide();
            $form.find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            })
                .done(function (res) {
                    $('#ajaxModal').modal('hide');
                    toastSuccess(res.message || 'Saved successfully.');
                    loadData();
                })
                .fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function (field, errors) {
                            const normalizedField = field.replace(/\./g, '_');
                            const $input = $form.find(`[name="${field}"]`);
                            const $target = $form.find('.error-' + normalizedField);

                            $input.addClass('is-invalid');

                            if ($target.length) {
                                $target.text(errors[0]).show();
                            }
                        });

                        return;
                    }

                    Swal.fire(
                        'Error',
                        errorMessage(xhr),
                        'error'
                    );
                })
                .always(function () {
                    $btn.prop('disabled', false);
                    $btn.find('.submit-spinner').remove();
                });
        });

        $(document).on('click', '.btn-delete, .btn-restore, .btn-force-delete', function () {
            const $button = $(this);
            const url = $button.data('url');

            if (!url) {
                return;
            }

            const isRestore = $button.hasClass('btn-restore');
            const isForce = $button.hasClass('btn-force-delete');
            const method = isRestore ? 'POST' : 'DELETE';

            Swal.fire({
                title: isRestore
                    ? 'Restore Permission?'
                    : (isForce ? 'Permanently Delete?' : 'Move to Trash?'),
                text: isRestore
                    ? 'This permission will be restored.'
                    : 'This action cannot be undone!',
                icon: isRestore ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: isRestore ? '#28a745' : '#d33',
                confirmButtonText: isRestore ? 'Yes, restore!' : 'Yes, proceed!'
            }).then(function (result) {
                if (!result.value) {
                    return;
                }

                $button.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method
                })
                    .done(function (res) {
                        if (res.success === false) {
                            Swal.fire(
                                'Error',
                                res.message || 'Action failed.',
                                'error'
                            );
                            return;
                        }

                        toastSuccess(res.message || 'Action completed successfully.');
                        loadData();
                    })
                    .fail(function (xhr) {
                        Swal.fire(
                            'Error',
                            errorMessage(xhr, 'Action failed.'),
                            'error'
                        );
                    })
                    .always(function () {
                        $button.prop('disabled', false);
                    });
            });
        });

        $(document).on('click', '#checkAll', function () {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $(document).on('change', '.row-checkbox', function () {
            const total = $('.row-checkbox').length;
            const checked = $('.row-checkbox:checked').length;

            $('#checkAll').prop(
                'checked',
                total > 0 && total === checked
            );
        });

        $(document).on('click', '#btnApplyBulk', function () {
            const $button = $(this);
            const action = $('#bulk_action').val();
            const ids = $('.row-checkbox:checked')
                .map(function () {
                    return $(this).val();
                })
                .get();

            if (!action) {
                Swal.fire(
                    'Notice',
                    'Please select an action.',
                    'info'
                );
                return;
            }

            if (ids.length === 0) {
                Swal.fire(
                    'Notice',
                    'Please select at least one row.',
                    'info'
                );
                return;
            }

            const isRestore = action === 'restore';
            const isForce = action === 'force_delete';

            Swal.fire({
                title: 'Confirm Bulk Action',
                text: `Apply this action on ${ids.length} item(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isRestore ? '#28a745' : (isForce ? '#d33' : '#3085d6'),
                confirmButtonText: 'Yes, Proceed'
            }).then(function (result) {
                if (!result.value) {
                    return;
                }

                $button.prop('disabled', true);

                $.post(
                    $page.data('bulk-url'),
                    {
                        action: action,
                        ids: ids
                    }
                )
                    .done(function (res) {
                        if (res.success === false) {
                            Swal.fire(
                                'Error',
                                res.message || 'Bulk action failed.',
                                'error'
                            );
                            return;
                        }

                        toastSuccess(res.message || 'Bulk action completed successfully.');
                        $('#bulk_action').val('');
                        loadData();
                    })
                    .fail(function (xhr) {
                        Swal.fire(
                            'Error',
                            errorMessage(xhr, 'Bulk action failed.'),
                            'error'
                        );
                    })
                    .always(function () {
                        $button.prop('disabled', false);
                    });
            });
        });
    });
</script>
