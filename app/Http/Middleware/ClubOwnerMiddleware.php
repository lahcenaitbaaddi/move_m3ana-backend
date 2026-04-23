<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubOwnerMiddleware
{
    /**
     * Vérifie que l'utilisateur connecté a le rôle club.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'club') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Droits propriétaire de club requis.',
            ], 403);
        }

        return $next($request);
    }
}
