<?php

namespace App\Http\Clients\Universalis;

interface UniversalisClientInterface
{
    /**
     * Fetch market board listings for a specific server and item
     *
     * @param string $server
     * @param array<int> $itemIDs
     * @return array
     */
    public function fetchMarketBoardListings(string $server, array $itemIDs): array;

    /**
     * Fetch market board history for a specific item
     *
     * @param string $server
     * @param int $itemID
     * @return array
     */
    public function fetchMarketBoardSales(string $server, int $itemID): array;

    /**
     * Fetch the total sale count for an item over the last week
     *
     * @param string $server
     * @param int $itemID
     * @return int
     */
    public function fetchLastWeekSaleCount(string $server, int $itemID): int;
}
