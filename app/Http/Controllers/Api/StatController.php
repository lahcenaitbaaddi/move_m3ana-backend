<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class StatController extends Controller
{
    /**
     * GET /api/stats/clubs
     * Statistiques sur les clubs.
     */
    public function clubs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_clubs'     => Club::count(),
                'approved_clubs'  => Club::approved()->count(),
                'pending_clubs'   => Club::where('is_approved', false)->count(),
                'active_clubs'    => Club::active()->count(),
                'inactive_clubs'  => Club::where('is_active', false)->count(),
                'clubs_by_sport'  => Club::approved()
                    ->selectRaw('sport, COUNT(*) as count')
                    ->groupBy('sport')
                    ->pluck('count', 'sport'),
                'clubs_by_city'   => Club::approved()
                    ->selectRaw('city, COUNT(*) as count')
                    ->groupBy('city')
                    ->pluck('count', 'city'),
            ],
        ]);
    }

    /**
     * GET /api/stats/events
     * Statistiques sur les événements.
     */
    public function events(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_events'    => Event::count(),
                'upcoming_events' => Event::where('date', '>=', now())->count(),
                'past_events'     => Event::where('date', '<', now())->count(),
                'events_by_month' => Event::selectRaw('MONTH(date) as month, YEAR(date) as year, COUNT(*) as count')
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->limit(12)
                    ->get(),
            ],
        ]);
    }

    /**
     * GET /api/stats/users
     * Statistiques sur les utilisateurs.
     */
    public function users(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_users'     => User::count(),
                'active_users'    => User::active()->count(),
                'suspended_users' => User::where('is_active', false)->count(),
                'users_by_role'   => User::selectRaw('role, COUNT(*) as count')
                    ->groupBy('role')
                    ->pluck('count', 'role'),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
        ]);
    }

    /**
     * GET /api/stats/dashboard
     * Dashboard global (admin).
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_users'         => User::count(),
                'total_clubs'         => Club::count(),
                'total_events'        => Event::count(),
                'total_subscriptions' => Subscription::count(),
                'total_messages'      => Message::count(),
                'total_reviews'       => Review::count(),
                'pending_clubs'       => Club::where('is_approved', false)->count(),
                'suspended_users'     => User::where('is_active', false)->count(),
                'upcoming_events'     => Event::where('date', '>=', now())->count(),
                'recent_users'        => User::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'role', 'created_at']),
                'recent_clubs'        => Club::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'sport', 'city', 'is_approved', 'created_at']),
            ],
        ]);
    }
}
