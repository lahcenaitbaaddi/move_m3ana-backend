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
     */
    public function store(StoreClubRequest $request): JsonResponse
    {
        $data = $request->validated();

        // utilisateur connecté
        $data['user_id'] = auth()->id();

        // valeurs métier importantes
        $data['is_approved'] = false;
        $data['is_active'] = true;

        // upload logo
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        // upload images multiples
        if ($request->hasFile('images')) {
            $images = [];

            foreach ($request->file('images') as $image) {
                $images[] = $image->store('clubs/images', 'public');
            }

            $data['images'] = $images;
        }

        $club = Club::create($data);

        // log activité
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
     */
    public function update(UpdateClubRequest $request, string $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        // sécurité : owner ou admin
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 403);
        }

        $data = $request->validated();

        // update logo
        if ($request->hasFile('logo')) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }

            $data['logo'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        // update images
        if ($request->hasFile('images')) {
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
     */
    public function destroy(string $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        // sécurité
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 403);
        }

        // delete files
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
        ]);
    }

    /**
     * GET /api/clubs/location/{city}
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
        ]);
    }
}