<?php

namespace App\Models\Traits;

use App\Models\Promotion;

/**
 * Trait HasPromotions
 *
 * Ajoute le support des promotions polymorphiques à n'importe quel modèle.
 * Utilisation : `use HasPromotions;` dans le modèle concerné.
 */
trait HasPromotions
{
    /**
     * Toutes les promotions associées à cette entité.
     */
    public function promotions()
    {
        return $this->morphMany(Promotion::class, 'promotable');
    }

    /**
     * La promotion active en cours (la plus récente).
     */
    public function activePromotion()
    {
        return $this->morphOne(Promotion::class, 'promotable')
            ->where('status', Promotion::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest('start_date');
    }

    /**
     * Vérifie si l'entité est actuellement promue.
     */
    public function isPromoted(): bool
    {
        return $this->activePromotion()->exists();
    }

    /**
     * Vérifie si l'entité est actuellement boostée.
     */
    public function isBoosted(): bool
    {
        return $this->activePromotion()->where('is_boosted', true)->exists();
    }

    /**
     * Scope : trier les résultats avec les promus en premier.
     * Ordre de priorité : boosted > promoted > normal.
     */
    public function scopeWithPromotionPriority($query)
    {
        $promotionTable = (new Promotion())->getTable();
        $modelTable = $this->getTable();

        return $query
            ->leftJoin("{$promotionTable} as promo", function ($join) use ($modelTable) {
                $join->on('promo.promotable_id', '=', "{$modelTable}.id")
                    ->where('promo.promotable_type', '=', static::class)
                    ->where('promo.status', '=', Promotion::STATUS_ACTIVE)
                    ->where('promo.start_date', '<=', now())
                    ->where('promo.end_date', '>=', now());
            })
            ->select("{$modelTable}.*")
            ->selectRaw('CASE WHEN promo.is_boosted = 1 THEN 2 WHEN promo.id IS NOT NULL THEN 1 ELSE 0 END as promotion_priority')
            ->orderByDesc('promotion_priority');
    }
}
