<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Clients\XIV\MockXIVClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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
                        ->has('ingredients', 5)
                        ->has('ingredients.0', function (Assert $ingredients) {
                            $ingredients->where('item_id', MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID)
                                ->has('id')
                                ->has('item', function (Assert $item) {
                                    $item->has('name')
                                        ->has('icon')
                                        ->has('vendor_price')
                                        ->missing('market_price')
                                        ->etc();
                                })
                                ->where('amount', 3)
                                ->has('crafting_recipe', function (Assert $craftingRecipe) {
                                    $craftingRecipe->has('id')
                                        ->has('item_id')
                                        ->where('amount_result', 3)
                                        ->where('purchase_cost', 297)
                                        ->where('market_craft_cost', 270000000)
                                        ->where('optimal_craft_cost', 27000)
                                        ->has('crafting_costs')
                                        ->etc();
                                })
                                ->etc();
                        })
                        ->has('ingredients.1', function (Assert $ingredients) {
                            $ingredients->where('item_id', MockXIVClient::MYTHRIL_RIVETS_ITEM_ID)
                                ->has('id')
                                ->has('item', function (Assert $item) {
                                    $item->has('name')
                                        ->has('icon')
                                        ->has('vendor_price')
                                        ->missing('market_price')
                                        ->etc();
                                })
                                ->where('amount', 2)
                                ->etc();
                        })
                        ->has('ingredients.2', function (Assert $ingredients) {
                            $ingredients->where('item_id', MockXIVClient::VARNISH_ITEM_ID)
                                ->has('id')
                                ->has('item', function (Assert $item) {
                                    $item->has('name')
                                        ->has('icon')
                                        ->has('vendor_price')
                                        ->missing('market_price')
                                        ->etc();
                                })
                                ->where('amount', 2)
                                ->etc();
                        })
                        ->has('ingredients.3', function (Assert $ingredients) {
                            $ingredients->where('item_id', 4)
                                ->has('id')
                                ->has('item', function (Assert $item) {
                                    $item->has('name')
                                        ->has('icon')
                                        ->has('vendor_price')
                                        ->where('market_price', 10000000)
                                        ->etc();
                                })
                                ->where('amount', 6)
                                ->etc();
                        })
                        ->has('amount_result')
                        ->has('class_job')
                        ->has('class_job_level')
                        ->has('class_job_icon')
                        ->has('updated_at')
                        ->has('created_at')
                        ->where('purchase_cost', 1000)
                        ->where('market_craft_cost', 150000297)
                        ->where('optimal_craft_cost', 15297)
                        ->has('crafting_costs');
                })
                ->has('item', function (Assert $item) {
                    $item->has('name')
                        ->has('icon')
                        ->has('vendor_price')
                        ->where('market_price', 9999)
                        ->where('id', 24511)
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('market_prices', 1);
                })
                ->has('history', 8)
                ->has('listings', 1)
                ->has('lastUpdated')
        );
    }
}
