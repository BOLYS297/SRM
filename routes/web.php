<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/connexion', 'auth.login')->name('login');
Route::view('/etudiant/dashboard', 'etudiant.dashboard')->name('etudiant.dashboard');
Route::view('/requetes/depot', 'requetes.depot')->name('requetes.depot');
Route::view('/requetes/suivi', 'requetes.suivi')->name('requetes.suivi');
Route::view('/profil', 'etudiant.profil')->name('etudiant.profil');
Route::view('/agent/dashboard', 'agent.dashboard')->name('agent.dashboard');
Route::view('/agent/services', 'agent.services')->name('agent.services');
Route::view('/agent/types', 'agent.types')->name('agent.types');
Route::view('/agent/agents', 'agent.agents')->name('agent.agents');
Route::view('/agent/etudiants', 'agent.etudiants')->name('agent.etudiants');
Route::view('/agent/etapes', 'agent.historique')->name('agent.historique');
Route::view('/agent/historique', 'agent.historique')->name('agent.historique');
Route::view('/agent/decisions', 'agent.decisions')->name('agent.decisions');
