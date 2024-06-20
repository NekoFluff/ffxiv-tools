<?php

namespace App\Http\Clients\Universalis;

use App\Models\Enums\Server;

interface UniversalisClientInterface
{
    /**
     * Fetch market board listings for a specific server and item
     *
     * @param  array<int>  $itemIDs
     * @return array<int,array<mixed>>
     */
    public function fetchMarketBoardListings(Server $server, array $itemIDs): array;

    /**
     * Fetch market board history for a specific item
     *
     * @return array<int,array<mixed>>
     */
    public function fetchMarketBoardSales(Server $server, int $itemID): array;

    /**
     * Fetch the total sale count for an item over the last week
     */
    public function fetchLastWeekSaleCount(Server $server, int $itemID): int;

    /**
     * Fetch the most recently updated items
     *
     * @return array<int,array<mixed>>
     */
    public function fetchMostRecentlyUpdatedItems(Server $server): array;
}
