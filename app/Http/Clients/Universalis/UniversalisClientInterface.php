<?php

namespace App\Http\Clients\Universalis;

interface UniversalisClientInterface
{
    /**
     * Fetch market board listings for a specific server and item
     *
     * @param  array<int>  $itemIDs
     * @return array<int, array>
     */
    public function fetchMarketBoardListings(string $server, array $itemIDs): array;

    /**
     * Fetch market board history for a specific item
     */
    public function fetchMarketBoardSales(string $server, int $itemID): array;

    /**
     * Fetch the total sale count for an item over the last week
     */
    public function fetchLastWeekSaleCount(string $server, int $itemID): int;

    /**
     * Fetch the most recently updated items
     */
    public function fetchMostRecentlyUpdatedItems(string $server): array;
}
