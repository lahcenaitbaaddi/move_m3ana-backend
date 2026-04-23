<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * POST /api/messages
     * Envoyer un message à un club.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            'user_id' => auth()->id(),
            'club_id' => $request->club_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé avec succès.',
            'data'    => new MessageResource($message->load(['user', 'club'])),
        ], 201);
    }

    /**
     * GET /api/messages
     * Liste des messages de l'utilisateur connecté.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        // Si l'utilisateur est un club, afficher les messages reçus par son club
        if ($user->isClub() && $user->club) {
            $messages = Message::where('club_id', $user->club->id)
                ->with(['user', 'club'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // Sinon, afficher les messages envoyés par l'utilisateur
            $messages = Message::where('user_id', $user->id)
                ->with(['user', 'club'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return response()->json([
            'success' => true,
            'data'    => MessageResource::collection($messages),
            'meta'    => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * GET /api/messages/{id}
     * Détail d'un message.
     */
    public function show(string $id): JsonResponse
    {
        $message = Message::with(['user', 'club'])->findOrFail($id);

        // Vérifier les droits d'accès
        $user = auth()->user();
        $isOwner = $message->user_id === $user->id;
        $isClubOwner = $user->club && $message->club_id === $user->club->id;
        $isAdmin = $user->isAdmin();

        if (!$isOwner && !$isClubOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à consulter ce message.',
            ], 403);
        }

        // Marquer comme lu si le club consulte le message
        if ($isClubOwner && !$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'data'    => new MessageResource($message),
        ]);
    }

    /**
     * GET /api/messages/club/{clubId}
     * Liste des messages d'un club.
     */
    public function byClub(string $clubId): JsonResponse
    {
        $messages = Message::where('club_id', $clubId)
            ->with(['user', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => MessageResource::collection($messages),
            'meta'    => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * GET /api/messages/user/{userId}
     * Liste des messages d'un utilisateur.
     */
    public function byUser(string $userId): JsonResponse
    {
        $messages = Message::where('user_id', $userId)
            ->with(['user', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => MessageResource::collection($messages),
            'meta'    => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }
}
