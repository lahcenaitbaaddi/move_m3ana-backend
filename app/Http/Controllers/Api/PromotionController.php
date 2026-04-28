<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\StorePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    protected PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * POST /api/promotions
     * Créer une nouvelle promotion.
     */
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $promotion = $this->promotionService->createPromotion(
                auth()->user(),
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion créée avec succès.',
                'data'    => new PromotionResource($promotion->load(['promotable', 'user'])),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/promotions
     * Liste des promotions de l'utilisateur connecté.
     */
    public function index(): JsonResponse
    {
        $promotions = Promotion::where('user_id', auth()->id())
            ->with(['promotable', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => PromotionResource::collection($promotions),
            'meta'    => [
                'current_page' => $promotions->currentPage(),
                'last_page'    => $promotions->lastPage(),
                'per_page'     => $promotions->perPage(),
                'total'        => $promotions->total(),
            ],
        ]);
    }

    /**
     * GET /api/promotions/{id}
     * Détail d'une promotion.
     */
    public function show(string $id): JsonResponse
    {
        $promotion = Promotion::with(['promotable', 'user'])->findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire ou admin
        if (auth()->user()->role !== 'admin' && $promotion->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à voir cette promotion.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => new PromotionResource($promotion),
        ]);
    }

    /**
     * PUT /api/promotions/{id}/boost
     * Activer le boost sur une promotion.
     */
    public function boost(string $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $promotion->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à booster cette promotion.',
            ], 403);
        }

        // Vérifier que la promotion est active
        if (!$promotion->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Seule une promotion active peut être boostée.',
            ], 422);
        }

        $promotion = $this->promotionService->boostPromotion($promotion);

        return response()->json([
            'success' => true,
            'message' => 'Promotion boostée avec succès.',
            'data'    => new PromotionResource($promotion->load(['promotable', 'user'])),
        ]);
    }

    /**
     * PUT /api/promotions/{id}/cancel
     * Annuler une promotion.
     */
    public function cancel(string $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $promotion->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à annuler cette promotion.',
            ], 403);
        }

        $cancelled = $this->promotionService->cancelPromotion($promotion);

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Cette promotion ne peut pas être annulée (statut actuel : ' . $promotion->status . ').',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Promotion annulée avec succès.',
            'data'    => new PromotionResource($promotion->fresh()->load(['promotable', 'user'])),
        ]);
    }
}
