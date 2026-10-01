@props(['video','class'=>''])
@php
$source=$video?->resolved_source_type;
$poster=$video?->poster_url;
@endphp
@if($video&&$video->is_active&&$source)
<div {{ $attributes->merge(['class'=>'video-player '.$class]) }} data-video-source="{{ $source }}">
    @if($source==='youtube')
    <div class="aspect-video"><iframe src="{{ $video->youtube_embed_url }}" title="{{ $video->title }}"
            class="w-full h-full" loading="lazy"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen></iframe></div>
    @elseif($source==='embed')
    <div class="aspect-video"><iframe src="{{ $video->embedded_playback_url }}" title="{{ $video->title }}"
            class="w-full h-full" loading="lazy" allow="autoplay; fullscreen; picture-in-picture"
            allowfullscreen></iframe></div>
    @elseif($source==='upload')
    <video src="{{ $video->playback_url }}" @if($poster) poster="{{ $poster }}" @endif @if($video->controls) controls
        @endif @if($video->autoplay) autoplay @endif @if($video->muted) muted @endif @if($video->loop) loop @endif
        playsinline preload="metadata" class="w-full h-auto"></video>
    @endif
</div>
@endif