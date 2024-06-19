<?php

namespace Database\Factories;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = 1;
        $pricePerUnit = $this->faker->numberBetween(1, 1000);
        $total = $quantity;
        $tax = intval(0.05 * $total);

        return [
            'id' => $this->faker->unique()->randomNumber(),
            'data_center' => DataCenter::from('Crystal'),
            'server' => Server::GOBLIN,
            'retainer_name' => $this->faker->name,
            'retainer_city' => 1,
            'quantity' => $quantity,
            'hq' => false,
            'price_per_unit' => $pricePerUnit,
            'total' => $total,
            'tax' => $tax,
            'last_review_time' => now(),
        ];
    }
}
