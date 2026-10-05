<script>
(function () {
    const isEdit = @json($isEdit);
    let slugTouched = isEdit;

    function makeSlug(value) {
        return String(value || '').toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function getJsonIds(id) {
        try {
            const values = JSON.parse($('#' + id).val() || '[]');
            return Array.isArray(values) ? values.map(Number).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    function setJsonIds(id, ids) {
        $('#' + id).val(JSON.stringify(Array.from(new Set(ids.map(Number).filter(Boolean)))));
    }

    function toggleContentEmpty() {
        const hasItems = $('#content_images_preview .post-content-preview-item').length > 0;
        $('#content_images_empty').toggleClass('d-none', hasItems);
    }

    function previewItem(source, key, url, label) {
        const $item = $('<div>')
            .addClass('border rounded bg-light p-1 text-center position-relative post-content-preview-item')
            .attr({'data-source': source, 'data-key': key})
            .css('width', '122px');

        const $remove = $('<button>')
            .attr({type:'button', title:'Remove image'})
            .addClass('btn btn-danger btn-sm btn-remove-content-image position-absolute')
            .attr({'data-source': source, 'data-key': key})
            .css({right:'3px', top:'3px', padding:'1px 5px', zIndex:2})
            .html('<i class="fas fa-times"></i>');

        const $img = $('<img>')
            .attr({src:url, alt:label || 'Content image'})
            .addClass('rounded')
            .css({width:'110px', height:'78px', objectFit:'cover'});

        const $label = $('<small>')
            .addClass('text-muted d-block text-truncate mt-1')
            .attr('title', label || '')
            .text(label || 'Image');

        return $item.append($remove, $img, $label);
    }

    function renderUploadPreviews() {
        $('#content_images_preview .post-content-preview-item[data-source="upload"]').remove();
        const input = document.getElementById('content_images');
        const files = Array.from(input?.files || []);

        files.forEach(function (file, index) {
            const reader = new FileReader();
            reader.onload = function (event) {
                $('#content_images_preview').append(previewItem('upload', index, event.target.result, file.name));
                toggleContentEmpty();
            };
            reader.readAsDataURL(file);
        });

        toggleContentEmpty();
    }

    function removeUploadFile(index) {
        const input = document.getElementById('content_images');
        if (!input || !input.files) return;

        const transfer = new DataTransfer();
        Array.from(input.files).forEach(function (file, fileIndex) {
            if (fileIndex !== index) transfer.items.add(file);
        });
        input.files = transfer.files;
        renderUploadPreviews();
    }

    $('#post_slug').on('input', function () { slugTouched = true; });
    $('#post_title').on('input', function () {
        if (!slugTouched) $('#post_slug').val(makeSlug($(this).val()));
    });

    if ($('.select2').length) {
        $('.select2').select2({width:'100%', dropdownParent:$('#ajaxModal')});
    }

    $('#post_featured').on('change', function () {
        $('#featured_media_id').val('');
        $('#featured_remove').val('0');

        const file = this.files && this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            $('#featured_preview').attr('src', event.target.result);
            $('#featured_preview_wrap').removeClass('d-none');
            $('#featured_empty').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    $('#btnChooseFeatured').on('click', function () {
        if (!window.MediaPicker || typeof window.MediaPicker.open !== 'function') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }

        window.MediaPicker.open(function (media) {
            if (!media || !media.id || !media.url) return;
            $('#post_featured').val('');
            $('#featured_media_id').val(media.id);
            $('#featured_remove').val('0');
            $('#featured_preview').attr('src', media.url);
            $('#featured_preview_wrap').removeClass('d-none');
            $('#featured_empty').addClass('d-none');
        }, {type:'image', multiple:false, title:'Choose Featured Image'});
    });

    $('#btnRemoveFeatured').on('click', function () {
        $('#post_featured').val('');
        $('#featured_media_id').val('');
        $('#featured_remove').val('1');
        $('#featured_preview_wrap').addClass('d-none');
        $('#featured_empty').removeClass('d-none');
    });

    $('#btnChooseContentImages').on('click', function () {
        if (!window.MediaPicker || typeof window.MediaPicker.open !== 'function') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }

        window.MediaPicker.open(function (selected) {
            const items = (Array.isArray(selected) ? selected : [selected]).filter(Boolean);
            if (!items.length) return;

            const existingIds = getJsonIds('content_images_media_ids');
            const mergedIds = existingIds.concat(items.map(function (item) { return Number(item.id); }));
            setJsonIds('content_images_media_ids', mergedIds);

            items.forEach(function (media) {
                if (!media || !media.id || !media.url) return;
                const id = Number(media.id);
                if ($('#content_images_preview .post-content-preview-item[data-source="picker"][data-key="' + id + '"]').length) return;
                $('#content_images_preview').append(previewItem('picker', id, media.url, media.file_name || media.name || 'Media'));
            });

            toggleContentEmpty();
        }, {type:'image', multiple:true, max:20, title:'Choose Content Images'});
    });

    $('#content_images').on('change', function () {
        renderUploadPreviews();
    });

    $('#content_images_preview').on('click', '.btn-remove-content-image', function () {
        const source = $(this).data('source');
        const key = Number($(this).data('key'));
        const mediaId = Number($(this).data('media-id'));

        if (source === 'existing' && mediaId) {
            const ids = getJsonIds('content_images_remove_ids');
            ids.push(mediaId);
            setJsonIds('content_images_remove_ids', ids);
            $(this).closest('.post-content-preview-item').remove();
        } else if (source === 'picker' && key) {
            const ids = getJsonIds('content_images_media_ids').filter(function (id) { return id !== key; });
            setJsonIds('content_images_media_ids', ids);
            $(this).closest('.post-content-preview-item').remove();
        } else if (source === 'upload' && Number.isInteger(key)) {
            removeUploadFile(key);
        }

        toggleContentEmpty();
    });

    $('#btnClearContentImages').on('click', function () {
        $('#content_images').val('');
        $('#content_images_media_ids').val('[]');
        $('#content_images_remove_ids').val('[]');
        $('#content_images_clear').val('1');
        $('#content_images_preview .post-content-preview-item').remove();
        toggleContentEmpty();
    });

    document.dispatchEvent(new CustomEvent('admin:content-updated'));
})();
</script>