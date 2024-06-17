<?php

namespace App\Http\Clients\Universalis;

use App\Models\Enums\Server;
use Faker\Generator;

class MockUniversalisClient implements UniversalisClientInterface
{
    public function __construct(private Generator $faker)
    {
    }

    public function fetchMarketBoardListings(Server $server, array $itemIDs): array
    {
        return [];
    }


    public function fetchMarketBoardSales(Server $server, int $itemID): array
    {
        return [];
    }


    public function fetchLastWeekSaleCount(Server $server, int $itemID): int
    {
        return $this->faker->numberBetween(1, 1000);
    }


    public function fetchMostRecentlyUpdatedItems(Server $server): array
    {
        return [];
    }
}
