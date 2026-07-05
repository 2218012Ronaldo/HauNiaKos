<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;




class User extends Authenticatable implements HasAvatar, FilamentUser
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;
    
protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'phone'];
protected $hidden = ['password', 'remember_token'];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'kost' => in_array($this->role, ['admin', 'owner_kost', 'user'], true),
            default => false,
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner_kost';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }


    public function boardingHouses()
    {
        return $this->hasMany(BoardingHouse::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar');
        $avatar = $this->$avatarColumn;

        if (! $avatar) {
            return null;
        }

        $disk = config('filament-edit-profile.disk', 'public');

        $url = Storage::disk($disk)->exists($avatar)
            ? Storage::disk($disk)->url($avatar)
            : null;

        if (! $url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsed = parse_url($url);
            $host = request()?->getHost();

            if ($host && isset($parsed['host']) && $parsed['host'] !== $host) {
                $relative = $parsed['path'] ?? $url;
                if (isset($parsed['query'])) {
                    $relative .= '?'.$parsed['query'];
                }

                return $relative;
            }
        }

        return $url;
    }
}
