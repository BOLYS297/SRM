<?php

namespace Database\Factories;

use App\Models\Etudiant;
use App\Models\Requete;
use App\Models\TypeRequete;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requete>
 */
class RequeteFactory extends Factory
{
    protected $model = Requete::class;

    public function definition(): array
    {
        return [
            'date_depot' => fake()->dateTimeBetween('-1 month', 'now'),
            'objet' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'statut' => fake()->randomElement(['en_attente', 'en_traitement', 'traitee', 'rejetee']),
            'annee_depot' => fake()->randomElement(['2024-2025', '2025-2026']),
            'filiere_depot' => fake()->randomElement(['INFO', 'GELEC', 'GCIV', 'COM', 'GEII']),
            'niveau_depot' => fake()->randomElement(['N1', 'N2', 'N3']),
            'etudiant_id' => Etudiant::factory(),
            'type_requete_id' => TypeRequete::factory(),
        ];
    }
}
