<?php

namespace Database\Factories;

use App\Models\Decision;
use App\Models\Requete;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Decision>
 */
class DecisionFactory extends Factory
{
    protected $model = Decision::class;

    public function definition(): array
    {
        return [
            'date_decision' => fake()->dateTimeBetween('-1 week', 'now'),
            'resultat' => fake()->randomElement(['favorable', 'defavorable', 'incomplet']),
            'motif' => fake()->optional()->sentence(),
            'requete_id' => Requete::factory(),
            'service_id' => Service::factory(),
        ];
    }
}
