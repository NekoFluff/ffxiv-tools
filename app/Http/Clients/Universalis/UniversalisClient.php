<?php

namespace App\Http\Clients\Universalis;

use App\Models\Enums\Server;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UniversalisClient implements UniversalisClientInterface
{
    public const BASE_URL = 'https://universalis.app/api/v2';

    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->timeout(20)
            ->retry(3, 3000, function (Exception $exception, PendingRequest $request) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    return in_array($exception->response->status(), [429, 500, 503]);
                }

                return false;
            });
    }

    public function fetchMarketBoardListings(Server $server, array $itemIDs): array
    {
        $itemIDs = array_unique($itemIDs);
        sort($itemIDs);

        try {
            $response = $this->request()->get("/{$server->value}/".implode(',', $itemIDs), [
                'listings' => 40
            ]);

            /** @var array<mixed> $body */
            $body = $response->json();

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
            ]);

            return $mbListings;
        } catch (\Throwable $ex) {
            Log::error('Failed to retrieve market board listings', [
                'server' => $server->value,
                'items' => $itemIDs,
                'exception' => $ex,
            ]);

            return [];
        }
    }

    /** @return array<mixed> */
    public function fetchMarketBoardSales(Server $server, int $itemID): array
    {
        try {
            $response = $this->request()->get("/history/{$server->value}/{$itemID}");

            $sales = $response->json()['entries'] ?? [];
            Log::debug('Retrieved market board history', [
                'server' => $server->value,
                'itemID' => $itemID,
            ]);

            return $sales;
        } catch (Exception $ex) {
            Log::error('Failed to retrieve market board history', [
                'exception' => $ex,
                'server' => $server->value,
                'itemID' => $itemID
            ]);
        }

        return [];
    }

    public function fetchLastWeekSaleCount(Server $server, int $itemID): int
    {
        try {
            $response = $this->request()->get("/history/{$server->value}/{$itemID}");

            /** @var array $mbSales */
            $mbSales = $response->json()['entries'] ?? [];

            $count = collect($mbSales)->sum('quantity');

            Log::debug('Retrieved last week sale count', [
                'server' => $server->value,
                'itemID' => $itemID,
                'count' => $count
            ]);

            return $count;
        } catch (Exception) {
            Log::error('Failed to retrieve last week sale count', [
                'server' => $server->value,
                'itemID' => $itemID
            ]);
        }

        return 0;
    }

    /** @return array<mixed> */
    public function fetchMostRecentlyUpdatedItems(Server $server): array
    {
        Log::debug('Fetching most recently updated items', ['server' => $server->value]);
        try {
            $response = $this->request()->get("/extra/stats/most-recently-updated", [
                'world' => $server->value
            ]);

            Log::debug('Retrieved most recently updated items', ['server' => $server->value]);
            return $response->json()['items'] ?? [];
        } catch (Exception $ex) {
            Log::error('Failed to retrieve most recently updated items', [
                'exception' => $ex,
                'server' => $server->value
            ]);
        }

        return [];
    }
}
