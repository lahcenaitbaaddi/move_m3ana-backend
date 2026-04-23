<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'sport',
        'description',
        'address',
        'city',
        'phone',
        'email',
        'tarifs',
        'horaires',
        'logo',
        'images',
        'social_links',
        'latitude',
        'longitude',
        'is_approved',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
        'social_links' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * Le propriétaire du club (utilisateur avec role = club).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les événements du club.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Les abonnés du club.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Les messages reçus par le club.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Les avis sur le club.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope : clubs approuvés uniquement.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope : clubs actifs uniquement.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : filtrer par sport.
     */
    public function scopeBySport($query, string $sport)
    {
        return $query->where('sport', 'LIKE', "%{$sport}%");
    }

    /**
     * Scope : filtrer par ville.
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'LIKE', "%{$city}%");
    }

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Obtenir la note moyenne du club.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Obtenir le nombre d'abonnés.
     */
    public function getSubscribersCountAttribute()
    {
        return $this->subscriptions()->count();
    }
}
