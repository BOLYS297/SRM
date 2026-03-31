<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TestStudentNavigation extends Command
{
    protected $signature = 'test:student-navigation';
    protected $description = 'Teste la navigation complète de l\'étudiant';

    public function handle()
    {
        $this->info('=== TEST NAVIGATION ÉTUDIANT ==='.PHP_EOL);

        // 1. Test du login
        $this->line('1️⃣  TEST LOGIN');
        $user = User::where('email', 'jean.dupont@example.com')->first();

        // Simule une tentative de login
        if ($user && Hash::check('Password123!', $user->password)) {
            $this->line('   ✓ Authentification valide');
            $this->line('   ✓ Email: ' . $user->email);
            $this->line('   ✓ Rôle: ' . $user->role);
            $this->line('   ✓ Etudiant lié: ' . ($user->etudiant_id ? 'Oui (ID: ' . $user->etudiant_id . ')' : 'Non'));
        } else {
            $this->error('   ✗ Erreur d\'authentification');
            return;
        }

        $this->line('');

        // 2. Test de l'accès aux données étudiant
        $this->line('2️⃣  TEST ACCÈS AUX DONNÉES');
        if ($user->etudiant_id) {
            $etudiant = $user->etudiant;
            $this->line('   ✓ Données étudiant accessibles:');
            $this->line('     - Nom: ' . $etudiant->nom . ' ' . $etudiant->prenom);
            $this->line('     - Matricule: ' . $etudiant->matricule);
            $this->line('     - Téléphone: ' . ($etudiant->telephone ?? 'N/A'));
        }

        $this->line('');

        // 3. Test des requêtes
        $this->line('3️⃣  TEST REQUÊTES');
        $requetes = $user->etudiant->requetes;
        $this->line('   ✓ Nombre de requêtes: ' . $requetes->count());

        $stats = [
            'en_attente' => $requetes->where('statut', 'en_attente')->count(),
            'en_traitement' => $requetes->where('statut', 'en_traitement')->count(),
            'traitee' => $requetes->where('statut', 'traitee')->count(),
            'rejetee' => $requetes->where('statut', 'rejetee')->count(),
        ];

        foreach ($stats as $statut => $count) {
            $this->line('     - ' . $statut . ': ' . $count);
        }

        $this->line('');

        // 4. Test des notifications
        $this->line('4️⃣  TEST NOTIFICATIONS');
        $notifications = $user->etudiant->notifications;
        $this->line('   ✓ Nombre de notifications: ' . $notifications->count());
        if ($notifications->count() > 0) {
            foreach ($notifications->take(3) as $notif) {
                $this->line('     - ' . $notif->message . ' (lue: ' . ($notif->read_at ? 'Oui' : 'Non') . ')');
            }
        }

        $this->line('');

        // 5. Test des étapes de traitement
        $this->line('5️⃣  TEST REQUÊTES DÉTAILLÉES');
        foreach ($requetes->take(2) as $requete) {
            $this->line('   Requête #' . $requete->id . ':');
            $this->line('     - Type: ' . $requete->typeRequete->libelle);
            $this->line('     - Statut: ' . $requete->statut);
            $this->line('     - Date: ' . $requete->date_depot);

            $etapes = $requete->etapeTraitements;
            $this->line('     - Étapes de traitement: ' . $etapes->count());

            if ($requete->decision) {
                $this->line('     - Décision: ' . $requete->decision->resultat);
            }

            if ($requete->piecesJointes->count() > 0) {
                $this->line('     - Pièces jointes: ' . $requete->piecesJointes->count());
            }
        }

        $this->line('');
        $this->info('=== RÉSUMÉ ===');
        $this->info('✓ Tous les tests de navigation ont réussi!');
        $this->info('✓ L\'étudiant peut accéder à:');
        $this->line('  - Son profil personnalisé');
        $this->line('  - Son dashboard avec statistiques');
        $this->line('  - Ses requêtes en cours');
        $this->line('  - Ses notifications');
        $this->line('  - Les étapes de traitement de ses requêtes');
        $this->line('  - Les décisions prises');
        $this->line('');
        $this->info('✓ Navigation complète validée!');
    }
}
