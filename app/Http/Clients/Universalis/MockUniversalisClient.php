<?php

namespace App\Http\Clients\Universalis;

use Faker\Generator;

class MockUniversalisClient implements UniversalisClientInterface
{
    public function __construct(private Generator $faker)
    {
    }

    public function fetchMarketBoardListings(string $server, array $itemIDs): array
    {
        return [];
    }


    public function fetchMarketBoardSales(string $server, int $itemID): array
    {
        return [];
    }


    public function fetchLastWeekSaleCount(string $server, int $itemID): int
    {
        return $this->faker->numberBetween(1, 1000);
    }


    public function fetchMostRecentlyUpdatedItems(string $server): array
    {
        return [];
    }
}
