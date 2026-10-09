<script>
(function() {
    // Show the effective playback state before saving.
    const autoplayInput = document.getElementById('video_autoplay');
    const mutedInput = document.getElementById('video_muted');
    if (autoplayInput && mutedInput) {
        autoplayInput.addEventListener('change', function () {
            if (this.checked) mutedInput.checked = true;
        });
        if (autoplayInput.checked) mutedInput.checked = true;
    }
    function renderVideoPreview(url, label) {
        const $preview = $('#video_file_preview').empty();
        if (!url) {
            $preview.html(
                '<div class="text-muted small video-file-empty"><i class="fas fa-video mr-1"></i>No recording/upload video selected.</div>'
                );
            return;
        }
        const $video = $('<video>').attr({
            src: url,
            controls: true,
            preload: 'metadata'
        }).addClass('w-100 rounded border bg-dark').css('max-height', '260px');
        const $label = $('<small>').addClass('text-muted d-block text-truncate mt-1').text(label || 'Video');
        $preview.append($('<div>').addClass('video-file-preview-item').append($video, $label));
    }

    function renderPosterPreview(url, label) {
        const $preview = $('#video_poster_preview').empty();
        if (!url) {
            $preview.html(
                '<div class="text-muted small video-poster-empty"><i class="far fa-image mr-1"></i>No poster selected.</div>'
                );
            return;
        }
        const $box = $('<div>').addClass('border rounded bg-white p-1 text-center video-poster-preview-item').css(
            'width', '180px');
        const $img = $('<img>').attr({
            src: url,
            alt: label || 'Video poster'
        }).addClass('rounded').css({
            width: '170px',
            height: '105px',
            objectFit: 'cover'
        });
        const $label = $('<small>').addClass('text-muted d-block text-truncate mt-1').text(label || 'Poster');
        $preview.append($box.append($img, $label));
    }
    $('#youtube_input').off('input.video').on('input.video', function() {
        $('#youtube_remove').val('0');
    });
    $('#embed_input').off('input.video').on('input.video', function() {
        $('#embed_remove').val('0');
    });
    $('#btnRemoveYoutube').off('click.video').on('click.video', function() {
        $('#youtube_input').val('');
        $('#youtube_remove').val('1');
        $('#youtube_preview').empty();
    });
    $('#btnRemoveEmbed').off('click.video').on('click.video', function() {
        $('#embed_input').val('');
        $('#embed_remove').val('1');
        $('#embed_preview').empty();
    });
    $('#video_file').off('change.video').on('change.video', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        $('#video_file_media_id').val('');
        $('#video_file_remove').val('0');
        if (!file) {
            renderVideoPreview(null, null);
            return;
        }
        renderVideoPreview(URL.createObjectURL(file), file.name);
    });
    $('#btnChooseVideoFile').off('click.video').on('click.video', function() {
        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }
        MediaPicker.open(function(media) {
            if (!media || !media.id || !media.url) return;
            $('#video_file').val('');
            $('#video_file_media_id').val(media.id);
            $('#video_file_remove').val('0');
            renderVideoPreview(media.url, media.file_name || media.name || 'Media video');
        }, {
            type: 'video',
            multiple: false,
            max: 1,
            title: 'Choose Recording / Upload Video'
        });
    });
    $('#btnRemoveVideoFile').off('click.video').on('click.video', function() {
        $('#video_file').val('');
        $('#video_file_media_id').val('');
        $('#video_file_remove').val('1');
        renderVideoPreview(null, null);
    });
    $('#video_poster').off('change.video').on('change.video', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        $('#video_poster_media_id').val('');
        $('#video_poster_remove').val('0');
        if (!file) {
            renderPosterPreview(null, null);
            return;
        }
        const reader = new FileReader();
        reader.onload = function(event) {
            renderPosterPreview(event.target.result, file.name);
        };
        reader.readAsDataURL(file);
    });
    $('#btnChooseVideoPoster').off('click.video').on('click.video', function() {
        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }
        MediaPicker.open(function(media) {
            if (!media || !media.id || !media.url) return;
            $('#video_poster').val('');
            $('#video_poster_media_id').val(media.id);
            $('#video_poster_remove').val('0');
            renderPosterPreview(media.url, media.file_name || media.name || 'Media poster');
        }, {
            type: 'image',
            multiple: false,
            max: 1,
            title: 'Choose Video Poster'
        });
    });
    $('#btnRemoveVideoPoster').off('click.video').on('click.video', function() {
        $('#video_poster').val('');
        $('#video_poster_media_id').val('');
        $('#video_poster_remove').val('1');
        renderPosterPreview(null, null);
    });
})();
</script>