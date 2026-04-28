<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /**
     * Créer une nouvelle promotion.
     */
    public function createPromotion(User $user, array $data): Promotion
    {
        // Résoudre le type polymorphique
        $promotableType = $data['promotable_type'];
        $promotableId   = $data['promotable_id'];

        // Vérifier que l'entité existe
        $promotableClass = $this->resolvePromotableClass($promotableType);
        $promotable = $promotableClass::findOrFail($promotableId);

        return Promotion::create([
            'user_id'         => $user->id,
            'promotable_id'   => $promotable->id,
            'promotable_type' => $promotableClass,
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'status'          => Promotion::STATUS_ACTIVE,
            'is_boosted'      => $data['is_boosted'] ?? false,
        ]);
    }

    /**
     * Annuler une promotion.
     */
    public function cancelPromotion(Promotion $promotion): bool
    {
        return $promotion->cancel();
    }

    /**
     * Activer le boost sur une promotion.
     */
    public function boostPromotion(Promotion $promotion): Promotion
    {
        $promotion->update(['is_boosted' => true]);
        return $promotion->fresh();
    }

    /**
     * Expirer automatiquement les promotions dépassées.
     * Utilisée par la commande Artisan `promotions:expire`.
     */
    public function expireOverduePromotions(): int
    {
        return Promotion::overdue()->update([
            'status' => Promotion::STATUS_EXPIRED,
        ]);
    }

    /**
     * ── Futur système de paiement ──
     * Activer une promotion après confirmation du paiement.
     *
     * @param Promotion $promotion
     * @param string    $paymentId     ID de la transaction (Stripe, etc.)
     * @param string    $paymentStatus Statut du paiement
     * @return Promotion
     */
    public function activateAfterPayment(Promotion $promotion, string $paymentId, string $paymentStatus = 'paid'): Promotion
    {
        $promotion->update([
            'status'         => Promotion::STATUS_ACTIVE,
            'payment_id'     => $paymentId,
            'payment_status' => $paymentStatus,
        ]);

        return $promotion->fresh();
    }

    /**
     * Résoudre la classe du modèle à partir du type court.
     */
    protected function resolvePromotableClass(string $type): string
    {
        $types = Promotion::PROMOTABLE_TYPES;

        if (isset($types[$type])) {
            return $types[$type];
        }

        // Si le type complet est fourni directement (App\Models\...)
        if (in_array($type, $types)) {
            return $type;
        }

        throw new \InvalidArgumentException("Type promotable invalide : {$type}");
    }
}
