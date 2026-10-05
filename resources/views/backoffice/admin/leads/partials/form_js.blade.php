<script>
(function () {
    const $modal = $('#ajaxModal');
    const $form = $('#ajax-form');

    $form.find('.select2').each(function () {
        const $select = $(this);

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            dropdownParent: $modal,
            placeholder: $select.data('placeholder') || undefined,
            closeOnSelect: true
        });
    });

    function syncServiceNotes() {
        const selected = ($('#lead_service_ids').val() || []).map(String);

        $('.service-note-panel').each(function () {
            const id = String($(this).data('service-id'));
            $(this).toggle(selected.includes(id));
        });

        document.dispatchEvent(new CustomEvent('admin:content-updated'));
    }

    $('#lead_service_ids').on('change', syncServiceNotes);
    syncServiceNotes();

    function selectedPickerIds() {
        return $('#lead-picker-media-inputs input[name="attachment_media_ids[]"]')
            .map(function () { return Number(this.value); })
            .get()
            .filter(Number.isFinite);
    }

    function uploadedFileCount() {
        const input = document.getElementById('lead_attachments');
        return input && input.files ? input.files.length : 0;
    }

    function appendPickerPreview(media) {
        if (!media || !media.id || !media.url) return;

        const id = Number(media.id);

        if (selectedPickerIds().includes(id)) {
            return;
        }

        $('#lead-picker-media-inputs').append(
            $('<input>', {
                type: 'hidden',
                name: 'attachment_media_ids[]',
                value: id,
                'data-media-id': id
            })
        );

        const $col = $('<div class="col-lg-3 col-md-4 col-sm-6 mb-3"></div>')
            .attr('data-picker-media-id', id);

        const $card = $('<div class="lead-picker-preview-card"></div>');
        const $img = $('<img class="border mb-2" alt="">').attr('src', media.url);
        const $name = $('<small class="d-block text-truncate pr-4"></small>')
            .attr('title', media.file_name || media.name || 'Selected media')
            .text(media.file_name || media.name || 'Selected media');

        const $remove = $('<button type="button" class="btn btn-danger btn-xs btn-remove-picker-image" title="Remove selected image"><i class="fas fa-times"></i></button>')
            .attr('data-media-id', id);

        $card.append($img, $name, $remove);
        $col.append($card);
        $('#picker-attachment-previews').append($col);
    }

    $('#btnChooseLeadAttachmentImages').on('click', function () {
        if (!window.MediaPicker || typeof window.MediaPicker.open !== 'function') {
            window.showAlert('Media Picker is not available on this page.', 'error');
            return;
        }

        const remaining = 10 - uploadedFileCount() - selectedPickerIds().length;

        if (remaining <= 0) {
            window.showAlert('A maximum of 10 new attachments can be added per request.', 'warning');
            return;
        }

        window.MediaPicker.open(function (selected) {
            const items = (Array.isArray(selected) ? selected : [selected]).filter(Boolean);
            const currentIds = selectedPickerIds();
            let slots = 10 - uploadedFileCount() - currentIds.length;

            items.forEach(function (media) {
                if (slots <= 0) return;
                if (!media || !media.id || !media.url) return;
                if (selectedPickerIds().includes(Number(media.id))) return;

                appendPickerPreview(media);
                slots -= 1;
            });

            if (items.length > remaining) {
                window.showAlert('Only the available attachment slots were added.', 'warning');
            }
        }, {
            type: 'image',
            multiple: true,
            max: remaining,
            title: 'Choose Lead Attachment Images'
        });
    });

    $('#picker-attachment-previews').on('click', '.btn-remove-picker-image', function () {
        const id = Number($(this).data('media-id'));

        $('#lead-picker-media-inputs input[data-media-id="' + id + '"]').remove();
        $('#picker-attachment-previews [data-picker-media-id="' + id + '"]').remove();
    });

    $('#lead_attachments').on('change', function (event) {
        const $preview = $('#new-attachment-previews').empty();
        const files = Array.from(event.target.files || []);

        if (files.length + selectedPickerIds().length > 10) {
            window.showAlert('Uploaded files plus Media Picker images cannot exceed 10 attachments per request.', 'warning');
        }

        files.forEach(function (file) {
            const $col = $('<div class="col-lg-3 col-md-4 col-sm-6 mb-2"></div>');
            const $card = $('<div class="border rounded p-2 h-100"></div>');

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    $card.prepend(
                        $('<img class="img-fluid rounded border mb-2" style="width:100%;height:100px;object-fit:cover;" alt="">')
                            .attr('src', e.target.result)
                    );
                };

                reader.readAsDataURL(file);
            } else {
                $card.append(
                    '<div class="text-center py-3 bg-light rounded mb-2"><i class="fas fa-file fa-2x text-muted"></i></div>'
                );
            }

            $card.append(
                $('<small class="d-block text-truncate"></small>')
                    .attr('title', file.name)
                    .text(file.name)
            );

            $col.append($card);
            $preview.append($col);
        });
    });

    document.dispatchEvent(new CustomEvent('admin:content-updated'));
})();
</script>