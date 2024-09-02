<?php

namespace Database\Factories;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => 1,
            'price_per_unit' => $this->faker->numberBetween(1, 1000),
            'buyer_name' => $this->faker->name,
            'timestamp' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'hq' => false,
            'data_center' => DataCenter::CRYSTAL,
            'server' => Server::GOBLIN,
        ];
    }
}
