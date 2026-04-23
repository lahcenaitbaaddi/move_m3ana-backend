<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * POST /api/events
     * Créer un nouvel événement (propriétaire du club uniquement).
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Vérifier que l'utilisateur est le propriétaire du club
        $club = Club::findOrFail($data['club_id']);
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à créer un événement pour ce club.',
            ], 403);
        }

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Événement créé avec succès.',
            'data'    => new EventResource($event->load('club')),
        ], 201);
    }

    /**
     * GET /api/events
     * Liste de tous les événements.
     */
    public function index(): JsonResponse
    {
        $events = Event::with('club')
            ->orderBy('date', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => EventResource::collection($events),
            'meta'    => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'per_page'     => $events->perPage(),
                'total'        => $events->total(),
            ],
        ]);
    }

    /**
     * GET /api/events/{id}
     * Détail d'un événement.
     */
    public function show(string $id): JsonResponse
    {
        $event = Event::with('club')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => new EventResource($event),
        ]);
    }

    /**
     * PUT /api/events/{id}
     * Modifier un événement (propriétaire du club ou admin).
     */
    public function update(UpdateEventRequest $request, string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $club  = $event->club;

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cet événement.',
            ], 403);
        }

        $data = $request->validated();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Événement mis à jour avec succès.',
            'data'    => new EventResource($event->fresh()->load('club')),
        ]);
    }

    /**
     * DELETE /api/events/{id}
     * Supprimer un événement (propriétaire du club ou admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $club  = $event->club;

        // Vérifier les droits
        if (auth()->user()->role !== 'admin' && $club->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cet événement.',
            ], 403);
        }

        // Supprimer l'image
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Événement supprimé avec succès.',
        ]);
    }

    /**
     * GET /api/events/club/{clubId}
     * Liste des événements d'un club spécifique.
     */
    public function byClub(string $clubId): JsonResponse
    {
        $events = Event::where('club_id', $clubId)
            ->with('club')
            ->orderBy('date', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => EventResource::collection($events),
            'meta'    => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'per_page'     => $events->perPage(),
                'total'        => $events->total(),
            ],
        ]);
    }
}
