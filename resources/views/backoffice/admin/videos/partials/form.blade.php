@php
$isEdit=isset($video)&&$video instanceof \App\Models\Video;
$actionUrl=$isEdit?route('admin.videos.update',$video->id):route('admin.videos.store');
$videoMedia=$isEdit?$video->getFirstMedia(\App\Models\Video::VIDEO_COLLECTION):null;
$videoUrl=$videoMedia?$videoMedia->getUrl():null;
$posterMedia=$isEdit?$video->getFirstMedia(\App\Models\Video::POSTER_COLLECTION):null;
$posterUrl=$posterMedia?$posterMedia->getUrl():null;
$youtubeValue=old('youtube_input',$isEdit?$video->provider_video_id:'');
$embedValue=old('embed_input',$isEdit?$video->source_url:'');
@endphp
<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)@method('PUT')@endif
    <div class="row">
        <div class="col-md-8 mb-2"><label class="font-weight-bold mb-1">Title <span
                    class="text-danger">*</span></label><input type="text" name="title"
                class="form-control form-control-sm" value="{{ old('title',$isEdit?$video->title:'') }}" maxlength="190"
                required placeholder="Video title">
            <div class="invalid-feedback error-title"></div>
        </div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Duration (seconds)</label><input type="number"
                name="duration_seconds" class="form-control form-control-sm"
                value="{{ old('duration_seconds',$isEdit?$video->duration_seconds:'') }}" min="0" max="4294967295"
                step="1" placeholder="Optional">
            <div class="invalid-feedback error-duration_seconds"></div>
        </div>
        <div class="col-md-12 mb-3"><label class="font-weight-bold mb-1">Caption</label><textarea name="caption"
                id="video_caption" class="form-control tinymce-editor" rows="7" data-editor-height="280"
                placeholder="Video caption...">{{ old('caption',$isEdit?$video->caption:'') }}</textarea>
            <div class="invalid-feedback error-caption"></div>
        </div>
        <div class="col-md-12 mb-2">
            <div class="alert alert-info py-2 mb-2"><strong><i class="fas fa-sort-amount-down mr-1"></i>Frontend
                    Priority:</strong> 1. YouTube Video → 2. Embedded Video → 3. Recording / Upload Video. You may save
                all three; the user panel automatically renders the first available source.</div>
        </div>
        @can('video_embed')
        <div class="col-md-12 mb-3">
            <div class="card border-primary mb-0">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"><strong><span
                            class="badge badge-primary mr-2">1</span><i
                            class="fab fa-youtube text-danger mr-1"></i>YouTube Video</strong><button type="button"
                        class="btn btn-outline-danger btn-sm" id="btnRemoveYoutube"><i
                            class="fas fa-times mr-1"></i>Clear</button></div>
                <div class="card-body py-3"><label class="font-weight-bold mb-1">YouTube URL / Video ID</label><input
                        type="text" name="youtube_input" id="youtube_input" class="form-control"
                        value="{{ $youtubeValue }}" maxlength="2048"
                        placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX or 11-character ID"><input
                        type="hidden" name="youtube_remove" id="youtube_remove" value="0">
                    <div class="invalid-feedback error-youtube_input"></div><small class="text-muted">Supports
                        youtube.com, youtu.be, Shorts, Live, Embed URL or direct 11-character video ID.</small>
                    <div id="youtube_preview" class="mt-2">@if($isEdit&&$video->provider_video_id)<div
                            class="embed-responsive embed-responsive-16by9 rounded border"><iframe
                                class="embed-responsive-item" src="{{ $video->youtube_embed_url }}"
                                title="{{ $video->title }}" allowfullscreen></iframe></div>@endif</div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <div class="card border-info mb-0">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"><strong><span
                            class="badge badge-info mr-2">2</span><i class="fas fa-code mr-1"></i>Embedded
                        Video</strong><button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveEmbed"><i
                            class="fas fa-times mr-1"></i>Clear</button></div>
                <div class="card-body py-3"><label class="font-weight-bold mb-1">Embed URL / iframe
                        Code</label><textarea name="embed_input" id="embed_input" class="form-control" rows="3"
                        maxlength="5000"
                        placeholder="Paste a YouTube/Vimeo embed URL or iframe code">{{ $embedValue }}</textarea><input
                        type="hidden" name="embed_remove" id="embed_remove" value="0">
                    <div class="invalid-feedback error-embed_input"></div><small class="text-muted">For security, only
                        approved YouTube/Vimeo embed sources are accepted. The iframe code itself is not stored; only
                        its safe <code>src</code> URL is stored.</small>
                    <div id="embed_preview" class="mt-2">@if($isEdit&&$video->source_url)<div
                            class="embed-responsive embed-responsive-16by9 rounded border"><iframe
                                class="embed-responsive-item" src="{{ $video->embedded_playback_url }}"
                                title="{{ $video->title }}" allowfullscreen></iframe></div>@endif</div>
                </div>
            </div>
        </div>
        @endcan
        @can('video_upload')
        <div class="col-md-12 mb-3">
            <div class="card border-success mb-0">
                <div class="card-header bg-light py-2"><strong><span class="badge badge-success mr-2">3</span><i
                            class="fas fa-video mr-1"></i>Recording / Upload Video</strong></div>
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;"><input type="file"
                            name="video_file" id="video_file" class="form-control-file"
                            accept="video/mp4,video/webm">@can('media_list')<button type="button"
                            class="btn btn-outline-primary btn-sm" id="btnChooseVideoFile"><i
                                class="fas fa-photo-video mr-1"></i>Choose from Media</button>@endcan<button
                            type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveVideoFile"><i
                                class="fas fa-trash-alt mr-1"></i>Remove Video</button></div><input type="hidden"
                        name="video_file_media_id" id="video_file_media_id" value=""><input type="hidden"
                        name="video_file_remove" id="video_file_remove" value="0">
                    <div class="text-danger small mb-2 error-video_file"></div>
                    <div class="text-danger small mb-2 error-video_file_media_id"></div>
                    <div id="video_file_preview">@if($videoUrl)<div class="video-file-preview-item"><video
                                src="{{ $videoUrl }}" controls preload="metadata" class="w-100 rounded border bg-dark"
                                style="max-height:260px;"></video><small
                                class="text-muted d-block text-truncate mt-1">{{ $videoMedia->file_name }}</small></div>
                        @else<div class="text-muted small video-file-empty"><i class="fas fa-video mr-1"></i>No
                            recording/upload video selected.</div>@endif</div><small class="text-muted d-block mt-2">MP4
                        or WebM, maximum 100 MB. This source is used only when YouTube and Embedded Video are
                        unavailable.</small>
                </div>
            </div>
        </div>
        @endcan
        <div class="col-md-12 mb-3"><label class="font-weight-bold mb-1">Video Poster Image</label>
            <div class="border rounded p-3 bg-light">
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;"><input type="file"
                        name="video_poster" id="video_poster" class="form-control-file"
                        accept="image/jpeg,image/png,image/webp">@can('media_list')<button type="button"
                        class="btn btn-outline-primary btn-sm" id="btnChooseVideoPoster"><i
                            class="fas fa-images mr-1"></i>Choose from Media</button>@endcan<button type="button"
                        class="btn btn-outline-danger btn-sm" id="btnRemoveVideoPoster"><i
                            class="fas fa-trash-alt mr-1"></i>Remove Poster</button></div><input type="hidden"
                    name="video_poster_media_id" id="video_poster_media_id" value=""><input type="hidden"
                    name="video_poster_remove" id="video_poster_remove" value="0">
                <div class="text-danger small mb-2 error-video_poster"></div>
                <div class="text-danger small mb-2 error-video_poster_media_id"></div>
                <div id="video_poster_preview">@if($posterUrl)<div
                        class="border rounded bg-white p-1 text-center video-poster-preview-item" style="width:180px;">
                        <img src="{{ $posterUrl }}" alt="{{ $isEdit?$video->title:'Video poster' }}" class="rounded"
                            style="width:170px;height:105px;object-fit:cover;"><small
                            class="text-muted d-block text-truncate mt-1">{{ $posterMedia->file_name }}</small></div>
                    @else<div class="text-muted small video-poster-empty"><i class="far fa-image mr-1"></i>No poster
                        selected.</div>@endif</div><small class="text-muted d-block mt-2">JPG, JPEG, PNG or WEBP.
                    Maximum 10 MB.</small>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card border mb-3">
                <div class="card-header py-2 bg-light"><strong><i class="fas fa-sliders-h mr-1"></i>Playback
                        Settings</strong></div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-3 mb-2"><input type="hidden" name="autoplay" value="0">
                            <div class="custom-control custom-switch"><input type="checkbox"
                                    class="custom-control-input" id="video_autoplay" name="autoplay" value="1"
                                    {{ old('autoplay',$isEdit?(int)$video->autoplay:0)?'checked':'' }}><label
                                    class="custom-control-label" for="video_autoplay">Autoplay</label></div>
                        </div>
                        <div class="col-md-3 mb-2"><input type="hidden" name="muted" value="0">
                            <div class="custom-control custom-switch"><input type="checkbox"
                                    class="custom-control-input" id="video_muted" name="muted" value="1"
                                    {{ old('muted',$isEdit?(int)$video->muted:0)?'checked':'' }}><label
                                    class="custom-control-label" for="video_muted">Muted</label></div>
                        </div>
                        <div class="col-md-3 mb-2"><input type="hidden" name="controls" value="0">
                            <div class="custom-control custom-switch"><input type="checkbox"
                                    class="custom-control-input" id="video_controls" name="controls" value="1"
                                    {{ old('controls',$isEdit?(int)$video->controls:1)?'checked':'' }}><label
                                    class="custom-control-label" for="video_controls">Controls</label></div>
                        </div>
                        <div class="col-md-3 mb-2"><input type="hidden" name="loop" value="0">
                            <div class="custom-control custom-switch"><input type="checkbox"
                                    class="custom-control-input" id="video_loop" name="loop" value="1"
                                    {{ old('loop',$isEdit?(int)$video->loop:0)?'checked':'' }}><label
                                    class="custom-control-label" for="video_loop">Loop</label></div>
                        </div>
                    </div><small class="text-muted">Autoplay without muted is automatically disabled by backend.</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Sort Order</label><input type="number"
                name="sort_order" class="form-control form-control-sm"
                value="{{ old('sort_order',$isEdit?$video->sort_order:'') }}" min="0" max="2147483647" step="1"
                placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
        </div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Status <span
                    class="text-danger">*</span></label><select name="is_active" class="form-control form-control-sm"
                required>
                <option value="1" {{ old('is_active',$isEdit?(int)$video->is_active:1)==1?'selected':'' }}>Active
                </option>
                <option value="0" {{ old('is_active',$isEdit?(int)$video->is_active:1)==0?'selected':'' }}>Inactive
                </option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>
    <div class="d-flex justify-content-end border-top pt-2 mt-2"><button type="button" class="btn btn-light btn-sm mr-2"
            data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm"><i
                class="fas fa-save mr-1"></i>{{ $isEdit?'Update Video':'Save Video' }}</button></div>
</form>
<script>
(function() {
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