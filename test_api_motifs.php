<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simuler une requête API pour le dashboard étudiant
$user = \App\Models\User::where('email', 'jean.dupont@example.com')->first();

if ($user) {
    // Simuler la logique du contrôleur
    $recents = \App\Models\Requete::with(['typeRequete', 'decision'])
        ->where('etudiant_id', $user->etudiant_id)
        ->orderByDesc('date_depot')
        ->limit(6)
        ->get();

    echo "Requêtes récentes pour l'étudiant Jean Dupont:\n\n";

    foreach ($recents as $requete) {
        echo "Requête #" . $requete->id . " - " . $requete->objet . "\n";
        echo "  Statut: " . $requete->statut . "\n";

        if ($requete->decision) {
            echo "  Décision: " . $requete->decision->resultat . "\n";
            echo "  Motif: " . ($requete->decision->motif ?: 'AUCUN MOTIF') . "\n";
            echo "  Date décision: " . $requete->decision->date_decision . "\n";
        } else {
            echo "  Aucune décision\n";
        }

        echo "\n";
    }

    // Vérifier aussi l'API des requêtes
    echo "=== Test de l'API requetes ===\n\n";

    $requetes = \App\Models\Requete::with(['typeRequete', 'decision', 'etudiant', 'piecesJointes'])
        ->where('etudiant_id', $user->etudiant_id)
        ->orderByDesc('date_depot')
        ->get();

    $rejetee = $requetes->where('statut', 'rejetee')->first();

    if ($rejetee && $rejetee->decision) {
        echo "Exemple de requête rejetée:\n";
        echo "ID: " . $rejetee->id . "\n";
        echo "Objet: " . $rejetee->objet . "\n";
        echo "Motif de rejet: " . ($rejetee->decision->motif ?: 'AUCUN MOTIF') . "\n";
    }

} else {
    echo 'Utilisateur non trouvé';
}
