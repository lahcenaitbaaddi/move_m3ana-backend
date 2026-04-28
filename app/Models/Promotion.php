<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    // ─── Status Constants ────────────────────────────────────

    const STATUS_PENDING   = 'pending';
    const STATUS_ACTIVE    = 'active';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Types polymorphiques autorisés.
     */
    const PROMOTABLE_TYPES = [
        'activity' => \App\Models\Activity::class,
        'club'     => \App\Models\Club::class,
        'event'    => \App\Models\Event::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'promotable_id',
        'promotable_type',
        'start_date',
        'end_date',
        'status',
        'is_boosted',
        'payment_id',
        'payment_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_boosted' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    /**
     * L'entité promue (Activity, Club ou Event).
     */
    public function promotable()
    {
        return $this->morphTo();
    }

    /**
     * L'utilisateur propriétaire de la promotion.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope : promotions actives.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope : promotions en attente.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope : promotions expirées.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope : promotions boostées.
     */
    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true);
    }

    /**
     * Scope : promotions dont la date est dépassée mais encore marquées active.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('end_date', '<', now());
    }

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Vérifie si la promotion est actuellement active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Vérifie si la promotion est expirée.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || $this->end_date < now();
    }

    /**
     * Vérifie si la promotion peut être annulée.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACTIVE]);
    }

    /**
     * Annuler la promotion.
     */
    public function cancel(): bool
    {
        if (!$this->isCancellable()) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
        return true;
    }
}
