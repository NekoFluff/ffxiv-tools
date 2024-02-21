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
            $recipe_data = json_decode($response->getBody(), true);
            Log::debug("Retrieved data {$recipe_data}");
            return $recipe_data;
        } catch (Exception) {
            Log::error("Failed to retrieve recipe data for recipe {$recipeID}");
        }

        return [];
    }

    public function fetchVendorCost(int $item_id): int
    {
        Log::debug("Fetching vendor data for item {$item_id}");
        try {
            $response = $this->client->get("item/{$item_id}?columns=GameContentLinks.GilShopItem.Item,PriceMid");
            $vendor_data = json_decode($response->getBody(), true);
            Log::debug("Retrieved data {$vendor_data}");
            return $vendor_data["GameContentLinks"]["GilShopItem"]["Item"] ? $vendor_data["PriceMid"] : 0;
        } catch (Exception) {
            Log::error("Failed to retrieve vendor data for item {$item_id}");
        }

        return 0;
    }
}
