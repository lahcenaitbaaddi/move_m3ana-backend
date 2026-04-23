<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * Pour une API REST, on retourne null pour forcer une réponse JSON 401.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API uniquement : toujours retourner null (pas de redirection)
        return null;
    }
}
