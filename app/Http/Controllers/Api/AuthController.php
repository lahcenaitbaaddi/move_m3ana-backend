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
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // 🔐 ROLE FORCÉ (sécurité)
        $role = 'user';

        // 👉 si tu veux autoriser "club"
        if ($request->role === 'club') {
            $role = 'club';
        }

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => $role,
            'phone'             => $request->phone,
            'sport_preferences' => $request->sport_preferences,
            'is_active'         => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity($user->id, 'register', $request);

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
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Email ou mot de passe incorrect.', 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            return $this->error('Compte suspendu.', 403);
        }

        // 🔥 supprimer anciens tokens (optionnel mais pro)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity($user->id, 'login', $request);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('Non authentifié.', 401);
        }

        $user->currentAccessToken()?->delete();

        $this->logActivity($user->id, 'logout', request());

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'success' => true,
                'message' => 'Lien envoyé par email.',
            ])
            : $this->error('Erreur lors de l’envoi.', 400);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->update([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé.',
            ])
            : $this->error('Échec de réinitialisation.', 400);
    }

    /**
     * GET /api/auth/me
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource(auth()->user()),
        ]);
    }

    // ================================
    // 🔧 HELPERS (propre)
    // ================================

    private function logActivity($userId, $action, $request): void
    {
        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => "Action: {$action}",
            'ip_address'  => $request->ip(),
        ]);
    }

    private function error(string $message, int $code): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

}