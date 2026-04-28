<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'sport_preferences',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'sport_preferences' => 'array',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * Le club appartenant à cet utilisateur (si role = club).
     */
    public function club()
    {
        return $this->hasOne(Club::class);
    }

    /**
     * Les abonnements de l'utilisateur.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Les messages envoyés par l'utilisateur.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Les avis laissés par l'utilisateur.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Les logs d'activité de l'utilisateur.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Les activités créées par l'utilisateur.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Les promotions de l'utilisateur.
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope : utilisateurs actifs uniquement.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : filtrer par rôle.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClub(): bool
    {
        return $this->role === 'club';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
