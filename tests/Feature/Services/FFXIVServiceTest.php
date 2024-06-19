<?php

namespace Tests\Feature\Services;

use App\Http\Clients\Universalis\MockUniversalisClient;
use App\Services\FFXIVService;
use App\Http\Clients\XIV\MockXIVClient;
use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
        $this->service->updateMarketPrice(Server::from('Goblin'), $item, $listings);

        // Assert
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => $expectedPrice
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }

    #[Test]
    public function it_should_update_the_market_price_for_an_item_to_the_median_price(): void
    {
        // Arrange
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
        $this->service->updateMarketPrice(Server::from('Goblin'), $item, $listings);

        // Assert
        $this->assertDatabaseHas('market_prices', [
            'item_id' => $item->id,
            'price' => $expectedPrice
        ]);
        $this->assertDatabaseCount('market_prices', 1);
    }
}
