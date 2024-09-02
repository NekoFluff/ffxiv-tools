<?php

namespace App\Http\Clients\Universalis;

use App\Models\Enums\Server;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
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
            'timeout' => 10.0,
            'handler' => $stack,
        ]);
    }

    public function fetchMarketBoardListings(Server $server, array $itemIDs): array
    {
        $itemIDs = array_unique($itemIDs);
        sort($itemIDs);

        try {
            $mbListings = $this->client->get("{$server->value}/".implode(',', $itemIDs).'?listings=40');
        } catch (ServerException $ex) {
            Log::error('A server exception occurred while retrieving the market board listings', [
                'server' => $server->value,
                'items' => $itemIDs,
                'exception' => $ex,
            ]);

            return [];
        } catch (\Exception $ex) {
            Log::error('Unknown exception occurred while and failed to retrieve market board listings', [
                'server' => $server->value,
                'items' => $itemIDs,
                'exception' => $ex,
            ]);

            return [];
        }
        /** @var array<mixed> $body */
        $body = json_decode($mbListings->getBody(), true);

        $mbListings = [];
        if (isset($body['itemID'])) {
            $mbListings = [
                $body['itemID'] => $body,
            ];
        } else {
            $mbListings = $body['items'] ?? [];
        }

        Log::debug('Retrieved market board listings', [
            'server' => $server->value,
            'items' => $itemIDs,
            'response' => $mbListings,
        ]);

        return $mbListings;
    }

    /** @return array<mixed> */
    public function fetchMarketBoardSales(Server $server, int $itemID): array
    {
        try {
            $response = $this->client->get("history/{$server->value}/{$itemID}");

            $sales = json_decode($response->getBody(), true)['entries'] ?? [];
            Log::debug('Retrieved market board history', [
                'server' => $server->value,
                'itemID' => $itemID,
                'response' => $sales,
            ]);

            return $sales;
        } catch (\Exception $ex) {
            Log::error('Failed to retrieve market board history', ['exception' => $ex, 'server' => $server->value, 'itemID' => $itemID]);
        }

        return [];
    }

    public function fetchLastWeekSaleCount(Server $server, int $itemID): int
    {
        try {
            $response = $this->client->get("history/{$server->value}/{$itemID}");

            /** @var array $mbSales */
            $mbSales = json_decode($response->getBody(), true)['entries'] ?? [];

            $count = collect($mbSales)->map(
                function ($entry) {
                    return $entry['quantity'];
                }
            )->sum();

            Log::debug('Retrieved last week sale count', ['server' => $server->value, 'itemID' => $itemID, 'count' => $count]);

        } catch (\Exception) {
            Log::error('Failed to retrieve last week sale count', ['server' => $server->value, 'itemID' => $itemID]);
        }

        return 0;
    }

    /** @return array<mixed> */
    public function fetchMostRecentlyUpdatedItems(Server $server): array
    {
        Log::debug('Fetching most recently updated items', ['server' => $server->value]);
        try {
            $response = $this->client->get("https://universalis.app/api/v2/extra/stats/most-recently-updated?world={$server->value}");
            Log::debug('Retrieved most recently updated items', ['server' => $server->value]);

            return json_decode($response->getBody(), true)['items'] ?? [];
        } catch (\Exception $ex) {
            Log::error('Failed to retrieve most recently updated items', ['exception' => $ex, 'server' => $server->value]);
        }

        return [];
    }
}
