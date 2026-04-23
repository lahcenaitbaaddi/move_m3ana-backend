<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Http\Resources\EventResource;
use App\Models\Club;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * GET /api/search/clubs
     * Recherche avancée de clubs.
     * Query params: sport, city, query, min_rating
     * Exemple: /api/search/clubs?sport=football&city=tanger
     */
    public function clubs(Request $request): JsonResponse
    {
        $query = Club::approved()->active()->with('user');

        // Recherche par sport
        if ($request->filled('sport')) {
            $query->bySport($request->sport);
        }

        // Recherche par ville
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        // Recherche par nom ou description (mot-clé)
        if ($request->filled('query')) {
            $searchTerm = $request->query('query');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        $clubs = $query->paginate(15);

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
     * GET /api/search/events
     * Recherche avancée d'événements.
     * Query params: query, club_id, from, to, location
     */
    public function events(Request $request): JsonResponse
    {
        $query = Event::with('club');

        // Recherche par titre ou description
        if ($request->filled('query')) {
            $searchTerm = $request->query('query');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtrer par club
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filtrer par date de début
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        // Filtrer par date de fin
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // Filtrer par location
        if ($request->filled('location')) {
            $query->where('location', 'LIKE', "%{$request->location}%");
        }

        $events = $query->orderBy('date', 'asc')->paginate(15);

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
