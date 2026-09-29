<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    protected $table = 'admins';

    protected string $guard_name = 'admin';

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'status',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status' => 'boolean',
            'password' => 'hashed',
        ];
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('media_library')
            ->useDisk('public');
    }

    public function adminlte_profile_url(): string
    {
        return route('admin.profile');
    }

    public function adminlte_image(): string
    {
        return $this->image_url;
    }

    public function adminlte_desc(): string
    {
        return $this->email;
    }

    public function hasProfilePhoto(): bool
    {
        return $this->hasMedia('avatars') || ! empty($this->photo);
    }

    public function getImageUrlAttribute(): string
    {
        $mediaUrl = $this->getFirstMediaUrl('avatars');

        if ($mediaUrl !== '') {
            return $mediaUrl;
        }

        if (! empty($this->photo)) {
            $photo = ltrim((string) $this->photo, '/');

            if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                return $photo;
            }

            if (str_starts_with($photo, 'storage/')) {
                $storagePath = Str::after($photo, 'storage/');

                if (Storage::disk('public')->exists($storagePath)) {
                    return Storage::disk('public')->url($storagePath);
                }
            }

            if (str_starts_with($photo, 'uploads/')) {
                return asset($photo);
            }

            if (Storage::disk('public')->exists($photo)) {
                return Storage::disk('public')->url($photo);
            }

            if (is_file(public_path('uploads/' . $photo))) {
                return asset('uploads/' . $photo);
            }
        }

        return asset('images/no-image.png');
    }
}
