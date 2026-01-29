<?php

namespace Database\Factories;

use App\Models\EtapeTraitement;
use App\Models\Requete;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EtapeTraitement>
 */
class EtapeTraitementFactory extends Factory
{
    protected $model = EtapeTraitement::class;

    public function definition(): array
    {
        $dateEntree = fake()->dateTimeBetween('-1 month', 'now');
        $dateSortie = fake()->boolean(70) ? fake()->dateTimeBetween($dateEntree, 'now') : null;

        return [
            'ordre_etape' => fake()->numberBetween(1, 7),
            'action' => fake()->randomElement(['enregistrer', 'transmettre', 'coter', 'examiner', 'valider', 'rejeter']),
            'date_entree' => $dateEntree,
            'date_sortie' => $dateSortie,
            'observation' => fake()->optional()->sentence(),
            'requete_id' => Requete::factory(),
            'service_id' => Service::factory(),
        ];
    }
}
