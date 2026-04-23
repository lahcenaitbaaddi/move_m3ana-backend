<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'status',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur qui a signalé le contenu.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Le contenu signalé (polymorphic: Club, Event, Review, etc.).
     */
    public function reportable()
    {
        return $this->morphTo();
    }
}
