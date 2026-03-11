<?php

use App\Http\Controllers\AtelierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\NetworkingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — LinQora
|--------------------------------------------------------------------------
| Auth   : Laravel Sanctum (Bearer token)
| Roles  : super_admin | admin_entreprise | gestionnaire | participant | operateur_scan
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════════════════════
// PUBLIC — sans authentification
// ═══════════════════════════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Landing page publique d'un événement (slug)
Route::get('/evenements/slug/{slug}', [EvenementController::class, 'showBySlug']);

// Inscription publique d'un participant
Route::post('/evenements/{evenement}/inscrire', [InscriptionController::class, 'inscrire']);

// Lookup QR code (app scan mobile)
Route::get('/inscriptions/qr/{code}', [InscriptionController::class, 'parCodeQr']);

// ═══════════════════════════════════════════════════════════════════════════════
// PROTÉGÉ — authentification requise (Sanctum)
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ─────────────────────────────────────────────────────────────────
    Route::post('/auth/logout',  [AuthController::class, 'logout']);
    Route::get('/auth/me',       [AuthController::class, 'me']);
    Route::put('/auth/profil',   [AuthController::class, 'updateProfil']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);

    // ── Super Admin ───────────────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('entreprises', EntrepriseController::class);
        Route::get('entreprises/{entreprise}/stats', [EntrepriseController::class, 'stats']);
        Route::get('super-admin/stats-globales',     [EntrepriseController::class, 'statsGlobales']);
    });

    // ── Gestion Utilisateurs ──────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin_entreprise')->group(function () {
        Route::apiResource('utilisateurs', UtilisateurController::class);
        Route::patch('utilisateurs/{utilisateur}/toggle-actif',    [UtilisateurController::class, 'toggleActif']);
        Route::patch('utilisateurs/{utilisateur}/reset-password',  [UtilisateurController::class, 'resetPassword']);
    });

    // ── Événements — écriture ─────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin_entreprise,gestionnaire')->group(function () {
        Route::post('evenements',                       [EvenementController::class, 'store']);
        Route::put('evenements/{evenement}',            [EvenementController::class, 'update']);
        Route::delete('evenements/{evenement}',         [EvenementController::class, 'destroy']);
        Route::patch('evenements/{evenement}/statut',   [EvenementController::class, 'changerStatut']);
        Route::get('evenements/{evenement}/dashboard',  [EvenementController::class, 'dashboard']);

        // Inscriptions — gestion
        Route::get('evenements/{evenement}/inscriptions',      [InscriptionController::class, 'index']);
        Route::get('inscriptions/{inscription}',               [InscriptionController::class, 'show']);
        Route::patch('inscriptions/{inscription}/valider',     [InscriptionController::class, 'valider']);
        Route::patch('inscriptions/{inscription}/annuler',     [InscriptionController::class, 'annuler']);
        Route::patch('inscriptions/{inscription}/paiement',    [InscriptionController::class, 'marquerPaye']);

        // Ateliers
        Route::get('evenements/{evenement}/ateliers',   [AtelierController::class, 'index']);
        Route::post('evenements/{evenement}/ateliers',  [AtelierController::class, 'store']);
        Route::get('ateliers/{atelier}',                [AtelierController::class, 'show']);
        Route::put('ateliers/{atelier}',                [AtelierController::class, 'update']);
        Route::delete('ateliers/{atelier}',             [AtelierController::class, 'destroy']);
        Route::post('ateliers/{atelier}/speakers/sync', [AtelierController::class, 'syncSpeakers']);

        // Speakers
        Route::apiResource('speakers', SpeakerController::class);

        // Pointage — statistiques
        Route::get('evenements/{idEvenement}/pointage/stats', [PointageController::class, 'statsPresence']);
        Route::get('evenements/{idEvenement}/absents',        [PointageController::class, 'listeAbsents']);
        Route::get('ateliers/{idAtelier}/pointage/stats',     [PointageController::class, 'statsAtelier']);

        // Exports
        Route::get('evenements/{evenement}/export/excel',  [ExportController::class, 'exportExcel']);
        Route::get('evenements/{evenement}/export/pdf',    [ExportController::class, 'exportPdf']);
        Route::get('inscriptions/{inscription}/badge',     [ExportController::class, 'exportBadge']);
    });

    // ── Événements — lecture (tous les connectés) ─────────────────────────────
    Route::get('evenements',             [EvenementController::class, 'index']);
    Route::get('evenements/{evenement}', [EvenementController::class, 'show']);

    // ── Pointage QR (opérateur + gestionnaire + admin) ────────────────────────
    Route::middleware('role:super_admin,admin_entreprise,gestionnaire,operateur_scan')->group(function () {
        Route::post('pointage/evenement', [PointageController::class, 'scanEvenement']);
        Route::post('pointage/atelier',   [PointageController::class, 'scanAtelier']);
        Route::get('pointage/historique', [PointageController::class, 'historique']);
    });

    // ── Networking ────────────────────────────────────────────────────────────
    Route::prefix('networking')->group(function () {
        Route::post('evenements/{evenement}/profil',      [NetworkingController::class, 'creerOuMettreAJourProfil']);
        Route::get('evenements/{evenement}/mon-profil',   [NetworkingController::class, 'monProfil']);
        Route::delete('evenements/{evenement}/profil',    [NetworkingController::class, 'desactiverProfil']);
        Route::get('evenements/{evenement}/participants', [NetworkingController::class, 'listeParticipants']);
        Route::get('profils/{idProfil}',                  [NetworkingController::class, 'voirProfil']);
        Route::post('rencontres',                         [NetworkingController::class, 'proposerRencontre']);
        Route::patch('rencontres/{rencontre}/repondre',   [NetworkingController::class, 'repondreRencontre']);
        Route::patch('rencontres/{rencontre}/annuler',    [NetworkingController::class, 'annulerRencontre']);
        Route::post('rencontres/{rencontre}/feedback',    [NetworkingController::class, 'ajouterFeedback']);
        Route::get('mes-rencontres',                      [NetworkingController::class, 'mesRencontres']);
    });

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/',                      [NotificationController::class, 'index']);
        Route::get('/non-lues',              [NotificationController::class, 'nonLues']);
        Route::patch('/{notification}/lire', [NotificationController::class, 'marquerLu']);
        Route::patch('/tout-lire',           [NotificationController::class, 'toutMarquerLu']);
        Route::delete('/{notification}',     [NotificationController::class, 'destroy']);
    });
});
