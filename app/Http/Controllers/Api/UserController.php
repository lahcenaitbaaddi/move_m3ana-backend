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
     * GET /api/users
     * Liste de tous les utilisateurs (admin uniquement).
     */
    public function index(): JsonResponse
    {
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
     * Détail d'un utilisateur.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

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
        $user = User::findOrFail($id);

        $data = $request->validated();

        // Gestion de l'upload d'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
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
        $user = User::findOrFail($id);

        // Supprimer l'avatar si existant
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

        // Gestion de l'upload d'avatar
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
}
