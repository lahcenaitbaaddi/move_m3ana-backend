<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * POST /api/reviews
     * Laisser un avis sur un club.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        // Vérifier si l'utilisateur a déjà laissé un avis pour ce club
        $exists = Review::where('user_id', auth()->id())
            ->where('club_id', $request->club_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà laissé un avis pour ce club.',
            ], 409);
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'club_id' => $request->club_id,
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avis ajouté avec succès.',
            'data'    => new ReviewResource($review->load(['user', 'club'])),
        ], 201);
    }

    /**
     * GET /api/reviews/club/{clubId}
     * Liste des avis d'un club.
     */
    public function byClub(string $clubId): JsonResponse
    {
        $reviews = Review::where('club_id', $clubId)
            ->with(['user', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculer la moyenne des notes
        $averageRating = Review::where('club_id', $clubId)->avg('rating');

        return response()->json([
            'success'        => true,
            'data'           => ReviewResource::collection($reviews),
            'average_rating' => round($averageRating ?? 0, 1),
            'meta'           => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    /**
     * PUT /api/reviews/{id}
     * Modifier un avis (auteur uniquement).
     */
    public function update(UpdateReviewRequest $request, string $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        // Vérifier que l'utilisateur est l'auteur
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cet avis.',
            ], 403);
        }

        $review->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Avis mis à jour avec succès.',
            'data'    => new ReviewResource($review->fresh()->load(['user', 'club'])),
        ]);
    }

    /**
     * DELETE /api/reviews/{id}
     * Supprimer un avis (auteur ou admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cet avis.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Avis supprimé avec succès.',
        ]);
    }
}
