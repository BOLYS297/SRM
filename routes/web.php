<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login');
Route::view('/connexion', 'auth.login')->name('login');
Route::view('/etudiant/dashboard', 'etudiant.dashboard');
Route::view('/requetes/depot', 'requetes.depot');
Route::view('/requetes/suivi', 'requetes.suivi');
Route::view('/profil', 'etudiant.profil');
Route::view('/agent/dashboard', 'agent.dashboard');
Route::view('/agent/services', 'agent.services');
Route::view('/agent/types', 'agent.types');
Route::view('/agent/agents', 'agent.agents');
Route::view('/agent/etudiants', 'agent.etudiants');
Route::view('/agent/etapes', 'agent.etapes');
Route::view('/agent/decisions', 'agent.decisions');
