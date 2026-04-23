<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Inscription d'un nouvel utilisateur.
     */
    // public function register(RegisterRequest $request): JsonResponse
    // {
    //     $user = User::create([
    //         'name'     => $request->name,
    //         'email'    => $request->email,
    //         'password' => Hash::make($request->password),
    //         'role'     => $request->role ?? 'user',
    //         'phone'    => $request->phone,
    //     ]);

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     // Log de l'activité
    //     ActivityLog::create([
    //         'user_id'     => $user->id,
    //         'action'      => 'register',
    //         'description' => "Nouvel utilisateur inscrit : {$user->email}",
    //         'ip_address'  => $request->ip(),
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Inscription réussie.',
    //         'data'    => [
    //             'user'  => new UserResource($user),
    //             'token' => $token,
    //         ],
    //     ], 201);
    // }
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => $request->role ?? 'user',
            'phone'             => $request->phone,
            'sport_preferences' => $request->sport_preferences,
            'is_active'         => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'register',
            'description' => "Nouvel utilisateur inscrit : {$user->email}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }
    /**
     * POST /api/auth/login
     * Connexion d'un utilisateur.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Vérifier si le compte est actif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log de l'activité
        ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'login',
            'description' => "Connexion de l'utilisateur : {$user->email}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }
//_________________________________________________________________________________
    /**
     * POST /api/auth/logout
     * Déconnexion (révoque le token courant).
     */
    // public function logout(): JsonResponse
    // {
    //     $user = auth()->user();
    //     $user->currentAccessToken()->delete();

    //     // Log de l'activité
    //     ActivityLog::create([
    //         'user_id'     => $user->id,
    //         'action'      => 'logout',
    //         'description' => "Déconnexion de l'utilisateur : {$user->email}",
    //         'ip_address'  => request()->ip(),
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Déconnexion réussie.',
    //     ]);
    // }
    //_________________________________________________________________________________
    public function logout(): JsonResponse
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Non authentifié.',
        ], 401);
    }

    $token = $user->currentAccessToken();

    if ($token) {
        $token->delete();
    }

    ActivityLog::create([
        'user_id'     => $user->id,
        'action'      => 'logout',
        'description' => "Déconnexion de l'utilisateur : {$user->email}",
        'ip_address'  => request()->ip(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Déconnexion réussie.',
    ]);
}

    /**
     * POST /api/auth/forgot-password
     * Envoi d'un lien de réinitialisation de mot de passe.
     */

//      public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
// {
//     $status = Password::sendResetLink(
//         $request->only('email')
//     );

//     return response()->json([
//         'status' => $status
//     ]);
// }
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Un lien de réinitialisation a été envoyé à votre email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Impossible d\'envoyer le lien de réinitialisation.',
        ], 400);
    }

    /**
     * POST /api/auth/reset-password
     * Réinitialisation du mot de passe via token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Échec de la réinitialisation du mot de passe.',
        ], 400);
    }

    /**
     * GET /api/auth/me
     * Retourne les informations de l'utilisateur connecté.
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource(auth()->user()),
        ]);
    }
}
