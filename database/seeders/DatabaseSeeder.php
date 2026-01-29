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
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $servicesData = [
            ['nom_service' => 'Conseil orientation', 'type_service' => 'ConseilOrientation'],
            ['nom_service' => 'Service courrier', 'type_service' => 'Courrier'],
            ['nom_service' => 'Direction', 'type_service' => 'Direction'],
            ['nom_service' => 'Direction adjointe', 'type_service' => 'DA'],
            ['nom_service' => 'Departement', 'type_service' => 'Departement'],
            ['nom_service' => 'Cellule informatique', 'type_service' => 'CelluleInfo'],
            ['nom_service' => 'Scolarite', 'type_service' => 'Scolarite'],
        ];

        foreach ($servicesData as $data) {
            Service::firstOrCreate(['nom_service' => $data['nom_service']], $data);
        }

        $courrierService = Service::where('nom_service', 'Service courrier')->first();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'service_id' => $courrierService ? $courrierService->id : null,
            ]
        );

        $typesData = [
            ['libelle' => 'Certificat de scolarite', 'delai_cible_hrs' => 72],
            ['libelle' => 'Retrait diplome academique', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande duplicata', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande syllabus cours', 'delai_cible_hrs' => 72],
            ['libelle' => 'Demande correction infos', 'delai_cible_hrs' => 72],
            ['libelle' => 'Attestation non delivrance diplome', 'delai_cible_hrs' => 72],
            ['libelle' => 'Suspension de cours', 'delai_cible_hrs' => 72],
            ['libelle' => 'Attestation etude langue francaise', 'delai_cible_hrs' => 72],
            ['libelle' => 'Changement horaire filiere', 'delai_cible_hrs' => 72],
        ];

        foreach ($typesData as $data) {
            TypeRequete::firstOrCreate(['libelle' => $data['libelle']], $data);
        }

        $services = Service::all();
        $types = TypeRequete::all();
        $etudiants = Etudiant::factory()->count(10)->create();

        $faker = FakerFactory::create();

        $requetes = collect();
        for ($i = 0; $i < 20; $i++) {
            $requetes->push(Requete::factory()->create([
                'etudiant_id' => $etudiants->random()->id,
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

                Notification::create([
                    'etudiant_id' => $requete->etudiant_id,
                    'requete_id' => $requete->id,
                    'decision_id' => $decision->id,
                    'message' => 'Decision ' . $decision->resultat . ' pour la requete #' . $requete->id . '.',
                    'read_at' => null,
                ]);
            }

            $pieces = random_int(0, 2);
            if ($pieces > 0) {
                PieceJointe::factory()->count($pieces)->create([
                    'requete_id' => $requete->id,
                ]);
            }
        }
    }
}
