<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StatController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes — TANGIER SPORTS COMMUNITY
|--------------------------------------------------------------------------
|
| Toutes les routes sont préfixées par /api automatiquement.
| Les routes protégées nécessitent un token Sanctum valide.
|
*/

// ─── 1. Authentification (routes publiques) ──────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Routes nécessitant une authentification
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// ─── Routes protégées (authentification + compte actif) ──────────────────
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // ─── 2. Utilisateurs ─────────────────────────────────────────────
    Route::put('users/profile', [UserController::class, 'updateProfile']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // ─── 3. Clubs ────────────────────────────────────────────────────
    Route::get('clubs/sport/{sport}', [ClubController::class, 'bySport']);
    Route::get('clubs/location/{city}', [ClubController::class, 'byLocation']);
    Route::get('clubs', [ClubController::class, 'index']);
    Route::post('clubs', [ClubController::class, 'store']);
    Route::get('clubs/{id}', [ClubController::class, 'show']);
    Route::put('clubs/{id}', [ClubController::class, 'update']);
    Route::delete('clubs/{id}', [ClubController::class, 'destroy']);

    // ─── 4. Événements ───────────────────────────────────────────────
    Route::get('events/club/{clubId}', [EventController::class, 'byClub']);
    Route::get('events', [EventController::class, 'index']);
    Route::post('events', [EventController::class, 'store']);
    Route::get('events/{id}', [EventController::class, 'show']);
    Route::put('events/{id}', [EventController::class, 'update']);
    Route::delete('events/{id}', [EventController::class, 'destroy']);

    // ─── 5. Abonnements ──────────────────────────────────────────────
    Route::post('subscriptions', [SubscriptionController::class, 'store']);
    Route::delete('subscriptions/{id}', [SubscriptionController::class, 'destroy']);
    Route::get('subscriptions/user/{userId}', [SubscriptionController::class, 'byUser']);
    Route::get('subscriptions/club/{clubId}', [SubscriptionController::class, 'byClub']);

    // ─── 6. Messages ─────────────────────────────────────────────────
    Route::post('messages', [MessageController::class, 'store']);
    Route::get('messages', [MessageController::class, 'index']);
    Route::get('messages/{id}', [MessageController::class, 'show']);
    Route::get('messages/club/{clubId}', [MessageController::class, 'byClub']);
    Route::get('messages/user/{userId}', [MessageController::class, 'byUser']);

    // ─── 7. Avis / Notes ─────────────────────────────────────────────
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::get('reviews/club/{clubId}', [ReviewController::class, 'byClub']);
    Route::put('reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);

    // ─── 8. Statistiques ─────────────────────────────────────────────
    Route::prefix('stats')->group(function () {
        Route::get('clubs', [StatController::class, 'clubs']);
        Route::get('events', [StatController::class, 'events']);
        Route::get('users', [StatController::class, 'users']);
        Route::get('dashboard', [StatController::class, 'dashboard']);
    });

    // ─── 9. Administration (admin uniquement) ────────────────────────
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::put('approve-club/{id}', [AdminController::class, 'approveClub']);
        Route::put('suspend-user/{id}', [AdminController::class, 'suspendUser']);
        Route::get('reports', [AdminController::class, 'reports']);
        Route::get('logs', [AdminController::class, 'logs']);
    });

    // ─── 10. Recherche ───────────────────────────────────────────────
    Route::prefix('search')->group(function () {
        Route::get('clubs', [SearchController::class, 'clubs']);
        Route::get('events', [SearchController::class, 'events']);
    });
});

//_______________________________________
Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API fonctionne'
    ]);
});
