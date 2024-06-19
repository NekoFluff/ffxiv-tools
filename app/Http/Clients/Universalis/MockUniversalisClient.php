<?php

namespace App\Http\Clients\Universalis;

use App\Models\Enums\Server;
use Faker\Generator;

class MockUniversalisClient implements UniversalisClientInterface
{
    public function __construct(private Generator $faker)
    {
    }

    /** @return array<mixed> */
    public function fetchMarketBoardListings(Server $server, array $itemIDs): array
    {
        $json = (string) file_get_contents('app\Http\Clients\Universalis\24511_5376.json');
        $data = json_decode($json, true)['items'];
        if (!in_array(24511, $itemIDs)) {
            unset($data['24511']);
        }
        if (!in_array(5376, $itemIDs)) {
            unset($data['5376']);
        }
        return $data;
    }

    /** @return array<mixed> */
    public function fetchMarketBoardSales(Server $server, int $itemID): array
    {
        return [];
    }


    public function fetchLastWeekSaleCount(Server $server, int $itemID): int
    {
        return $this->faker->numberBetween(1, 1000);
    }

    /** @return array<mixed> */
    public function fetchMostRecentlyUpdatedItems(Server $server): array
    {
        return [];
    }
}
