<?php

namespace App\Http\Clients\Universalis;

interface UniversalisClientInterface
{
    /**
     * Fetch market board listings for a specific server and item
     *
     * @param string $server
     * @param array $item_ids
     * @return array
     */
    public function fetchMarketBoardListings(string $server, array $item_ids): array;

    /**
     * Fetch market board history for a specific item
     *
     * @param string $server
     * @param string $item_id
     * @return array
     */
    public function fetchMarketBoardHistory(string $server, string $item_id): array;

    /**
     * Fetch the total sale count for an item over the last week
     *
     * @param string $server
     * @param string $item_id
     * @return int
     */
    public function fetchLastWeekSaleCount(string $server, string $item_id): int;
}
