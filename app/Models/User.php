<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'reviewer_name', 'name');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'name', 'name');
    }
    public function reports()
    {
        return $this->hasMany(Report::class, 'admin_id');
    }

    public function approvedRatings()
    {
        return $this->hasMany(Rating::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }
}