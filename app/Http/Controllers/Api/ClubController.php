<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\StoreClubRequest;
use App\Http\Requests\Club\UpdateClubRequest;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    /**
     * POST /api/clubs
     * Créer un nouveau club.
     */
    public function store(StoreClubRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        // Gestion des images multiples
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('clubs/images', 'public');
            }
            $data['images'] = $images;
        }

        $club = Club::create($data);

        // Log de l'activité
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'club_created',
            'description' => "Nouveau club créé : {$club->name}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Club créé avec succès. En attente d\'approbation par l\'administrateur.',
            'data'    => new ClubResource($club),
        ], 201);
    }

    /**
     * GET /api/clubs
     * Liste de tous les clubs approuvés.
     */
    public function index(): JsonResponse
    {
        $clubs = Club::approved()
            ->active()
            ->with('user')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ClubResource::collection($clubs),
            'meta'    => [
                'current_page' => $clubs->currentPage(),
                'last_page'    => $clubs->lastPage(),
                'per_page'     => $clubs->perPage(),
                'total'        => $clubs->total(),
            ],
        ]);
    }

    /**
     * GET /api/clubs/{id}
     * Détail d'un club.
     */
    public function show(string $id): JsonResponse
    {
        $club = Club::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new ClubResource($club),
        ]);
    }

    /**
     * PUT /api/clubs/{id}
     * Modifier un club (owner ou admin).
     */
    public function update(UpdateClubRequest $request, string $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire ou admin
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier ce club.',
            ], 403);
        }

        $data = $request->validated();

        // Gestion du logo
        if ($request->hasFile('logo')) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }
            $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        // Gestion des images multiples
        if ($request->hasFile('images')) {
            // Supprimer les anciennes images
            if ($club->images) {
                foreach ($club->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('clubs/images', 'public');
            }
            $data['images'] = $images;
        }

        $club->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Club mis à jour avec succès.',
            'data'    => new ClubResource($club->fresh()),
        ]);
    }

    /**
     * DELETE /api/clubs/{id}
     * Supprimer un club (owner ou admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire ou admin
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer ce club.',
            ], 403);
        }

        // Supprimer les fichiers associés
        if ($club->logo) {
            Storage::disk('public')->delete($club->logo);
        }
        if ($club->images) {
            foreach ($club->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $club->delete();

        return response()->json([
            'success' => true,
            'message' => 'Club supprimé avec succès.',
        ]);
    }

    /**
     * GET /api/clubs/sport/{sport}
     * Chercher des clubs par sport.
     */
    public function bySport(string $sport): JsonResponse
    {
        $clubs = Club::approved()
            ->active()
            ->bySport($sport)
            ->with('user')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ClubResource::collection($clubs),
            'meta'    => [
                'current_page' => $clubs->currentPage(),
                'last_page'    => $clubs->lastPage(),
                'per_page'     => $clubs->perPage(),
                'total'        => $clubs->total(),
            ],
        ]);
    }

    /**
     * GET /api/clubs/location/{city}
     * Chercher des clubs par ville/localisation.
     */
    public function byLocation(string $city): JsonResponse
    {
        $clubs = Club::approved()
            ->active()
            ->byCity($city)
            ->with('user')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ClubResource::collection($clubs),
            'meta'    => [
                'current_page' => $clubs->currentPage(),
                'last_page'    => $clubs->lastPage(),
                'per_page'     => $clubs->perPage(),
                'total'        => $clubs->total(),
            ],
        ]);
    }
}
