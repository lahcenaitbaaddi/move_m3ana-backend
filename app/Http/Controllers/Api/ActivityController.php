<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    /**
     * POST /api/activities
     * Créer une nouvelle activité (propriétaire du club ou admin).
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Vérifier que l'utilisateur est le propriétaire du club
        $club = Club::findOrFail($data['club_id']);
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à créer une activité pour ce club.',
            ], 403);
        }

        $data['user_id'] = auth()->id();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('activities', 'public');
        }

        $activity = Activity::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Activité créée avec succès.',
            'data'    => new ActivityResource($activity->load(['club', 'user'])),
        ], 201);
    }

    /**
     * GET /api/activities
     * Liste de toutes les activités avec promotions en priorité.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::with(['club', 'user'])
            ->withPromotionPriority();

        // Filtres optionnels
        if ($request->has('sport')) {
            $query->bySport($request->sport);
        }
        if ($request->has('city')) {
            $query->byCity($request->city);
        }
        if ($request->has('club_id')) {
            $query->byClub($request->club_id);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ActivityResource::collection($activities),
            'meta'    => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'per_page'     => $activities->perPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }

    /**
     * GET /api/activities/{id}
     * Détail d'une activité.
     */
    public function show(string $id): JsonResponse
    {
        $activity = Activity::with(['club', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new ActivityResource($activity),
        ]);
    }

    /**
     * PUT /api/activities/{id}
     * Modifier une activité (propriétaire du club ou admin).
     */
    public function update(UpdateActivityRequest $request, string $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $club     = $activity->club;

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cette activité.',
            ], 403);
        }

        $data = $request->validated();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            if ($activity->image) {
                Storage::disk('public')->delete($activity->image);
            }
            $data['image'] = $request->file('image')->store('activities', 'public');
        }

        $activity->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Activité mise à jour avec succès.',
            'data'    => new ActivityResource($activity->fresh()->load(['club', 'user'])),
        ]);
    }

    /**
     * DELETE /api/activities/{id}
     * Supprimer une activité (propriétaire du club ou admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $club     = $activity->club;

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette activité.',
            ], 403);
        }

        // Supprimer l'image
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activité supprimée avec succès.',
        ]);
    }

    /**
     * GET /api/activities/club/{clubId}
     * Liste des activités d'un club spécifique.
     */
    public function byClub(string $clubId): JsonResponse
    {
        $activities = Activity::where('club_id', $clubId)
            ->with(['club', 'user'])
            ->withPromotionPriority()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ActivityResource::collection($activities),
            'meta'    => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'per_page'     => $activities->perPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }

    /**
     * GET /api/activities/sport/{sport}
     * Liste des activités filtrées par sport.
     */
    public function bySport(string $sport): JsonResponse
    {
        $activities = Activity::bySport($sport)
            ->with(['club', 'user'])
            ->withPromotionPriority()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ActivityResource::collection($activities),
            'meta'    => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'per_page'     => $activities->perPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }

    /**
     * GET /api/activities/city/{city}
     * Liste des activités filtrées par ville.
     */
    public function byCity(string $city): JsonResponse
    {
        $activities = Activity::byCity($city)
            ->with(['club', 'user'])
            ->withPromotionPriority()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ActivityResource::collection($activities),
            'meta'    => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'per_page'     => $activities->perPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }
}
