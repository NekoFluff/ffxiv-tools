<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     sleep(2);
//     return inertia(
//         'Home',
//         [
//             "username" => "John Doe",
//         ]
//     );
// });

// Route::get(
//     '/about',
//     function () {
//         return inertia('About');
//     }
// );

Route::get('/', function () {
    return inertia(
        'Recipes',
        [
            "recipe" => null,
        ]
    );
});

Route::get('/{itemID}', function ($itemID) {
    if ($itemID) {
        $recipe = searchRecipe($itemID);
    } else {
        $recipe = null;
    }

    return inertia(
        'Recipes',
        [
            "recipe" => $recipe,
        ]
    );
})->where('name', '.*');

function searchRecipe(string $itemID): ?Recipe
{
    logger("Searching for item {$itemID}");
    // $search = file_get_contents("https://xivapi.com/search?string={$name}");
    // $search = json_decode($search);

    // $item = collect($search->Results)->filter(
    //     function ($item) {
    //         return $item->UrlType === "Item";
    //     }
    // )->first();
    // logger("Item Url: {$item->Url}");

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

    if ($recipe) {
        $recipe = Recipe::get($recipe->ID);
        $recipe->alignAmounts(1);
        $mb_data = getMarketBoardData("Goblin", $recipe->itemIDs());
        $recipe->populateCosts($mb_data);
        logger(json_encode($recipe));

        logger("Market Profit: " . ($recipe->market_cost - $recipe->market_craft_cost) . " (" . ($recipe->market_cost / $recipe->market_craft_cost * 100) . "%) ");
        logger("Optimal Profit: " . ($recipe->market_cost - $recipe->optimal_craft_cost) . " (" . ($recipe->market_cost / $recipe->optimal_craft_cost  * 100) . "%) ");

    // $last_week_sale_count = getLastWeekSaleCount("Goblin", $recipe->item_id);
    // logger("Last week sale count: {$last_week_sale_count}");
    } else {
        $recipe = null;
    }

    return $recipe;
}

class Ingredient
{
    public int $item_id;

    public string $name;

    public float $amount;

    public int $market_cost;

    public int $vendor_cost;

    public string $icon;

    public ?Recipe $recipe;
}

class Recipe
{
    public int $id;

    public int $item_id;

    public string $name;

    public string $icon;

    /** @var Ingredient[] $ingredients */
    public array $ingredients;

    public float $amount_result;

    public int $market_cost;

    public int $market_craft_cost;

    public int $optimal_craft_cost;

    public int $vendor_cost;

    public string $class_job;

    public string $class_job_icon;

    public static function get($id): ?Recipe
    {
        $recipe = cache()->remember('recipe_' . $id, now()->addMinutes(30), function () use ($id) {
            logger("Fetching recipe {$id}");
            return file_get_contents("https://xivapi.com/recipe/{$id}");
        });

        if ($recipe === false) {
            return null;
        }

        return static::parseJson(json_decode($recipe, true));
    }

    public static function parseJson(array $json): ?Recipe
    {
        $recipe = new Recipe();
        $recipe->id = $json["ID"];
        $recipe->item_id = $json["ItemResult"]["ID"];
        $recipe->name = $json["Name"];
        $recipe->amount_result = $json["AmountResult"];
        $recipe->vendor_cost = getVendorCost($recipe->item_id);
        $recipe->icon = $json["Icon"];
        $recipe->class_job = $json["ClassJob"]["Name"];
        $recipe->class_job_icon = $json["ClassJob"]["Icon"];

        $recipe->ingredients = [];
        for ($i = 0; $i <= 9; $i++) {
            $amount = $json["AmountIngredient{$i}"];
            if ($amount === 0) {
                continue;
            }

            $ingredient = new Ingredient();
            $ingredient->item_id = $json["ItemIngredient{$i}"]["ID"];
            $ingredient->name = $json["ItemIngredient{$i}"]["Name"];
            $ingredient->amount = $amount;
            $ingredient->vendor_cost = getVendorCost($ingredient->item_id);
            $ingredient->icon = $json["ItemIngredient{$i}"]["Icon"];
            $ingredient->recipe = null;

            $ingredient_recipe = $json["ItemIngredientRecipe{$i}"];
            if ($ingredient_recipe !== null) {
                $ingredient->recipe = static::get($ingredient_recipe[0]["ID"]);
            }
            $recipe->ingredients[] = $ingredient;
        }

        return $recipe;
    }

