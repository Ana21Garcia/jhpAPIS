<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Ensure passwords are hashed when assigned.
     */
    public function setPasswordAttribute($value)
    {
        if ($value === null) {
            $this->attributes['password'] = null;
            return;
        }

        // If already hashed (starts with $2y$ or $argon), keep it
        if (is_string($value) && (str_starts_with($value, '$2y$') || str_starts_with($value, '$argon'))) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
    }
}
