<script>
(function() {
    const isEdit = @json($isEdit);
    let slugTouched = isEdit;

    function makeSlug(value) {
        return String(value || '').toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }

    function safeText(value) {
        return $('<div>').text(value || '').html();
    }

    function renderPreview(collection, items) {
        const $preview = $('#' + collection + '_preview').empty();

        items.forEach(function(media) {
            if (!media.url) return;
            $preview.append('<div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="' + media.url + '" alt="' + safeText(media.name || 'Media') + '" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1">' + safeText(media.file_name || media.name || '') + '</small></div>');
        });

        if (!$preview.children().length) {
            $preview.html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
        }
    }

    $('#service_slug').on('input', function() {
        slugTouched = true;
    });

    $('#service_name').on('input', function() {
        if (!slugTouched) $('#service_slug').val(makeSlug($(this).val()));
    });

    $('.btn-choose-service-media').on('click', function() {
        const collection = $(this).data('collection');
        const label = $(this).data('label');
        const multiple = Number($(this).data('multiple')) === 1;
        const max = Number($(this).data('max')) || 1;

        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }

        MediaPicker.open(function(selected) {
            const items = (Array.isArray(selected) ? selected : [selected]).filter(Boolean).slice(0, max);

            if (!items.length) return;

            $('#' + collection).val('');
            $('#' + collection + '_media_ids').val(JSON.stringify(items.map(function(item) { return item.id; })));
            $('#' + collection + '_clear').val('0');
            renderPreview(collection, items);
        }, { type: 'image', multiple: multiple, max: max, title: 'Choose ' + label });
    });

    $('.service-media-file').on('change', function(event) {
        const collection = $(this).attr('id');
        const files = Array.from(event.target.files || []);
        const $preview = $('#' + collection + '_preview').empty();

        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_clear').val('0');

        if (!files.length) {
            $preview.html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
            return;
        }

        files.forEach(function(file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                $preview.append('<div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="' + e.target.result + '" alt="Preview" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1">' + safeText(file.name) + '</small></div>');
            };

            reader.readAsDataURL(file);
        });
    });

    $('.btn-clear-service-media').on('click', function() {
        const collection = $(this).data('collection');

        $('#' + collection).val('');
        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_clear').val('1');
        $('#' + collection + '_preview').html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
    });

    document.dispatchEvent(new CustomEvent('admin:content-updated'));
})();
</script>