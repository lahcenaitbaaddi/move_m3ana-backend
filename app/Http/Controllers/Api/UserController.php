<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Vérifie si l'utilisateur est admin
     */
    private function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * GET /api/users
     * Liste de tous les utilisateurs (admin uniquement).
     */
    public function index(): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $users = User::paginate(15);

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users),
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * GET /api/users/{id}
     * Voir un utilisateur (admin ou lui-même).
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (auth()->id() != $id && !$this->isAdmin()) {
            return $this->forbidden();
        }

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * PUT /api/users/{id}
     * Modifier un utilisateur (admin uniquement).
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $user = User::findOrFail($id);
        $data = $request->validated();

        // 🔐 empêcher modification du role par erreur
        unset($data['role']);

        // Gestion avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès.',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Supprimer un utilisateur (admin uniquement).
     */
    public function destroy(string $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $user = User::findOrFail($id);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès.',
        ]);
    }

    /**
     * PUT /api/users/profile
     * Modifier son propre profil.
     */
    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        // 🔐 empêcher modification du role
        unset($data['role']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Réponse standard pour accès interdit
     */
    private function forbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Accès non autorisé.',
        ], 403);
    }
}