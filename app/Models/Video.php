<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Video extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;
    public const VIDEO_COLLECTION    = 'video_file';
    public const POSTER_COLLECTION   = 'video_poster';
    public const SOURCE_TYPES        = ['youtube', 'embed', 'upload'];
    public const PROCESSING_STATUSES = ['pending', 'processing', 'ready', 'failed'];
    protected $fillable              = ['attachable_type', 'attachable_id', 'title', 'caption', 'source_type', 'provider', 'provider_video_id', 'source_url', 'duration_seconds', 'autoplay', 'muted', 'controls', 'loop', 'processing_status', 'processing_error', 'sort_order', 'is_active'];
    protected $attributes            = ['source_type' => 'youtube', 'autoplay' => false, 'muted' => false, 'controls' => true, 'loop' => false, 'processing_status' => 'ready', 'sort_order' => 0, 'is_active' => true];
    protected function casts(): array
    {
        return ['attachable_id' => 'integer', 'duration_seconds' => 'integer', 'autoplay' => 'boolean', 'muted' => 'boolean', 'controls' => 'boolean', 'loop' => 'boolean', 'sort_order' => 'integer', 'is_active' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
    }
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::VIDEO_COLLECTION)->useDisk('public')->singleFile()->acceptsMimeTypes(['video/mp4', 'video/webm']);
        $this->addMediaCollection(self::POSTER_COLLECTION)->useDisk('public')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title')->orderBy('id');
    }
    public function getResolvedSourceTypeAttribute(): ?string
    {
        if ($this->provider_video_id) {
            return 'youtube';
        }

        if ($this->source_url) {
            return 'embed';
        }

        if ($this->hasMedia(self::VIDEO_COLLECTION)) {
            return 'upload';
        }

        return null;
    }
    public function getAvailableSourcesAttribute(): array
    {
        $sources = [];
        if ($this->provider_video_id) {
            $sources[] = 'youtube';
        }

        if ($this->source_url) {
            $sources[] = 'embed';
        }

        if ($this->hasMedia(self::VIDEO_COLLECTION)) {
            $sources[] = 'upload';
        }

        return $sources;
    }
    public function getSourceTypeLabelAttribute(): string
    {
        return match ($this->resolved_source_type) {'youtube' => 'YouTube', 'embed' => 'Embedded Video', 'upload' => 'Recording / Upload',     default => 'No Source'};
    }
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (! preg_match('/^[a-zA-Z0-9_-]{11}$/', (string) $this->provider_video_id)) {
            return null;
        }

        $query = ['autoplay' => $this->autoplay ? 1 : 0, 'mute' => $this->muted ? 1 : 0, 'controls' => $this->controls ? 1 : 0, 'rel' => 0];
        if ($this->loop) {
            $query['loop']     = 1;
            $query['playlist'] = $this->provider_video_id;
        }
        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($this->provider_video_id) . '?' . http_build_query($query);
    }
    public function getEmbeddedPlaybackUrlAttribute(): ?string
    {
        if (! $this->source_url) {
            return null;
        }

        $url       = $this->source_url;
        $separator = str_contains($url, '?') ? '&' : '?';
        $host      = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (str_contains($host, 'vimeo.com')) {
            return $url . $separator . http_build_query(['autoplay' => $this->autoplay ? 1 : 0, 'muted' => $this->muted ? 1 : 0, 'controls' => $this->controls ? 1 : 0, 'loop' => $this->loop ? 1 : 0]);
        }
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com')) {
            return $url . $separator . http_build_query(['autoplay' => $this->autoplay ? 1 : 0, 'mute' => $this->muted ? 1 : 0, 'controls' => $this->controls ? 1 : 0, 'loop' => $this->loop ? 1 : 0]);
        }
        return $url;
    }
    public function getPlaybackUrlAttribute(): ?string
    {
        return match ($this->resolved_source_type) {'youtube' => $this->youtube_embed_url, 'embed' => $this->embedded_playback_url, 'upload' => $this->getFirstMediaUrl(self::VIDEO_COLLECTION) ?: null,     default => null};
    }
    public function getPosterUrlAttribute(): ?string
    {
        $uploaded = $this->getFirstMediaUrl(self::POSTER_COLLECTION);
        if ($uploaded) return $uploaded;
        $id = (string) $this->provider_video_id;
        return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) ? 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg' : null;
    }
    public function getHasPlayableSourceAttribute(): bool
    {
        return $this->resolved_source_type !== null;
    }
}
