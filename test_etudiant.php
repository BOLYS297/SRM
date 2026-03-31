<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->handle($request = $app->make('Illuminate\Http\Request'));

use App\Models\User;
use App\Models\Etudiant;
use App\Models\Requete;

echo "=== VÉRIFICATION ÉTUDIANT FICTIF ===\n\n";

// Vérifier l'étudiant
$etudiant = Etudiant::where('matricule', 'IUT0001')->first();
if ($etudiant) {
    echo "✓ Étudiant fictif trouvé:\n";
    echo "  - Matricule: " . $etudiant->matricule . "\n";
    echo "  - Nom: " . $etudiant->nom . " " . $etudiant->prenom . "\n";
    echo "  - Email: " . $etudiant->email . "\n";
    echo "  - Date de naissance: " . $etudiant->date_naissance . "\n";
} else {
    echo "✗ Erreur: Étudiant fictif non trouvé\n";
}

echo "\n";

// Vérifier l'utilisateur
$user = User::where('email', 'jean.dupont@example.com')->first();
if ($user) {
    echo "✓ Compte utilisateur trouvé:\n";
    echo "  - Email: " . $user->email . "\n";
    echo "  - Nom: " . $user->name . "\n";
    echo "  - Rôle: " . $user->role . "\n";
    echo "  - Lié à l'étudiant ID: " . $user->etudiant_id . "\n";
} else {
    echo "✗ Erreur: Compte utilisateur non trouvé\n";
}

echo "\n";

// Vérifier les requêtes de l'étudiant
if ($etudiant) {
    $requetes = $etudiant->requetes()->get();
    echo "✓ Requêtes de l'étudiant: " . $requetes->count() . "\n";
    foreach ($requetes as $req) {
        echo "  - ID: " . $req->id . " | Statut: " . $req->statut . " | Type: " . $req->typeRequete->libelle . "\n";
    }
} else {
    echo "✗ Impossible de récupérer les requêtes\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "Identifiants de connexion:\n";
echo "  - Email: jean.dupont@example.com\n";
echo "  - Mot de passe: Password123!\n\n";

$app->terminate($request, $kernel->handle($request));
