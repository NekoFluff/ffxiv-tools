<?php

namespace Tests\Feature\Services;

use App\Http\Clients\Universalis\MockUniversalisClient;
use App\Http\Clients\XIV\MockXIVClient;
use App\Models\Enums\Server;
use App\Models\Ingredient;
use App\Models\Item;
use App\Models\Listing;
use App\Models\MarketPrice;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FFXIVServiceTest extends TestCase
{
    use RefreshDatabase;

    private FFXIVService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new FFXIVService(new MockXIVClient(Factory::create()), new MockUniversalisClient(Factory::create()));
    }

    #[Test]
    public function it_should_return_null_for_an_invalid_item_id(): void
    {
        // Arrange
        $itemID = 999;

        // Act
        $recipe = $this->service->getRecipeByItemID($itemID);

        // Assert
        $this->assertNull($recipe);
        $this->assertDatabaseEmpty('recipes');
        $this->assertDatabaseCount('items', 1);
    }

    #[Test]
    public function it_should_get_a_recipe_by_item_id(): void
    {
        // Arrange
        $itemID = MockXIVClient::WOODEN_LOFT_ITEM_ID;

        // Act
        $recipe = $this->service->getRecipeByItemID($itemID);

        // Assert
        $this->assertNotNull($recipe);
        $this->assertEquals($itemID, $recipe->item_id);
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
        $this->assertDatabaseCount('recipes', 6);
        $this->assertDatabaseHas('items', ['id' => $itemID]);
        $this->assertDatabaseCount('items', 15);
        $this->assertDatabaseCount('ingredients', 17);
        $this->assertDatabaseCount('market_prices', 0);
        $this->assertDatabaseCount('crafting_costs', 0);
        $this->assertDatabaseCount('listings', 0);
        $this->assertDatabaseCount('sales', 0);
    }

    #[Test]
    public function it_should_update_the_market_price_for_an_item_to_the_average_price(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        $item = Item::factory()->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);

        /** @var Collection<int, Listing> $listings */
        $listings = collect([
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
        ]);
        $expectedPrice = 460;

        // Act
        $this->service->updateMarketPrice($server, $item, $listings);

        // Assert
        $this->assertEquals($expectedPrice, $item->marketPrice($server)->price);
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => $expectedPrice,
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }

    #[Test]
    public function it_should_update_the_market_price_for_an_item_to_the_median_price(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        $item = Item::factory()->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);

        /** @var Collection<int, Listing> $listings */
        $listings = collect([
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
        ]);
        $expectedPrice = 400;

        // Act
        $this->service->updateMarketPrice($server, $item, $listings);

        // Assert
        $this->assertEquals($expectedPrice, $item->marketPrice($server)->price);
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => $expectedPrice,
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }

    #[Test]
    public function it_should_successfully_overwrite_any_existing_market_price(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        $item = Item::factory()->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);

        /** @var Collection<int, Listing> $listings */
        $listings = collect([
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 400, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
        ]);
        /** @var Collection<int, Listing> $listings2 */
        $listings2 = collect([
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 500, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 600, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 600, 'quantity' => 1]),
            Listing::factory()->createOne(['item_id' => $item->id, 'price_per_unit' => 600, 'quantity' => 1]),
        ]);
        $expectedPrice = 560;

        // Act
        $this->service->updateMarketPrice($server, $item, $listings);
        $this->service->updateMarketPrice($server, $item, $listings2);

        // Assert
        $this->assertEquals($expectedPrice, $item->marketPrice($server)->price);
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => $expectedPrice,
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }

    #[Test]
    public function it_should_update_the_market_price_to_the_default_value_if_there_are_no_listings(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        $item = Item::factory()->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);

        // Act
        $this->service->updateMarketPrice($server, $item, collect());

        // Assert
        $this->assertEquals(MarketPrice::DEFAULT_MARKET_PRICE, $item->marketPrice($server)->price);
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => MarketPrice::DEFAULT_MARKET_PRICE,
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }

    #[Test]
    public function it_should_update_the_recipe_costs_so_its_best_to_buy_all_ingredients(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        Item::factory()->has(MarketPrice::factory()->state(['price' => 500]))->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 100]))->create(['id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 200]))->create(['id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 300]))->create(['id' => MockXIVClient::VARNISH_ITEM_ID]);

        $recipe = Recipe::factory()->create(['item_id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID, 'amount' => 2, 'recipe_id' => $recipe->id]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID, 'amount' => 3, 'recipe_id' => $recipe->id]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::VARNISH_ITEM_ID, 'amount' => 4, 'recipe_id' => $recipe->id]);

        // Act
        $this->service->updateRecipeCosts($server, $recipe);

        // Assert
        $this->assertEquals(500, $recipe->craftingCost($server)->purchase_cost);
        $this->assertEquals(2000, $recipe->craftingCost($server)->market_craft_cost);
        $this->assertEquals(2000, $recipe->craftingCost($server)->optimal_craft_cost);

        $this->assertDatabaseHas('crafting_costs', [
            'recipe_id' => $recipe->id,
            'purchase_cost' => 500,
            'market_craft_cost' => 2000,
            'optimal_craft_cost' => 2000,
        ]);
        $this->assertDatabaseCount('crafting_costs', 1);
    }

    #[Test]
    public function it_should_update_the_recipe_costs_so_its_best_to_craft_some_ingredients(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        Item::factory()->has(MarketPrice::factory()->state(['price' => 500]))->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 100]))->create(['id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 200]))->create(['id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID]);
        Item::factory()->has(MarketPrice::factory()->state(['price' => 300]))->create(['id' => MockXIVClient::VARNISH_ITEM_ID]);

        $recipe = Recipe::factory()->create(['item_id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Ingredient::factory()->for($recipe)->create(['item_id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID, 'amount' => 2]);
        Ingredient::factory()->for($recipe)->create(['item_id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID, 'amount' => 3]);
        Ingredient::factory()->for($recipe)->create(['item_id' => MockXIVClient::VARNISH_ITEM_ID, 'amount' => 4]);

        $recipe2 = Recipe::factory()->create(['item_id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID]);
        $cheapItem = Item::factory()->has(MarketPrice::factory()->state(['price' => 7]))->create(['id' => 1]);
        Ingredient::factory()->for($recipe2)->create(['item_id' => $cheapItem->id, 'amount' => 1]);

        // Act
        $this->service->updateRecipeCosts($server, $recipe);

        // Assert
        $this->assertEquals(500, $recipe->craftingCost($server)->purchase_cost);
        $this->assertEquals(2000, $recipe->craftingCost($server)->market_craft_cost);
        $this->assertEquals(1814, $recipe->craftingCost($server)->optimal_craft_cost);

        $this->assertDatabaseHas('crafting_costs', [
            'recipe_id' => $recipe->id,
            'purchase_cost' => 500,
            'market_craft_cost' => 2000,
            'optimal_craft_cost' => 1814,
        ]);

        $this->assertEquals(100, $recipe2->craftingCost($server)->purchase_cost);
        $this->assertEquals(7, $recipe2->craftingCost($server)->market_craft_cost);
        $this->assertEquals(7, $recipe2->craftingCost($server)->optimal_craft_cost);

        $this->assertDatabaseHas('crafting_costs', [
            'recipe_id' => $recipe2->id,
            'purchase_cost' => 100,
            'market_craft_cost' => 7,
            'optimal_craft_cost' => 7,
        ]);
        $this->assertDatabaseCount('crafting_costs', 2);
    }

    #[Test]
    public function it_should_update_the_crafting_costs_to_the_default_cost_if_there_is_no_market_price_data(): void
    {
        // Arrange
        $server = Server::GOBLIN;
        Item::factory()->create(['id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Item::factory()->create(['id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID]);
        Item::factory()->create(['id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID]);
        Item::factory()->create(['id' => MockXIVClient::VARNISH_ITEM_ID]);

        $recipe = Recipe::factory()->create(['item_id' => MockXIVClient::WOODEN_LOFT_ITEM_ID]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::ROSEWOOD_LUMBER_ITEM_ID, 'amount' => 2, 'recipe_id' => $recipe->id]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::MYTHRIL_RIVETS_ITEM_ID, 'amount' => 3, 'recipe_id' => $recipe->id]);
        Ingredient::factory()->create(['item_id' => MockXIVClient::VARNISH_ITEM_ID, 'amount' => 4, 'recipe_id' => $recipe->id]);

        // Act
        $this->service->updateRecipeCosts($server, $recipe);

        // Assert
        $this->assertEquals(10000000, $recipe->craftingCost($server)->purchase_cost);
        $this->assertEquals(90000000, $recipe->craftingCost($server)->market_craft_cost);
        $this->assertEquals(90000000, $recipe->craftingCost($server)->optimal_craft_cost);

        $this->assertDatabaseHas('crafting_costs', [
            'recipe_id' => $recipe->id,
            'purchase_cost' => 10000000,
            'market_craft_cost' => 90000000,
            'optimal_craft_cost' => 90000000,
        ]);
        $this->assertDatabaseCount('crafting_costs', 1);
    }
}
