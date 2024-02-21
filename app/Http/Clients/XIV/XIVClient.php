<?php

namespace App\Http\Clients\XIV;

use App\Http\Clients\XIV\XIVClientInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Facades\Log;

class XIVClient implements XIVClientInterface
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
            'base_uri' => 'https://xivapi.com/',
            'timeout' => 5.0,
            'handler' => $stack,
        ]);
    }

    public function fetchRecipe(int $recipeID): array
    {
        Log::debug("Fetching recipe data for recipe {$recipeID}");
        try {
            $response = $this->client->get("recipe/{$recipeID}");
            $recipeData = json_decode($response->getBody(), true);
            Log::debug("Retrieved data {$recipeData}");
            return $recipeData;
        } catch (Exception) {
            Log::error("Failed to retrieve recipe data for recipe {$recipeID}");
        }

        return [];
    }

    public function fetchItem(int $itemID): object
    {
        Log::debug("Fetching item data for item {$itemID}");
        try {
            $filterColumns = [
                "ID",
                "Name",
                "Description",
                "LevelItem",
                "ClassJobCategory.Name",
                "GameContentLinks.GilShopItem",
                "Icon",
                "IconHD",
                "Recipes",
                "PriceLow",
                "PriceMid"
            ];

            $response = $this->client->get("item/{$itemID}?columns=" . implode(",", $filterColumns));
            $itemData = json_decode($response->getBody());
            Log::debug("Retrieved data {$itemData}");
            return $itemData;
        } catch (Exception) {
            Log::error("Failed to retrieve item data for item {$itemID}");
        }

        return [];
    }

    public function fetchVendorPrice(int $itemID): int
    {
        Log::debug("Fetching vendor data for item {$itemID}");
        try {
            $response = $this->client->get("item/{$itemID}?columns=GameContentLinks.GilShopItem.Item,PriceMid");
            $vendorData = json_decode($response->getBody(), true);
            Log::debug("Retrieved data {$vendorData}");
            return $vendorData["GameContentLinks"]["GilShopItem"]["Item"] ? $vendorData["PriceMid"] : 0;
        } catch (Exception) {
            Log::error("Failed to retrieve vendor data for item {$itemID}");
        }

        return 0;
    }
}
