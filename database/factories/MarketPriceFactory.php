<?php

namespace Database\Factories;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketPrice>
 */
class MarketPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data_center' => DataCenter::CRYSTAL,
            'server' => Server::GOBLIN,
            'price' => $this->faker->numberBetween(100, 1000000),
        ];
    }
}
