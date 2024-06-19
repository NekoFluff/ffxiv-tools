<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount_result' => 1,
            'class_job' => $this->faker->word,
            'class_job_level' => $this->faker->numberBetween(1, 80),
            'class_job_icon' => $this->faker->imageUrl(),
        ];
    }
}