    public function alignAmounts(float $target_amount)
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        foreach ($this->ingredients as $ingredient) {
            $ingredient->amount = $ratio * $ingredient->amount;
            if ($ingredient->recipe !== null) {
                $ingredient->recipe->alignAmounts($ingredient->amount);
            }
        }
    }

    public function itemIDs(): array
    {
        $ids = [];
        $ids[] = $this->item_id;
        foreach ($this->ingredients as $ingredient) {
            $ids[] = $ingredient->item_id;
            if ($ingredient->recipe !== null) {
                $ids = array_merge($ids, $ingredient->recipe->itemIDs());
            }
        }

        return $ids;
    }

    public function populateCosts($mb_data)
    {
        $this->market_cost = 0;
        $this->market_craft_cost = 0;
        $this->optimal_craft_cost = 0;

        $mb_item = $mb_data["items"][$this->item_id] ?? null;
        if ($mb_item !== null) {
            $this->market_cost = $this->calculateMarketCost($mb_item);
        }

        foreach ($this->ingredients as &$ingredient) {
            $mb_item = $mb_data["items"][$ingredient->item_id] ?? null;
            if ($mb_item !== null) {
                $ingredient->market_cost = $this->calculateMarketCost($mb_item);
            }

            if ($ingredient->recipe !== null) {
                $ingredient->recipe->populateCosts($mb_data);
            }
        }

        $this->market_craft_cost = $this->calculateCraftCost(false);
        $this->optimal_craft_cost = $this->calculateCraftCost(true);
    }

    private function calculateMarketCost(array $mb_item): int
    {
        $listings = collect($mb_item["listings"])
            ->take(10);

        $median_cost = $listings->median('pricePerUnit');

        $sum = 0;
        foreach ($listings as $listing) {
            $sum += $listing['pricePerUnit'] * $listing['quantity'];
        }
        $avg_cost = $sum / max($listings->sum('quantity'), 1);

        logger("Market cost for item {$mb_item["itemID"]}: avg={$avg_cost}, median={$median_cost}");
        return min($avg_cost, $median_cost) ?? 0;
    }

    private function calculateCraftCost(bool $optimal): int
    {
        $cost = 0;
        foreach ($this->ingredients as $ingredient) {
            $min_cost = $ingredient->market_cost ?? $ingredient->recipe->calculateCraftCost($optimal);
            if ($optimal && $ingredient->recipe !== null) {
                $min_cost = min($min_cost, $ingredient->recipe->calculateCraftCost($optimal));
            }
            if ($optimal && $ingredient->vendor_cost != 0) {
                $min_cost = min($min_cost, $ingredient->vendor_cost);
            }
            $cost += $min_cost * $ingredient->amount;
        }

        return $cost;
    }
}

function getVendorCost(int $item_id): int
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

function getMarketBoardData(string $server, array $item_ids): array
{
    $item_ids = array_unique($item_ids);
    sort($item_ids);

    logger("Fetching market board data for server {$server}." . " | Items: " . implode(",", $item_ids));
    $mb_data = file_get_contents(
        "https://universalis.app/api/v2/{$server}/" . implode(",", $item_ids)
    );
    logger("Retrieved market board data for server {$server}");
    $mb_data = json_decode($mb_data, true);
    return $mb_data;
}

function getLastWeekSaleCount(string $server, int $item_id): int
{
    $cacheKey = "last_week_sale_count_{$item_id}";
    $sale_count = cache()->remember(
        $cacheKey,
        now()->addMinutes(60),
        function () use ($item_id, $server) {
            logger("Fetching last week sale count for item {$item_id}");
            $mb_history = file_get_contents("https://universalis.app/api/v2/history/{$server}/{$item_id}");
            $mb_history = json_decode($mb_history, true);
            return collect($mb_history["entries"])->map(
                function ($entry) {
                    return $entry["quantity"];
                }
            )->sum();
        }
    );
    return $sale_count;
}
