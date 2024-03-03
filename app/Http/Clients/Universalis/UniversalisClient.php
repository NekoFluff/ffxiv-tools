<?php

namespace App\Http\Clients\Universalis;

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

    public function fetchMarketBoardListings(string $server, array $itemIDs): array
    {
        $itemIDs = array_unique($itemIDs);
        sort($itemIDs);

        Log::debug("Fetching market board listings for server {$server}.".' | Items: '.implode(',', $itemIDs));
        try {
            $mbListings = $this->client->get("{$server}/".implode(',', $itemIDs).'?listings=20');
        } catch (\Exception $ex) {
            Log::error("Failed to retrieve market board listings for server {$server}. Exception: ".$ex->getMessage());

            return [];
        }
        Log::debug("Retrieved market board listings for server {$server} | Items: ".implode(',', $itemIDs));
        /** @var array $body */
        $body = json_decode($mbListings->getBody(), true);

        $mbListings = [];
        if (isset($body['itemID'])) {
            $mbListings = [
                $body['itemID'] => $body,
            ];
        } else {
            $mbListings = $body['items'] ?? [];
        }

        return $mbListings;
    }

    public function fetchMarketBoardSales(string $server, int $itemID): array
    {
        try {
            $response = $this->client->get("history/{$server}/{$itemID}");
            Log::debug("Retrieved market board history for item {$itemID}");

            return json_decode($response->getBody(), true)['entries'] ?? [];
        } catch (\Exception $ex) {
            Log::error("Failed to retrieve market board history for item {$itemID}. Exception: ".$ex->getMessage());
        }

        return [];
    }

    public function fetchLastWeekSaleCount(string $server, int $itemID): int
    {
        try {
            $response = $this->client->get("history/{$server}/{$itemID}");
            Log::debug("Retrieved market board history for item {$itemID}");

            /** @var array $mbSales */
            $mbSales = json_decode($response->getBody(), true)['entries'] ?? [];

            return collect($mbSales)->map(
                function ($entry) {
                    return $entry['quantity'];
                }
            )->sum();
        } catch (\Exception) {
            Log::error("Failed to retrieve last week sale count for item {$itemID}");
        }

        return 0;
    }
}
