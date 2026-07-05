<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;




class User extends Authenticatable implements HasAvatar
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
            'kost'=> $this->role === 'owner_kost' ,
            'admin' => $this->role === 'admin',
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
    return $this->$avatarColumn ? asset('storage/' . $this->$avatarColumn) : null;
    }
}
