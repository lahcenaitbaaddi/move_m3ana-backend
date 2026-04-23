<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'club_id',
        'rating',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur qui a laissé l'avis.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le club évalué.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
