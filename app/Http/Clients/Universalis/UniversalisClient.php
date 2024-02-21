<?php

namespace App\Http\Clients\Universalis;

use App\Http\Clients\Universalis\UniversalisClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Facades\Log;

class UniversalisClient implements UniversalisClientInterface
{
    private $client;

    public function __construct()
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'retry_on_status' => [429, 503],
            'retry_on_timeout' => true,
            'delay' => 1000,
            'max_retry_attempts' => 3,
        ]));

        $this->client = new Client([
            'base_uri' => 'https://universalis.app/api/v2/',
            'timeout' => 5.0,
            'handler' => $stack,
        ]);
    }

    public function fetchMarketBoardListingsWithRetries(string $server, array $item_ids): array
    {
        $item_ids = array_unique($item_ids);
        sort($item_ids);

        Log::debug("Fetching market board listings for server {$server}." . " | Items: " . implode(",", $item_ids));
        try {
            $mb_data = $this->client->get("{$server}/" . implode(",", $item_ids));
        } catch (\Exception) {
            Log::error("Failed to retrieve market board listings for server {$server}");
            return [];
        }

        Log::debug("Retrieved market board listings for server {$server} | Items: " . implode(",", $item_ids));
        $mb_data_arr = json_decode($mb_data->getBody()) ?? [];
        if (isset($mb_data_arr->itemID)) {
            $mb_data_arr = [
                $mb_data_arr->itemID => $mb_data_arr
            ];
        } else {
            $mb_data_arr = $mb_data_arr->items ?? [];
        }

        return $mb_data_arr;
    }

    public function fetchMarketBoardHistory(string $server, string $item_id): array
    {
        try {
            $response = $this->client->get("history/{$server}/{$item_id}");
            Log::debug("Retrieved market board history for item {$item_id}");
            return json_decode($response->getBody());
        } catch (\Exception) {
            Log::error("Failed to retrieve market board history for item {$item_id}");
        }

        return [];
    }

    public function fetchLastWeekSaleCount(string $server, string $item_id): int
    {
        try {
            $response = $this->client->get("history/{$server}/{$item_id}");
            Log::debug("Retrieved market board history for item {$item_id}");
            $mb_history = json_decode($response->getBody());
            return collect($mb_history->entries)->map(
                function ($entry) {
                    return $entry->quantity;
                }
            )->sum();
        } catch (\Exception) {
            Log::error("Failed to retrieve last week sale count for item {$item_id}");
        }

        return 0;
    }

}
