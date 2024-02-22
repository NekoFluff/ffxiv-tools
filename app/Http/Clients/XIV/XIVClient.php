<?php

namespace App\Http\Clients\XIV;

use App\Http\Clients\XIV\XIVClientInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Facades\Log;
use stdClass;

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
            Log::debug("Retrieved recipe data {$response->getBody()}");
            $recipeData = json_decode($response->getBody(), true);
            return $recipeData;
        } catch (Exception) {
            Log::error("Failed to retrieve recipe data for recipe {$recipeID}");
        }

        return [];
    }

    public function fetchItem(int $itemID): stdClass
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
            Log::debug("Retrieved item data {$response->getBody()}");
            $itemData = json_decode($response->getBody());
            return $itemData;
        } catch (Exception) {
            Log::error("Failed to retrieve item data for item {$itemID}");
        }

        return (object) [];
    }

    public function fetchVendorPrice(int $itemID): int
    {
        Log::debug("Fetching vendor data for item {$itemID}");
        try {
            $response = $this->client->get("item/{$itemID}?columns=GameContentLinks.GilShopItem.Item,PriceMid");
            Log::debug("Retrieved vendor price data {$response->getBody()}");
            $vendorData = json_decode($response->getBody(), true);
            return $vendorData["GameContentLinks"]["GilShopItem"]["Item"] ? $vendorData["PriceMid"] : 0;
        } catch (Exception) {
            Log::error("Failed to retrieve vendor data for item {$itemID}");
        }

        return 0;
    }
}
