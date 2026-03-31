<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\EtudiantDashboardController;
use App\Http\Controllers\EtapeTraitementController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PieceJointeController;
use App\Http\Controllers\RequeteController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TypeRequeteController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('api.token')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('role:etudiant')->group(function () {
        Route::get('etudiants/me', [EtudiantController::class, 'me']);
        Route::match(['put', 'patch'], 'etudiants/me', [EtudiantController::class, 'updateMe']);
        Route::get('dashboard/etudiant', [EtudiantDashboardController::class, 'index']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/{notification}', [NotificationController::class, 'update'])
            ->whereNumber('notification');
    });

    Route::middleware('role:agent')->group(function () {
        Route::get('dashboard/agent', [DashboardController::class, 'agent']);

        Route::middleware('agent.feature:manage_agents')->group(function () {
            Route::apiResource('agents', AgentController::class)
                ->parameters(['agents' => 'agent'])
                ->whereNumber('agent');
        });

        Route::middleware('agent.feature:manage_services')->group(function () {
            Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        });

        Route::middleware('agent.feature:manage_types')->group(function () {
            Route::apiResource('types-requetes', TypeRequeteController::class)->except(['index', 'show']);
        });

        Route::middleware('agent.feature:manage_etudiants')->group(function () {
            Route::apiResource('etudiants', EtudiantController::class)->whereNumber('etudiant');
            Route::post('etudiants/{etudiant}/compte', [EtudiantController::class, 'createCompte'])
                ->whereNumber('etudiant');
        });

        Route::middleware('agent.feature:process_etapes')->group(function () {
            Route::apiResource('etape-traitements', EtapeTraitementController::class);
            Route::post('requetes/{requete}/traiter', [RequeteController::class, 'traiter'])
                ->whereNumber('requete');
        });

        Route::middleware('agent.feature:decision_finale')->group(function () {
            Route::apiResource('decisions', DecisionController::class);
        });

        Route::apiResource('pieces-jointes', PieceJointeController::class)->except(['store']);
    });

    Route::middleware('role:agent,etudiant')->group(function () {
        Route::get('services', [ServiceController::class, 'index']);
        Route::get('services/{service}', [ServiceController::class, 'show'])->whereNumber('service');
        Route::get('types-requetes', [TypeRequeteController::class, 'index']);
        Route::get('types-requetes/{typeRequete}', [TypeRequeteController::class, 'show'])
            ->whereNumber('typeRequete');
        Route::post('pieces-jointes', [PieceJointeController::class, 'store']);
        Route::apiResource('requetes', RequeteController::class);
    });
});
