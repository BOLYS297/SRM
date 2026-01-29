<?php

namespace Database\Factories;

use App\Models\TypeRequete;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeRequete>
 */
class TypeRequeteFactory extends Factory
{
    protected $model = TypeRequete::class;

    public function definition(): array
    {
        return [
            'libelle' => fake()->unique()->words(3, true),
            'delai_cible_hrs' => fake()->numberBetween(24, 120),
        ];
    }
}
