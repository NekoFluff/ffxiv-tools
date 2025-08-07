<?php

namespace App\Http\Clients\XIV;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XIVClient implements XIVClientInterface
{
    public const BASE_URL = 'https://v2.xivapi.com/api';

    private const FILTER_RECIPE_FIELDS = [
        'AmountIngredient',
        'AmountResult',
        'Ingredient[].Name',
        'Ingredient[].Icon',
        'ItemResult.Name',
        'ItemResult.Icon',
        'ItemResult.ClassJobRepair.NameEnglish',
        'RecipeLevelTable.ClassJobLevel',
        'CraftType',
    ];

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

    public function fetchRecipe(int $recipeID): ?XIVRecipe
    {
        Log::debug("Fetching recipe data for recipe {$recipeID}");
        try {
            $response = $this->request()->get("/sheet/Recipe/{$recipeID}", [
                'fields' => implode(',', self::FILTER_RECIPE_FIELDS)
            ]);
            Log::debug('Retrieved recipe data');

            /** @var array<mixed> $recipeData */
            $recipeData = $response->json();

            $xivRecipe = XIVRecipe::hydrateFromRecipeFetchResponse($recipeData);

            return $xivRecipe;
        } catch (Exception $ex) {
            Log::error("Failed to retrieve recipe data for recipe {$recipeID}", ['exception' => $ex, 'recipeID' => $recipeID]);
        }

        return null;
    }

    public function fetchRecipesForItem(int $itemID): Collection
    {
        Log::debug("Fetching recipes for item {$itemID}");
        try {
            $response = $this->request()->get('/search', [
                'sheets' => 'Recipe',
                'query' => "ItemResult={$itemID}",
                'fields' => implode(',', self::FILTER_RECIPE_FIELDS),
            ]);
            Log::debug("Retrieved recipe data {$response->body()}");

            /** @var array<mixed> $recipeData */
            $recipeData = $response->json();

            return XIVRecipe::hydrateFromRecipeSearchResponse($recipeData);
        } catch (Exception $ex) {
            Log::error("Failed to retrieve recipes for item {$itemID}", ['exception' => $ex, 'itemID' => $itemID]);
        }

        return new Collection();
    }

    public function fetchItem(int $itemID): ?XIVItem
    {
        Log::debug("Fetching item data for item {$itemID}");
        try {
            $filterColumns = [
                'Name',
                'Icon',
            ];

            $response = $this->request()->get("/sheet/Item/{$itemID}", [
                'fields' => implode(',', $filterColumns),
            ]);
            Log::debug("Retrieved item data {$response->body()}");

            /** @var array<mixed> $itemData */
            $itemData = $response->json();

            $xivItem = XIVItem::hydrateFromItemFetchResponse($itemData);
            $xivItem->Recipes = $this->fetchRecipesForItem($itemID);

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
            $response = Http::retry(3, 1000)->get("/search", [
                'sheets' => 'GilShopItem',
                'query' => "ItemResult={$itemID}",
                'fields' => 'Item.PriceLow,Item.PriceMid'
        ]);
            Log::debug("Retrieved vendor price data {$response->body()}");
            $vendorData = $response->json();

            return $vendorData['results'][0]['fields']['Item']['fields']['PriceMid'] ?? 0;
        } catch (Exception $ex) {
            Log::error("Failed to retrieve vendor data for item {$itemID}", ['exception' => $ex, 'itemID' => $itemID]);
        }

        return 0;
    }
}
