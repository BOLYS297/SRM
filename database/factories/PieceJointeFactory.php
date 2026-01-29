<?php

namespace Database\Factories;

use App\Models\PieceJointe;
use App\Models\Requete;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PieceJointe>
 */
class PieceJointeFactory extends Factory
{
    protected $model = PieceJointe::class;

    public function definition(): array
    {
        return [
            'nom_fichier' => fake()->word() . '.pdf',
            'type_piece' => fake()->randomElement(['pdf', 'image', 'doc']),
            'chemin_fichier' => 'uploads/' . fake()->uuid() . '.pdf',
            'date_ajout' => fake()->dateTimeBetween('-1 month', 'now'),
            'requete_id' => Requete::factory(),
        ];
    }
}
