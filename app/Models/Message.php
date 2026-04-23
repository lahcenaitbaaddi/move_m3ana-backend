<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
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
        'message',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur qui a envoyé le message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le club destinataire du message.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
