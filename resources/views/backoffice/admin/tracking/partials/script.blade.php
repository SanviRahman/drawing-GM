@push('js')
<script>
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const $page = $('#tracking-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $body = $('#modal-body');

    let timer = null;
    let pixelIndex = 1000;

    const toast = (message, type = 'success') => window.showAlert
        ? window.showAlert(message, type)
        : Promise.resolve();

    const confirmBox = (title, text) => window.showConfirm
        ? window.showConfirm({ title, text })
        : Promise.resolve({ isConfirmed: window.confirm(text) });

    const fail = (xhr, message = 'Request failed.') =>
        toast(xhr.responseJSON?.message || message, 'error');

    function filterUrl() {
        const $form = $('#filterForm');
        const query = $form.serialize();

        return query
            ? $form.attr('action') + '?' + query
            : $form.attr('action');
    }

    function loadData(url = null, push = true) {
        const requestUrl = url || filterUrl();

        $wrapper.addClass('loading');

        $.get(requestUrl)
            .done(function (response) {
                $wrapper.html(response.html);
                $('#checkAll').prop('checked', false);

                if (push && history.replaceState) {
                    history.replaceState({}, '', requestUrl);
                }
            })
            .fail(function (xhr) {
                fail(xhr, 'Failed to load tracking providers.');
            })
            .always(function () {
                $wrapper.removeClass('loading');
            });
    }

    function initializeProviderForm() {
        toggleProviderFields();
        document.dispatchEvent(new CustomEvent('admin:content-updated'));
    }

    function openModal(url, title) {
        $('#modal-title').text(title);
        $body.html(
            '<div class="text-center py-5">' +
            '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>' +
            '</div>'
        );

        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });

        $.get(url)
            .done(function (response) {
                $body.html(response.html);
                initializeProviderForm();
            })
            .fail(function (xhr) {
                $body.html(
                    '<div class="alert alert-danger">' +
                    (xhr.responseJSON?.message || 'Failed to load.') +
                    '</div>'
                );
            });
    }

    function toggleProviderFields() {
        const isMetaPixel = $('#tracking_provider_key').val() === 'meta_pixel';
        $('#metaPixelConfig').toggleClass('d-none', !isMetaPixel);
    }

    $(document).on('change', '#tracking_provider_key', toggleProviderFields);

    $(document).on('click', '#btnAddMetaPixel', function () {
        const template = document.getElementById('metaPixelRowTemplate');

        if (!template) {
            return;
        }

        const currentIndex = pixelIndex++;
        const $row = $(template.content.cloneNode(true)).find('.meta-pixel-row');

        $row.attr('data-row-index', currentIndex);

        $row.find('[data-field]').each(function () {
            const field = $(this).data('field');
            $(this).attr('name', 'meta_pixels[' + currentIndex + '][' + field + ']');
        });

        $('#metaPixelRows').append($row);
    });

    $(document).on('click', '.btn-remove-meta-pixel', function () {
        $(this).closest('.meta-pixel-row').remove();
    });

    $('#btnAddRecord').on('click', function () {
        openModal($page.data('create-url'), 'Create Tracking Provider');
    });

    $(document).on('click', '.btn-edit', function () {
        openModal($(this).data('url'), 'Edit Tracking Provider');
    });

    $(document).on('click', '.btn-show', function () {
        openModal($(this).data('url'), 'Tracking Provider Details');
    });

    $(document).on('submit', '#filterForm', function (event) {
        event.preventDefault();
        loadData();
    });

    $(document).on('change', '#filter_provider,#filter_status,#filter_mode', function () {
        loadData();
    });

    $(document).on('input', '#table_search', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            loadData();
        }, 400);
    });

    $('#btnResetFilter').on('click', function () {
        document.getElementById('filterForm')?.reset();
        loadData();
    });

    $(document).on('click', '#content-wrapper .pagination a', function (event) {
        event.preventDefault();
        loadData($(this).attr('href'));
    });

    function clearValidationErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.field-error').text('');
        $form.find('[class*="error-"]').text('');
        $('#metaPixelGeneralError').addClass('d-none').text('');
    }

    function laravelKeyToInputName(key) {
        const parts = String(key).split('.');
        const root = parts.shift();

        return root + parts.map(function (part) {
            return '[' + part + ']';
        }).join('');
    }

    function showValidationError($form, key, message) {
        if (key === 'meta_pixels') {
            $('#metaPixelGeneralError')
                .removeClass('d-none')
                .text(message);
            return;
        }

        const inputName = laravelKeyToInputName(key);
        const $field = $form.find('[name="' + inputName + '"]').first();

        if ($field.length) {
            $field.addClass('is-invalid');

            const $feedback = $field.siblings('.field-error').first();
            if ($feedback.length) {
                $feedback.text(message).show();
            } else {
                const base = key.split('.')[0];
                $form.find('.error-' + key.replace(/\./g, '_') + ',.error-' + base)
                    .first()
                    .text(message)
                    .show();
            }

            const fieldTop = $field.offset()?.top;
            const modalTop = $modal.find('.modal-body').offset()?.top;

            if (fieldTop && modalTop && fieldTop < modalTop) {
                $field[0]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return;
        }

        const base = key.split('.')[0];
        $form.find('.error-' + key.replace(/\./g, '_') + ',.error-' + base)
            .first()
            .text(message)
            .show();
    }

    $(document).on('submit', '#ajax-form', function (event) {
        event.preventDefault();

        const form = this;
        const $form = $(form);
        const $button = $form.find('button[type="submit"]');

        clearValidationErrors($form);
        $button.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false
        })
        .done(function (response) {
            $modal.modal('hide');
            toast(response.message);
            loadData(null, false);
        })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;
                const firstMessage = Object.values(errors)?.[0]?.[0] || 'Please correct the highlighted fields.';

                Object.entries(errors).forEach(function ([key, messages]) {
                    showValidationError($form, key, messages[0]);
                });

                // Show the real server message instead of only a generic toast.
                toast(firstMessage, 'error');
                return;
            }

            fail(xhr);
        })
        .always(function () {
            $button.prop('disabled', false);
        });
    });

    $(document).on('change', '#checkAll', function () {
        $('.row-checkbox').prop('checked', this.checked);
    });

    $(document).on('click', '.btn-toggle', function () {
        $.post($(this).data('url'), {
            _token: '{{ csrf_token() }}'
        })
        .done(function (response) {
            toast(response.message);
            loadData(null, false);
        })
        .fail(fail);
    });

    $(document).on('click', '.btn-test', function () {
        $.post($(this).data('url'), {
            _token: '{{ csrf_token() }}'
        })
        .done(function (response) {
            toast(response.message, 'success');
        })
        .fail(fail);
    });

    function rowAction(selector, method, title, text) {
        $(document).on('click', selector, function () {
            const url = $(this).data('url');

            confirmBox(title, text).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                })
                .done(function (response) {
                    toast(response.message);
                    loadData(null, false);
                })
                .fail(fail);
            });
        });
    }

    rowAction(
        '.btn-delete',
        'DELETE',
        'Move Provider to Trash?',
        'The provider will be disabled and can be restored later.'
    );

    rowAction(
        '.btn-restore',
        'POST',
        'Restore Provider?',
        'The provider will be restored as disabled.'
    );

    rowAction(
        '.btn-force-delete',
        'DELETE',
        'Permanently Delete Provider?',
        'All event rules must be permanently removed first.'
    );

    $('#btnApplyBulk').on('click', function () {
        const action = $('#bulk_action').val();
        const ids = $('.row-checkbox:checked').map(function () {
            return this.value;
        }).get();

        if (!action) {
            return toast('Please select a bulk action.', 'warning');
        }

        if (!ids.length) {
            return toast('Please select at least one provider.', 'warning');
        }

        confirmBox(
            'Confirm Bulk Action',
            'Apply ' + action + ' to ' + ids.length + ' provider(s)?'
        ).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.post($page.data('bulk-url'), {
                _token: '{{ csrf_token() }}',
                action,
                ids
            })
            .done(function (response) {
                toast(response.message);
                $('#bulk_action').val('');
                loadData(null, false);
            })
            .fail(fail);
        });
    });
});
</script>
@endpush
