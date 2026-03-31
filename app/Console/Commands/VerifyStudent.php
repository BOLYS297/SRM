<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Console\Command;

class VerifyStudent extends Command
{
    protected $signature = 'verify:student';
    protected $description = 'Vérifie la création de l\'étudiant fictif';

    public function handle()
    {
        $this->info('=== VÉRIFICATION ÉTUDIANT FICTIF ==='.PHP_EOL);

        // Vérifier l'étudiant
        $etudiant = Etudiant::where('matricule', 'IUT0001')->first();
        if ($etudiant) {
            $this->line('✓ Étudiant fictif trouvé:');
            $this->line('  - Matricule: ' . $etudiant->matricule);
            $this->line('  - Nom: ' . $etudiant->nom . ' ' . $etudiant->prenom);
            $this->line('  - Email: ' . $etudiant->email);
            $this->line('  - Date de naissance: ' . $etudiant->date_naissance);
        } else {
            $this->error('✗ Erreur: Étudiant fictif non trouvé');
        }

        $this->line('');

        // Vérifier l'utilisateur
        $user = User::where('email', 'jean.dupont@example.com')->first();
        if ($user) {
            $this->line('✓ Compte utilisateur trouvé:');
            $this->line('  - Email: ' . $user->email);
            $this->line('  - Nom: ' . $user->name);
            $this->line('  - Rôle: ' . $user->role);
            $this->line('  - Lié à l\'étudiant ID: ' . $user->etudiant_id);
        } else {
            $this->error('✗ Erreur: Compte utilisateur non trouvé');
        }

        $this->line('');

        // Vérifier les requêtes de l'étudiant
        if ($etudiant) {
            $requetes = $etudiant->requetes()->get();
            $this->line('✓ Requêtes de l\'étudiant: ' . $requetes->count());
            foreach ($requetes as $req) {
                $this->line('  - ID: ' . $req->id . ' | Statut: ' . $req->statut . ' | Type: ' . $req->typeRequete->libelle);
            }
        } else {
            $this->error('✗ Impossible de récupérer les requêtes');
        }

        $this->line('');
        $this->info('=== RÉSUMÉ ===');
        $this->line('Identifiants de connexion:');
        $this->line('  Email: jean.dupont@example.com');
        $this->line('  Mot de passe: Password123!');
        $this->line('');
        $this->info('Vous pouvez maintenant vous connecter et naviguer en tant qu\'étudiant!');
    }
}
