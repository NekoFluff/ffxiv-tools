<?php

namespace App\Http\Clients\XIV;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XIVClient implements XIVClientInterface
{
    public const BASE_URL = 'https://xivapi.com';

    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)->retry(3, 1000, function (Exception $exception, PendingRequest $request) {
            if ($exception instanceof ConnectionException) {
                return true;
            }

            if ($exception instanceof RequestException) {
                return $exception->response->status() === 429 ||
                    $exception->response->status() == 503;
            }
        });
    }

    /** @return array<mixed> */
    public function fetchRecipe(int $recipeID): array
    {
        Log::debug("Fetching recipe data for recipe {$recipeID}");
        try {
            $response = $this->request()->get("/recipe/{$recipeID}");
            Log::debug('Retrieved recipe data');

            /** @var array<mixed> $recipeData */
            $recipeData = $response->json();

            return $recipeData;
        } catch (Exception $ex) {
            Log::error("Failed to retrieve recipe data for recipe {$recipeID}", ['exception' => $ex, 'recipeID' => $recipeID]);
        }

        return [];
    }

    public function fetchItem(int $itemID): ?XIVItem
    {
        Log::debug("Fetching item data for item {$itemID}");
        try {
            $filterColumns = [
                'ID',
                'Name',
                'Description',
                'LevelItem',
                'ClassJobCategory.Name',
                'GameContentLinks.GilShopItem',
                'Icon',
                'IconHD',
                'Recipes',
                'PriceLow',
                'PriceMid',
            ];

            $response = $this->request()->get("/item/{$itemID}", [
                'columns' => implode(',', $filterColumns),
            ]);
            Log::debug("Retrieved item data {$response->body()}");

            /** @var array<mixed> $itemData */
            $itemData = $response->json();

            $xivItem = XIVItem::hydrate($itemData);

            return $xivItem;
        } catch (Exception $ex) {
            Log::error("Failed to retrieve item data for item {$itemID}", ['exception' => $ex, 'itemID' => $itemID]);
        }

        return null;
    }

    public function fetchVendorPrice(int $itemID): int
    {
        Log::debug("Fetching vendor data for item {$itemID}");
        try {
            $response = Http::retry(3, 1000)->get("/item/{$itemID}", [
                'columns' => 'GameContentLinks.GilShopItem.Item,PriceMid',
        ]);
            Log::debug("Retrieved vendor price data {$response->body()}");
            $vendorData = $response->json();

            return $vendorData['GameContentLinks']['GilShopItem']['Item'] ? $vendorData['PriceMid'] : 0;
        } catch (Exception $ex) {
            Log::error("Failed to retrieve vendor data for item {$itemID}", ['exception' => $ex, 'itemID' => $itemID]);
        }

        return 0;
    }
}
