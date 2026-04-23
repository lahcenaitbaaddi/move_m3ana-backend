<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * POST /api/subscriptions
     * S'abonner à un club.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
        ]);

        // Vérifier si déjà abonné
        $exists = Subscription::where('user_id', auth()->id())
            ->where('club_id', $request->club_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà abonné à ce club.',
            ], 409);
        }

        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            'club_id' => $request->club_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Abonnement réussi.',
            'data'    => new SubscriptionResource($subscription->load(['user', 'club'])),
        ], 201);
    }

    /**
     * DELETE /api/subscriptions/{id}
     * Se désabonner d'un club.
     */
    public function destroy(string $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire de l'abonnement
        if ($subscription->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cet abonnement.',
            ], 403);
        }

        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Désabonnement réussi.',
        ]);
    }

    /**
     * GET /api/subscriptions/user/{userId}
     * Liste des abonnements d'un utilisateur.
     */
    public function byUser(string $userId): JsonResponse
    {
        $subscriptions = Subscription::where('user_id', $userId)
            ->with(['user', 'club'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => SubscriptionResource::collection($subscriptions),
            'meta'    => [
                'current_page' => $subscriptions->currentPage(),
                'last_page'    => $subscriptions->lastPage(),
                'per_page'     => $subscriptions->perPage(),
                'total'        => $subscriptions->total(),
            ],
        ]);
    }

    /**
     * GET /api/subscriptions/club/{clubId}
     * Liste des abonnés d'un club.
     */
    public function byClub(string $clubId): JsonResponse
    {
        $subscriptions = Subscription::where('club_id', $clubId)
            ->with(['user', 'club'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => SubscriptionResource::collection($subscriptions),
            'meta'    => [
                'current_page' => $subscriptions->currentPage(),
                'last_page'    => $subscriptions->lastPage(),
                'per_page'     => $subscriptions->perPage(),
                'total'        => $subscriptions->total(),
            ],
        ]);
    }
}
