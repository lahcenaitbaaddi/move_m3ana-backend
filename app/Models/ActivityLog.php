<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'utilisateur associé au log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
