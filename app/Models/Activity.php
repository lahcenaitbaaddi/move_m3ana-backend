<?php

namespace App\Models;

use App\Models\Traits\HasPromotions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, HasPromotions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'club_id',
        'title',
        'description',
        'sport',
        'location',
        'city',
        'date',
        'capacity',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'      => 'datetime',
        'capacity'  => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur qui a créé l'activité.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le club associé à l'activité.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope : activités actives uniquement.
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

    /**
     * Scope : filtrer par club.
     */
    public function scopeByClub($query, int $clubId)
    {
        return $query->where('club_id', $clubId);
    }
}
