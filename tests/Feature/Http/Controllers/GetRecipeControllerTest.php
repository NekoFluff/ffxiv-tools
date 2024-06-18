<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Clients\XIV\MockXIVClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Inertia\Testing\AssertableInertia as Assert;

class GetRecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        ini_set('memory_limit', '256M');

    }

    #[Test]
    public function it_should_get_a_recipe_for_the_requested_item(): void
    {
        $user = User::factory()->create();

        // Arrange
        $itemID = MockXIVClient::WOODEN_LOFT_ITEM_ID;
        $server = 'Goblin';

        // Act
        $response = $this->actingAs($user)->get(route('recipe.get', [
            'itemID' => $itemID,
            'server' => $server,
        ]));

        // Assert
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('recipe', function (Assert $recipe) {
                    $recipe->has('id')
                        ->has('item')
                        ->where('item_id', 24511)
                        ->has('ingredients')
                        ->has('amount_result')
                        ->has('class_job')
                        ->has('class_job_level')
                        ->has('class_job_icon')
                        ->has('updated_at')
                        ->has('created_at')
                        ->has('purchase_cost')
                        ->has('market_craft_cost')
                        ->has('optimal_craft_cost')
                        ->has('crafting_costs');
                })
                ->has('item', function (Assert $item) {
                    $item->has('name')
                        ->has('icon')
                        ->has('vendor_price')
                        ->where('market_price', 10000000)
                        ->where('id', 24511)
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('market_prices');
                })
                ->has('history')
                ->has('listings')
                ->has('lastUpdated')
        );
    }
}
