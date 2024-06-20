<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemRetainerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_should_save_a_new_item_for_a_retainer(): void
    {
        $user = User::factory()->create();
        $retainer = $user->retainers()->create([
            'name' => 'Test Retainer',
            'server' => Server::GOBLIN,
            'data_center' => DataCenter::CRYSTAL,
        ]);

        $item = Item::factory()->create();

        $response = $this->actingAs($user)->postJson(route('retainers.items.store', ['retainerID' => $retainer->id]), [
            'item_id' => $item->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'retainer_id',
            'retainer_name',
            'server',
            'items' => [
                '*' => [
                    'item_id',
                    'item_name',
                    'retainer_listing_price',
                    'num_retainer_listings',
                    'lowest_listing_price',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_should_not_allow_a_user_to_add_an_item_to_a_retainer_they_do_not_own(): void
    {
        $user = User::factory()->create();
        $retainer = User::factory()->create()->retainers()->create([
            'name' => 'Test Retainer',
            'server' => Server::GOBLIN,
            'data_center' => DataCenter::CRYSTAL,
        ]);

        $item = Item::factory()->create();

        $response = $this->actingAs($user)->postJson(route('retainers.items.store', ['retainerID' => $retainer->id]), [
            'item_id' => $item->id,
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function it_should_remove_an_item_from_a_retainer(): void
    {
        $user = User::factory()->create();
        $retainer = $user->retainers()->create([
            'name' => 'Test Retainer',
            'server' => Server::GOBLIN,
            'data_center' => DataCenter::CRYSTAL,
        ]);

        $item = Item::factory()->create();

        $retainer->items()->attach($item->id);

        $response = $this->actingAs($user)->deleteJson(route('retainers.items.destroy', ['retainerID' => $retainer->id]), [
            'item_ids' => [$item->id],
        ]);

        $response->assertNoContent();
        $this->assertDatabaseEmpty('item_retainer');
    }
}
