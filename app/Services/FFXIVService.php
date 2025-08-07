<?php

namespace App\Services;

use App\Http\Clients\Universalis\UniversalisClientInterface;
use App\Http\Clients\XIV\XIVClientInterface;
use App\Http\Clients\XIV\XIVRecipe;
use App\Models\CraftingCost;
use App\Models\Enums\Server;
use App\Models\Ingredient;
use App\Models\Item;
use App\Models\Listing;
use App\Models\MarketPrice;
use App\Models\Recipe;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FFXIVService
{
    protected XIVClientInterface $xivClient;

    protected UniversalisClientInterface $universalisClient;

    public function __construct(XIVClientInterface $xivClient, UniversalisClientInterface $universalisClient)
    {
        $this->xivClient = $xivClient;
        $this->universalisClient = $universalisClient;
    }

    public function getRecipeByItemID(int $itemID): ?Recipe
    {
        $recipe = Recipe::with('ingredients', 'craftingCosts')->where('item_id', $itemID)->first();
        // TODO: Uncomment once I finish filling in all the recipes
        // if ($recipe) {
        //     return $recipe;
        // }

        // TODO: Uncomment once I finish filling in all the recipes
        // $item = Item::find($itemID);
        // if ($item) { // There are no recipes for this item
        //     return null;
        // }

        $item = $this->xivClient->fetchItem($itemID);
        if (! $item) {
            return null;
        }
        Item::updateOrCreate([
            'id' => intval($item->ID),
        ], [
            'name' => $item->Name,
            'icon' => $item->Icon,
        ]);

        // TODO: Delete once I finish filling in all the recipes since it's covered by the above
        if ($recipe) {
            return $recipe;
        }
        $xivRecipe = collect($item->Recipes)->first();
        if (! $xivRecipe) {
            return null;
        }

        $this->saveRecipe($xivRecipe);

        Log::debug('Recipe: '.json_encode($recipe));

        return $recipe;
    }

    public function getRecipe(int $recipeID, bool $forceRefresh = false): ?Recipe
    {
        if (! $forceRefresh) {
            $recipe = Recipe::with('ingredients')->where('id', $recipeID)->first();
            if ($recipe) {
                return $recipe;
            }
        }

        $recipeData = $this->xivClient->fetchRecipe($recipeID);
        if (empty($recipeData)) {
            return null;
        }

        $recipe = $this->saveRecipe($recipeData);
        $this->updateVendorPrices($recipe);

        return $recipe;
    }

    private function saveRecipe(XIVRecipe $xivRecipe): Recipe
    {
        $item = Item::updateOrCreate([
            'id' => $xivRecipe->ResultItem->ID,
        ], [
            'name' => $xivRecipe->ResultItem->Name,
            'icon' => $xivRecipe->ResultItem->Icon,
        ]);

        $recipe = Recipe::updateOrCreate([
            'id' => $xivRecipe->ID,
        ], [
            'amount_result' => $xivRecipe->AmountResult,
            'class_job' => $xivRecipe->ClassJobName,
            'class_job_level' => $xivRecipe->ClassJobLevel,
            'class_job_icon' => '',
            'item_id' => $item->id,
        ]);

        foreach ($xivRecipe->Ingredients as $ingredient) {
            $amount = $ingredient->Amount;
            if ($amount === 0) {
                continue;
            }

            $ingredient_item = Item::updateOrCreate([
                'id' => $ingredient->ItemID,
            ], [
                'name' => $ingredient->ItemName,
                'icon' => $ingredient->ItemIcon,
            ]);

            Ingredient::updateOrCreate([
                'recipe_id' => $recipe->id,
                'item_id' => $ingredient_item->id,
            ], [
                'amount' => $amount,
            ]);

            $this->getRecipeByItemID($ingredient->ItemID);
        }

        return $recipe;
    }

    public function updateVendorPrices(Recipe $recipe): void
    {
        $item = $recipe->item;
        $item->update([
            'vendor_price' => $this->getVendorCost($item->id),
        ]);

        foreach ($recipe->ingredients as $ingredient) {
            $item = $ingredient->item;
            $item->update([
                'vendor_price' => $this->getVendorCost($item->id),
            ]);
        }
    }

    public function getVendorCost(int $itemID): int
    {
        $cacheKey = "vendor_gil_price_{$itemID}";
        $vendor_data = cache()->rememberForever($cacheKey, function () use ($itemID) {
            return $this->xivClient->fetchVendorPrice($itemID);
        });

        return $vendor_data;
    }

    /**
     * Update the market prices for a recipe and its ingredients.
     *
     * @param  Recipe  $recipe  The recipe to update.
     * @param  Collection<int, EloquentCollection<int|string, Listing>>  $mbListings  The market board listings.
     */
    public function updateMarketPrices(Server $server, Recipe $recipe, Collection $mbListings): void
    {
        $listings = $mbListings[$recipe->item_id] ?? collect([]);
        $this->updateMarketPrice($server, $recipe->item, $listings);

        foreach ($recipe->ingredients as $ingredient) {
            $listings = $mbListings[$ingredient->item_id] ?? collect([]);
            $this->updateMarketPrice($server, $ingredient->item, $listings);

            if ($ingredient->craftingRecipe !== null) {
                $this->updateMarketPrices($server, $ingredient->craftingRecipe, $mbListings);
            }
        }
    }

    /**
     * Update the costs of a recipe
     *
     * @param  Recipe  $recipe  The recipe to update
     * @param  Server  $server  The server
     */
    public function updateRecipeCosts(Server $server, Recipe $recipe): void
    {
        foreach ($recipe->ingredients as $ingredient) {
            if ($ingredient->craftingRecipe !== null) {
                $this->updateRecipeCosts($server, $ingredient->craftingRecipe);
            }
        }

        $craftingCost = $recipe->craftingCost($server);
        if ($craftingCost === null) {
            $craftingCost = new CraftingCost([
                'data_center' => $server->dataCenter(),
                'server' => $server,
            ]);
            $recipe->craftingCosts()->save($craftingCost);
            $recipe->load('craftingCosts');
        }

        $this->updatePurchaseCost($server, $recipe);
        $this->updateMarketCraftCost($server, $recipe);
        $this->updateOptimalCraftCost($server, $recipe);
        $recipe->craftingCost($server)->save();
        $recipe->save();
    }

    /**
     * Updates the purchase cost of a recipe.
     *
     * @param  Server  $server  The server
     * @param  Recipe  $recipe  The recipe to update the purchase cost for
     */
    private function updatePurchaseCost(Server $server, Recipe $recipe): void
    {
        $cost = $recipe->item->marketPrice($server)?->price;
        if ($recipe->item->vendor_price != 0) {
            $cost = min($cost, $recipe->item->vendor_price);
        }

        // Log::debug("Purchase cost for item {$recipe->item->name}: {$cost}");
        $craftingCost = $recipe->craftingCost($server);
        $craftingCost->purchase_cost = $cost ?: MarketPrice::DEFAULT_MARKET_PRICE;
    }

    /**
     * Updates the market price of an item.
     *
     * @param  Server  $server  The server
     * @param  Item  $item  The item to update the market price for
     * @param  Collection<int, Listing>  $listings  The market board listings
     */
    public function updateMarketPrice(Server $server, Item $item, Collection $listings): void
    {
        $listings = $listings->sortBy('price_per_unit')->take(5);

        $median_cost = $listings->median('price_per_unit');

        $sum = 0;
        foreach ($listings as $listing) {
            $sum += $listing['price_per_unit'] * $listing['quantity'];
        }
        $avg_cost = $sum / max($listings->sum('quantity'), 1);

        // logger("Listings for item {$listings[0]->item->id}: " . json_encode($listings->toArray()));
        // logger("Market cost for item {$listings[0]->item->id}: avg={$avg_cost}, median={$median_cost}");
        $marketPrice = $item->marketPrice($server);
        if ($marketPrice === null) {
            $marketPrice = new MarketPrice([
                'data_center' => $server->dataCenter(),
                'server' => $server,
                'item_id' => $item->id,
                'price' => 0,
            ]);
            $marketPrice->updated_at = now();
            $marketPrice->price = intval(min($avg_cost, $median_cost)) ?: MarketPrice::DEFAULT_MARKET_PRICE;
            $item->marketPrices()->save($marketPrice);
            $item->load('marketPrices');
        } else {
            $marketPrice->updated_at = now();
            $marketPrice->price = intval(min($avg_cost, $median_cost)) ?: MarketPrice::DEFAULT_MARKET_PRICE;
            $marketPrice->save();
        }
    }

    private function updateOptimalCraftCost(Server $server, Recipe $recipe): void
    {
        $cost = 0;

        foreach ($recipe->ingredients as $ingredient) {
            $ingredientCraftingCost = $ingredient->craftingRecipe?->craftingCost($server);
            $ingredientMarketPrice = $ingredient->item->marketPrice($server)?->price;
            $minIngredientCost = $ingredientMarketPrice ?: MarketPrice::DEFAULT_MARKET_PRICE;
            if (! $ingredientMarketPrice && $ingredientCraftingCost !== null) {
                $minIngredientCost = $ingredientCraftingCost->optimal_craft_cost / $ingredient->craftingRecipe->amount_result;
            }
            if ($ingredient->craftingRecipe !== null && $ingredientCraftingCost !== null) {
                $minIngredientCost = min($minIngredientCost, $ingredientCraftingCost->optimal_craft_cost / $ingredient->craftingRecipe->amount_result);
            }
            if ($ingredient->item->vendor_price != 0) {
                $minIngredientCost = min($minIngredientCost, $ingredient->item->vendor_price);
            }

            $cost += $minIngredientCost * $ingredient->amount;
        }

        $craftingCost = $recipe->craftingCost($server);
        $craftingCost->optimal_craft_cost = intval($cost);
    }

    private function updateMarketCraftCost(Server $server, Recipe $recipe): void
    {
        $cost = 0;

        foreach ($recipe->ingredients as $ingredient) {
            $minIngredientCost = $ingredient->item->marketPrice($server)?->price ?: MarketPrice::DEFAULT_MARKET_PRICE;

            // If the market price is not available, use the crafting cost
            if (! $ingredient->item->marketPrice($server)?->price && $ingredient->craftingRecipe !== null) {
                $ingredientMarketCraftCost = $ingredient->craftingRecipe->craftingCost($server)?->market_craft_cost;
                $minIngredientCost = $ingredientMarketCraftCost / $ingredient->craftingRecipe->amount_result;
            }

            $cost += $minIngredientCost * $ingredient->amount;
        }

        $craftingCost = $recipe->craftingCost($server);
        $craftingCost->market_craft_cost = intval($cost);
    }

    /** @return array<mixed> */
    public function fetchMostRecentlyUpdatedItems(Server $server): array
    {
        return $this->universalisClient->fetchMostRecentlyUpdatedItems($server);
    }

    /**
     * @param  array<int>  $itemIDs
     * @return array<mixed>
     */
    public function fetchMarketboardListings(Server $server, array $itemIDs): array
    {
        return $this->universalisClient->fetchMarketBoardListings($server, $itemIDs);
    }

    /**
     * @param  array<int>  $itemIDs
     */
    public function refreshMarketboardListings(Server $server, array $itemIDs): void
    {
        if (empty($itemIDs)) {
            return;
        }

        $listingsData = $this->universalisClient->fetchMarketBoardListings($server, $itemIDs);

        $this->processMarketBoardListings($server, $listingsData);
    }

    /**
     * Process the market board listings data from Universalis.
     *
     * @param  Server  $server  The server
     * @param  array<mixed>  $listingsData  The listings data
     */
    private function processMarketBoardListings(Server $server, array $listingsData): void
    {
        $dataCenter = $server->dataCenter();
        $listingsData = collect($listingsData)->map(
            function ($listingData, $itemID) use ($dataCenter, $server): array {

                /** @var array<mixed> $listings */
                $listings = $listingData['listings'] ?? [];

                return array_map(
                    function ($entry) use ($dataCenter, $server, $itemID): array {
                        return [
                            'id' => $entry['listingID'],
                            'item_id' => $itemID,
                            'data_center' => $dataCenter,
                            'server' => $server,
                            'retainer_name' => $entry['retainerName'],
                            'retainer_city' => $entry['retainerCity'],
                            'quantity' => $entry['quantity'],
                            'price_per_unit' => $entry['pricePerUnit'],
                            'hq' => $entry['hq'],
                            'total' => $entry['total'],
                            'tax' => $entry['tax'],
                            'last_review_time' => Carbon::createFromTimestamp($entry['lastReviewTime']),
                        ];
                    },
                    $listings
                );
            }
        );

        $listingsData->each(function ($listings, $itemID) use ($server) {
            Listing::lockForUpdate()->upsert(
                $listings,
                ['id'],
                ['retainer_name', 'retainer_city', 'quantity', 'price_per_unit', 'hq', 'total', 'tax', 'last_review_time']
            );

            // Prune old listings
            Listing::where('server', $server)
                ->where('item_id', $itemID)
                ->whereNotIn('id', array_column($listings, 'id'))
                ->delete();
        });
    }

    /**
     * Retrieves the market board sales for a specific server and item.
     *
     * @param  Server  $server  The server name.
     * @param  int  $itemID  The ID of the item.
     */
    public function refreshMarketBoardSales(Server $server, int $itemID): void
    {
        $mbSales = $this->universalisClient->fetchMarketBoardSales($server, $itemID);

        $dataCenter = $server->dataCenter();
        $sales = collect($mbSales)->map(
            function ($entry) use ($dataCenter, $server, $itemID): array {
                return [
                    'data_center' => $dataCenter,
                    'server' => $server,
                    'item_id' => $itemID,
                    'quantity' => $entry['quantity'],
                    'price_per_unit' => $entry['pricePerUnit'],
                    'buyer_name' => $entry['buyerName'],
                    'timestamp' => Carbon::createFromTimestamp($entry['timestamp']),
                    'hq' => $entry['hq'],
                ];
            }
        );

        DB::transaction(function () use ($sales) {
            Sale::lockForUpdate()->upsert(
                $sales->toArray(),
                ['item_id', 'timestamp', 'buyer_name'],
                ['quantity', 'price_per_unit', 'hq']
            );
        }, 3);
    }

    public function getLastWeekSaleCount(Server $server, int $itemID): int
    {
        $cacheKey = "last_week_sale_count_{$itemID}";
        $sale_count = cache()->remember(
            $cacheKey,
            now()->addMinutes(60),
            function () use ($itemID, $server) {
                return $this->universalisClient->fetchLastWeekSaleCount($server, $itemID);
            }
        );

        return $sale_count;
    }
}
