<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignSetting extends Model
{
    use SoftDeletes;

    public const SINGLETON_ID = 1;

    public const FALLBACK_MODES = [
        'homepage' => 'Homepage',
        'page' => 'Specific Page',
        'none' => 'No Fallback',
    ];

    protected $fillable = [
        'default_campaign_id',
        'fallback_mode',
        'fallback_page_id',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'default_campaign_id' => 'integer',
            'fallback_page_id' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public static function singleton(): self
    {
        $setting = static::withTrashed()->find(self::SINGLETON_ID);

        if ($setting) {
            if ($setting->trashed()) {
                $setting->restore();
            }

            return $setting;
        }

        $setting = new static(['fallback_mode' => 'homepage']);
        $setting->id = self::SINGLETON_ID;
        $setting->save();

        return $setting;
    }

    public function defaultCampaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'default_campaign_id')->withTrashed();
    }

    public function fallbackPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'fallback_page_id')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by')->withTrashed();
    }
}
