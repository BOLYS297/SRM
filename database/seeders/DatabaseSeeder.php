<?php

namespace Database\Seeders;

use App\Models\Decision;
use App\Models\EtapeTraitement;
use App\Models\Etudiant;
use App\Models\Notification;
use App\Models\PieceJointe;
use App\Models\Requete;
use App\Models\Service;
use App\Models\TypeRequete;
use App\Models\User;
use App\Support\FiliereCatalog;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $baseServicesData = [
            ['nom_service' => 'Conseil orientation', 'type_service' => 'ConseilOrientation', 'code_departement' => null],
            ['nom_service' => 'Service courrier', 'type_service' => 'Courrier', 'code_departement' => null],
            ['nom_service' => 'Direction', 'type_service' => 'Direction', 'code_departement' => null],
            ['nom_service' => 'Direction adjointe', 'type_service' => 'DA', 'code_departement' => null],
            ['nom_service' => 'Enseignant', 'type_service' => 'Enseignant', 'code_departement' => null],
            ['nom_service' => 'Cellule informatique', 'type_service' => 'CelluleInfo', 'code_departement' => null],
            ['nom_service' => 'Scolarite', 'type_service' => 'Scolarite', 'code_departement' => null],
        ];

        foreach ($baseServicesData as $data) {
            Service::updateOrCreate(
                ['nom_service' => $data['nom_service']],
                $data
            );
        }

        foreach (FiliereCatalog::all() as $filiere) {
            Service::updateOrCreate(
                ['nom_service' => 'Departement ' . $filiere['code'] . ' - ' . $filiere['libelle']],
                [
                    'type_service' => 'Departement',
                    'code_departement' => $filiere['code'],
                ]
            );


        $servicesByName = Service::query()->pluck('id', 'nom_service');
        $departements = Service::query()
            ->where('type_service', 'Departement')
            ->whereNotNull('code_departement')
            ->orderBy('code_departement')
            ->get();

        $agentFixtures = [
            ['name' => 'Agent Conseil Orientation', 'email' => 'conseil@iutdouala.test', 'service' => 'Conseil orientation'],
            ['name' => 'Agent Courrier', 'email' => 'courrier@iutdouala.test', 'service' => 'Service courrier'],
            ['name' => 'Agent Direction', 'email' => 'direction@iutdouala.test', 'service' => 'Direction'],
            ['name' => 'Agent Direction Adjointe', 'email' => 'da@iutdouala.test', 'service' => 'Direction adjointe'],
            ['name' => 'Agent Enseignant', 'email' => 'enseignant@iutdouala.test', 'service' => 'Enseignant'],
            ['name' => 'Agent Cellule Info', 'email' => 'cellule@iutdouala.test', 'service' => 'Cellule informatique'],
            ['name' => 'Agent Scolarite', 'email' => 'scolarite@iutdouala.test', 'service' => 'Scolarite'],
        ];

        foreach ($agentFixtures as $fixture) {
            User::updateOrCreate(
                ['email' => $fixture['email']],
                [
                    'name' => $fixture['name'],
                    'password' => Hash::make('Password123!'),
                    'role' => 'agent',
                    'service_id' => $servicesByName[$fixture['service']] ?? null,
                    'etudiant_id' => null,
                    'api_token' => null,
                ]
            );
        }

        foreach ($departements as $departement) {
            $code = strtolower((string) $departement->code_departement);
            User::updateOrCreate(
                ['email' => 'departement.' . $code . '@iutdouala.test'],
                [
                    'name' => 'Agent Departement ' . $departement->code_departement,
                    'password' => Hash::make('Password123!'),
                    'role' => 'agent',
                    'service_id' => $departement->id,
                    'etudiant_id' => null,
                    'api_token' => null,
                ]
            );
        }

        User::where('email', 'departement@iutdouala.test')->delete();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'service_id' => $servicesByName['Service courrier'] ?? null,
                'etudiant_id' => null,
                'api_token' => null,
            ]
        );

        $typesData = [
            ['libelle' => 'Certificat de scolarite', 'delai_cible_hrs' => 72],
            ['libelle' => 'Retrait de diplome academique', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de duplicata', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de syllabus de cours', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de correction (nom, prenom, date de naissance, etc)', 'delai_cible_hrs' => 72],
            ['libelle' => "Demande d'attestation de non delivrance de diplome", 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de suspension de cours', 'delai_cible_hrs' => 72],
            ['libelle' => "Demande d'attestation d'etude en langue francaise", 'delai_cible_hrs' => 72],
            ['libelle' => "Demande de changement d'horaire", 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de changement de filiere', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande de correction de notes', 'delai_cible_hrs' => 72],
            ['libelle' => 'Absence de note de CC', 'delai_cible_hrs' => 72],
            ['libelle' => 'Absence de nom sur les PV', 'delai_cible_hrs' => 72],
            ['libelle' => 'Absence de note sur les PV', 'delai_cible_hrs' => 72],
        ];

        $legacyToCanonical = [
            'Retrait diplome academique' => 'Retrait de diplome academique',
            'Demande duplicata' => 'Demande de duplicata',
            'Demande syllabus cours' => 'Demande de syllabus de cours',
            'Demande correction infos' => 'Demande de correction (nom, prenom, date de naissance, etc)',
            'Attestation non delivrance diplome' => "Demande d'attestation de non delivrance de diplome",
            'Suspension de cours' => 'Demande de suspension de cours',
            'Attestation etude langue francaise' => "Demande d'attestation d'etude en langue francaise",
            'Changement horaire filiere' => "Demande de changement d'horaire",
        ];

        foreach ($legacyToCanonical as $legacyLabel => $canonicalLabel) {
            $legacyType = TypeRequete::where('libelle', $legacyLabel)->first();
            if (!$legacyType) {
                continue;
            }

            $canonicalType = TypeRequete::where('libelle', $canonicalLabel)->first();
            if ($canonicalType && $canonicalType->id !== $legacyType->id) {
                Requete::where('type_requete_id', $legacyType->id)->update([
                    'type_requete_id' => $canonicalType->id,
                ]);
                $legacyType->delete();
                continue;
            }

            $legacyType->libelle = $canonicalLabel;
            $legacyType->delai_cible_hrs = 72;
            $legacyType->save();
        }

        foreach ($typesData as $data) {
            TypeRequete::updateOrCreate(
                ['libelle' => $data['libelle']],
                ['delai_cible_hrs' => $data['delai_cible_hrs']]
            );
        }

        $services = Service::all();
        $types = TypeRequete::all();
        $faker = FakerFactory::create();

        $etudiantFictif = Etudiant::updateOrCreate(
            ['matricule' => 'IUT0001'],
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'date_naissance' => '2003-05-15',
                'telephone' => '+33612345678',
                'email' => 'jean.dupont@example.com',
                'filiere' => 'GI',
            ]
        );

        User::updateOrCreate(
            ['email' => 'jean.dupont@example.com'],
            [
                'name' => 'Jean Dupont',
                'password' => Hash::make('Password123!'),
                'role' => 'etudiant',
                'etudiant_id' => $etudiantFictif->id,
                'service_id' => null,
                'api_token' => null,
            ]
        );

        $etudiants = Etudiant::factory()->count(10)->create();
        $requetes = collect();

        $statuts = ['en_attente', 'en_traitement', 'traitee', 'rejetee'];
        $typesRequetes = $types->all();

        foreach ($statuts as $index => $statut) {
            $requetes->push(Requete::factory()->create([
                'etudiant_id' => $etudiantFictif->id,
                'type_requete_id' => $typesRequetes[$index % count($typesRequetes)]->id,
                'filiere_depot' => $etudiantFictif->filiere,
                'statut' => $statut,
                'objet' => match ($statut) {
                    'en_attente' => 'Demande en attente de traitement',
                    'en_traitement' => 'Demande actuellement en cours de traitement',
                    'traitee' => 'Demande acceptee et traitee',
                    'rejetee' => 'Demande rejetee',
                },
            ]));
        }

        for ($i = 0; $i < 16; $i++) {
            $etudiant = $etudiants->random();
            $requetes->push(Requete::factory()->create([
                'etudiant_id' => $etudiant->id,
                'filiere_depot' => $etudiant->filiere,
                'type_requete_id' => $types->random()->id,
            ]));
        }

        foreach ($requetes as $requete) {
            $steps = random_int(2, 6);
            for ($ordre = 1; $ordre <= $steps; $ordre++) {
                EtapeTraitement::factory()->create([
                    'requete_id' => $requete->id,
                    'service_id' => $services->random()->id,
                    'ordre_etape' => $ordre,
                ]);
            }

            if (in_array($requete->statut, ['traitee', 'rejetee'], true)) {
                $decision = Decision::factory()->create([
                    'requete_id' => $requete->id,
                    'service_id' => $services->random()->id,
                    'resultat' => $requete->statut === 'traitee' ? 'favorable' : 'defavorable',
                    'date_decision' => $faker->dateTimeBetween($requete->date_depot, 'now'),
                ]);

                Notification::updateOrCreate(
                    ['decision_id' => $decision->id],
                    [
                        'etudiant_id' => $requete->etudiant_id,
                        'requete_id' => $requete->id,
                        'message' => 'Decision ' . $decision->resultat . ' pour la requete #' . $requete->id . '.',
                        'read_at' => null,
                    ]
                );
            }

            $pieces = random_int(0, 2);
            if ($pieces > 0) {
                PieceJointe::factory()->count($pieces)->create([
                    'requete_id' => $requete->id,
                ]);
            }
        }

        foreach ($services as $service) {
            $requete = Requete::factory()->create([
                'etudiant_id' => $etudiantFictif->id,
                'type_requete_id' => $types->random()->id,
                'filiere_depot' => $etudiantFictif->filiere,
                'statut' => 'en_traitement',
                'objet' => 'Dossier demo - ' . $service->nom_service,
            ]);

            if ($service->isCourrier()) {
                continue;
            }

            EtapeTraitement::factory()->create([
                'requete_id' => $requete->id,
                'service_id' => $service->id,
                'ordre_etape' => 1,
                'action' => 'traitement',
                'date_sortie' => null,
            ]);
        }
    }
}

