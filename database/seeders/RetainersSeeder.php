<?php

namespace Database\Seeders;

use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\Listing;
use App\Models\MarketPrice;
use App\Models\Retainer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class RetainersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where([
            'email' => 'test@example.com',
        ])->firstOrFail();

        $server = Server::GOBLIN;

        $retainers = Retainer::factory(10, [
            'server' => $server,
        ])->for($user)->create();

        foreach ($retainers as $retainer) {
            $this->createItems($retainer);
        }
    }

    private function createItems(Retainer $retainer): void
    {
        for ($i = 0; $i < 10; $i++) {
            $items[] = Item::factory()->create([
                'id' => max(1000000, Item::max('id') + 1),
            ]);
        }

        foreach ($items as $item) {
            Listing::factory(1, [
                'retainer_name' => $retainer->name,
                'server' => $retainer->server,
            ])->for($item)->create();

            Listing::factory(3, [
                'server' => $retainer->server,
            ])->for($item)->create();

            Sale::factory(10, [
                'server' => $retainer->server,
            ])->for($item)->create();

            MarketPrice::factory(1, [
                'server' => $retainer->server,
                'price' => Listing::where('item_id', $item->id)->where('server', $retainer->server)->avg('price_per_unit'),
            ])->for($item)->create();

            $retainer->items()->attach($item);
        }
    }
}
