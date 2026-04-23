<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'jean.dupont@example.com')->first();
if ($user) {
    $requetes = $user->etudiant->requetes()->with('decision')->get();
    foreach ($requetes as $req) {
        echo 'Requête #' . $req->id . ' - Statut: ' . $req->statut . PHP_EOL;
        if ($req->decision) {
            echo '  Décision: ' . $req->decision->resultat . PHP_EOL;
            echo '  Motif: ' . ($req->decision->motif ?: 'AUCUN MOTIF') . PHP_EOL;
        }
        echo PHP_EOL;
    }
} else {
    echo 'Utilisateur non trouvé';
}
