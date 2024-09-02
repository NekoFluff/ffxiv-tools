<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Retainer>
 */
class RetainerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'server' => $this->faker->randomElement(['Cactuar', 'Faerie', 'Gilgamesh', 'Jenova', 'Midgardsormr', 'Sargatanas', 'Siren', 'Cerberus', 'Louisoix', 'Moogle', 'Omega']),
            'data_center' => $this->faker->randomElement(['Aether', 'Chaos', 'Crystal', 'Elemental', 'Gaia', 'Light', 'Mana', 'Primal']),
        ];
    }
}
