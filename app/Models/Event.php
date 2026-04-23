<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'club_id',
        'title',
        'description',
        'date',
        'location',
        'capacity',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'capacity' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * Le club organisateur de l'événement.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
