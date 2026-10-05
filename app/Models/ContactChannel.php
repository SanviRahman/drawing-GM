<?php
namespace App\Models;

use App\Models\ContactTarget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactChannel extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'whatsapp' => 'WhatsApp',
        'phone'    => 'Phone',
        'email'    => 'Email',
        'custom'   => 'Custom URL',
    ];

    /**
     * Controlled icon keys. The database stores the key, never arbitrary HTML.
     */
    public const ICON_OPTIONS = [
        'whatsapp'   => ['label' => 'WhatsApp', 'class' => 'fab fa-whatsapp'],
        'phone'      => ['label' => 'Phone', 'class' => 'fas fa-phone-alt'],
        'email'      => ['label' => 'Email', 'class' => 'fas fa-envelope'],
        'link'       => ['label' => 'Link', 'class' => 'fas fa-link'],
        'comments'   => ['label' => 'Comments', 'class' => 'fas fa-comments'],
        'headset'    => ['label' => 'Headset', 'class' => 'fas fa-headset'],
        'map-marker' => ['label' => 'Map Marker', 'class' => 'fas fa-map-marker-alt'],
    ];

    /**
     * Named colour tokens accepted in addition to #RRGGBB.
     */
    public const COLOR_TOKENS = [
        'primary'   => '#007bff',
        'secondary' => '#6c757d',
        'success'   => '#28a745',
        'danger'    => '#dc3545',
        'warning'   => '#ffc107',
        'info'      => '#17a2b8',
        'dark'      => '#343a40',
        'light'     => '#f8f9fa',
        'whatsapp'  => '#25D366',
    ];

    protected $fillable = [
        'type',
        'label',
        'region',
        'value',
        'display_value',
        'message_template',
        'icon',
        'colour',
        'availability_text',
        'is_default',
        'is_active',
        'track_clicks',
        'sort_order',
    ];

    protected $attributes = [
        'is_default'   => false,
        'is_active'    => true,
        'track_clicks' => true,
        'sort_order'   => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_default'   => 'boolean',
            'is_active'    => 'boolean',
            'track_clicks' => 'boolean',
            'sort_order'   => 'integer',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ContactTarget::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function getIconClassAttribute(): string
    {
        return self::ICON_OPTIONS[$this->icon]['class'] ?? 'fas fa-link';
    }

    public function getColourCssAttribute(): string
    {
        $colour = trim((string) $this->colour);

        return self::COLOR_TOKENS[$colour] ?? $colour;
    }

    /**
     * Contact-message templates are edited with TinyMCE by project convention.
     * This accessor converts the stored safe HTML into plain text for future
     * WhatsApp/SMS-style URL generation.
     */
    public function getMessageTemplateTextAttribute(): ?string
    {
        $html = trim((string) ($this->message_template ?? ''));

        if ($html === '') {
            return null;
        }

        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6])>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text) !== '' ? trim($text) : null;
    }
}
