<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
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
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur abonné.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le club auquel l'utilisateur est abonné.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
