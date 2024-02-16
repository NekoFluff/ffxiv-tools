<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class XIVController extends Controller
{
    public function searchRecipeByName(string $name): ?Recipe
    {
        try {
            $search = file_get_contents("https://xivapi.com/search?string={$name}&string_algo=match");
            $search = json_decode($search);
        } catch (\Exception $e) {
            logger("Failed to retrieve search results for {$name}");
            return null;
        }

        $item = collect($search->Results)->filter(
            function ($item) {
                return $item->UrlType === "Item";
            }
        )->first();
        logger("Item Url: {$item->Url}");

        if (!$item) {
            return null;
        }

        return $this->searchRecipe($item->ID);
    }

    public function searchRecipe(string $itemID): ?Recipe
    {
        logger("Searching for item {$itemID}");

        $filter_columns = [
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
        $item = file_get_contents(
            // "https://xivapi.com{$item->Url}?columns=" . implode(",", $filter_columns)
            "https://xivapi.com/Item/{$itemID}?columns=" . implode(",", $filter_columns)
        );
        $item = json_decode($item);

        logger("<strong>{$item->Name} ({$item->ID})</strong>");
        logger("Item Lvl: {$item->LevelItem}");
        logger("<img src=\"https://xivapi.com/{$item->IconHD}\">");

        $recipe = collect($item->Recipes)->first();
        $recipeID = $recipe?->ID;
        if (!$recipe) {
            return null;
        }

        // Fetch from XIVAPI
        $recipe = self::getRecipe($recipeID);
        $this->reloadRecipeData($recipe);
        $recipe->alignAmounts(1);

        logger("Recipe: " . json_encode($recipe));

        return $recipe;
    }

    public function reloadRecipeData(Recipe $recipe)
    {
        $universalisController = new UniversalisController();
        $mb_data = $universalisController->getMarketBoardData("Goblin", $recipe->itemIDs());
        $recipe->populateCosts($mb_data);

        logger(json_encode($recipe));

        logger("Market Profit: " . ($recipe->market_price - $recipe->market_craft_cost) . " (" . ($recipe->market_price / $recipe->market_craft_cost * 100) . "%) ");
        logger("Optimal Profit: " . ($recipe->market_price - $recipe->optimal_craft_cost) . " (" . ($recipe->market_price / $recipe->optimal_craft_cost  * 100) . "%) ");
        // $last_week_sale_count = getLastWeekSaleCount("Goblin", $recipe->item_id);
        // logger("Last week sale count: {$last_week_sale_count}");
    }

    public function getVendorCost(int $item_id): int
    {
        $cacheKey = "vendor_gil_price_{$item_id}";
        $vendor_data = cache()->rememberForever($cacheKey, function () use ($item_id) {
            logger("Fetching vendor data for item {$item_id}");
            $vendor_data = file_get_contents("https://xivapi.com/item/{$item_id}?columns=GameContentLinks.GilShopItem.Item,PriceMid");
            logger("Retrieved data {$vendor_data}");
            $vendor_data = json_decode($vendor_data, true);
            return $vendor_data["GameContentLinks"]["GilShopItem"]["Item"] ? $vendor_data["PriceMid"] : 0;
        });
        return $vendor_data;
    }

    public static function getRecipe($id): ?Recipe
    {
        $recipe = cache()->remember('recipe_' . $id, now()->addMinutes(30), function () use ($id) {
            logger("Fetching recipe {$id}");
            return file_get_contents("https://xivapi.com/recipe/{$id}");
        });

        if ($recipe === false) {
            return null;
        }

        return Recipe::parseJson(json_decode($recipe, true));
    }
}
