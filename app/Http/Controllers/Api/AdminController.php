<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ActivityLogResource;
use App\Models\Club;
use App\Models\User;
use App\Models\Report;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * PUT /api/admin/approve-club/{id}
     * Approuver (ou rejeter) un club.
     */
    public function approveClub(Request $request, string $id): JsonResponse
    {
        $club = Club::findOrFail($id);

        $approved = $request->input('approved', true);
        $club->update(['is_approved' => $approved]);

        // Log de l'activité
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $approved ? 'club_approved' : 'club_rejected',
            'description' => ($approved ? 'Club approuvé' : 'Club rejeté') . " : {$club->name} (ID: {$club->id})",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $approved
                ? 'Club approuvé avec succès.'
                : 'Club rejeté.',
            'data'    => new ClubResource($club->fresh()),
        ]);
    }

    /**
     * PUT /api/admin/suspend-user/{id}
     * Suspendre ou réactiver un utilisateur.
     */
    public function suspendUser(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Empêcher la suspension d'un admin
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de suspendre un administrateur.',
            ], 403);
        }

        $suspend = $request->input('suspend', true);
        $user->update(['is_active' => !$suspend]);

        // Si suspendu, révoquer tous les tokens
        if ($suspend) {
            $user->tokens()->delete();
        }

        // Log de l'activité
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $suspend ? 'user_suspended' : 'user_reactivated',
            'description' => ($suspend ? 'Utilisateur suspendu' : 'Utilisateur réactivé') . " : {$user->email} (ID: {$user->id})",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $suspend
                ? 'Utilisateur suspendu avec succès.'
                : 'Utilisateur réactivé avec succès.',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * GET /api/admin/reports
     * Liste des signalements.
     */
    public function reports(Request $request): JsonResponse
    {
        $query = Report::with('reporter');

        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $reports,
        ]);
    }

    /**
     * GET /api/admin/logs
     * Liste des logs d'activité.
     */
    public function logs(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user');

        // Filtrer par action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filtrer par utilisateur
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrer par date
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => ActivityLogResource::collection($logs),
            'meta'    => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}
