<?php

namespace App\Services;

use App\Http\Clients\Universalis\UniversalisClientInterface;
use App\Http\Clients\XIV\XIVClientInterface;
use App\Models\CraftingCost;
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
        if ($recipe) {
            return $recipe;
        }

        $item = Item::find($itemID);
        if ($item) { // There are no recipes for this item
            return null;
        }

        $item = $this->xivClient->fetchItem($itemID);
        Item::updateOrCreate([
            'id' => intval($item->ID),
        ], [
            'name' => $item->Name,
            'icon' => $item->Icon,
        ]);
        $recipeObj = collect($item->Recipes)->first();
        if (! $recipeObj) {
            return null;
        }

        $recipe = $this->getRecipe($recipeObj->ID);

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

        if (! isset($recipeData['ItemResult'])) {
            return null;
        }

        $recipe = $this->parseRecipeJson($recipeData);
        $this->updateVendorPrices($recipe);

        $server = 'Goblin';
        $this->refreshMarketboardListings($server, $recipe->itemIDs());
        DB::transaction(function () use ($recipe, $server) {
            $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
            $this->updateMarketPrices($server, $recipe, $listings);
            $this->updateRecipeCosts($server, $recipe);
            $this->refreshMarketBoardSales($server, $recipe->item_id);
        });

        return $recipe;
    }

    public function parseRecipeJson(array $json): ?Recipe
    {
        $item = Item::updateOrCreate([
            'id' => intval($json['ItemResult']['ID']),
        ], [
            'name' => $json['Name'],
            'icon' => $json['Icon'],
        ]);

        $recipe = Recipe::updateOrCreate([
            'id' => $json['ID'],
        ], [
            'amount_result' => $json['AmountResult'],
            'class_job' => $json['ClassJob']['NameEnglish'],
            'class_job_level' => $json['RecipeLevelTable']['ClassJobLevel'],
            'class_job_icon' => $json['ClassJob']['Icon'],
            'item_id' => $item->id,
        ]);

        for ($i = 0; $i <= 9; $i++) {
            $amount = $json["AmountIngredient{$i}"];
            if ($amount === 0) {
                continue;
            }

            $ingredient_item = Item::updateOrCreate([
                'id' => $json["ItemIngredient{$i}"]['ID'],
            ], [
                'name' => $json["ItemIngredient{$i}"]['Name'],
                'icon' => $json["ItemIngredient{$i}"]['Icon'],
            ]);

            Ingredient::updateOrCreate([
                'recipe_id' => $recipe->id,
                'item_id' => $ingredient_item->id,
            ], [
                'amount' => $amount,
            ]);

            $ingredient_recipe_id = $json["ItemIngredientRecipe{$i}"][0]['ID'] ?? null;
            if ($ingredient_recipe_id !== null) {
                $ingredient_recipe = Recipe::where('id', $ingredient_recipe_id)->first();
                if ($ingredient_recipe === null) {
                    $this->getRecipe($ingredient_recipe_id);
                }
            }
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
    public function updateMarketPrices(string $server, Recipe $recipe, Collection $mbListings): void
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
     * @param  string  $server  The server name
     */
    public function updateRecipeCosts(string $server, Recipe $recipe): void
    {
        foreach ($recipe->ingredients as $ingredient) {
            if ($ingredient->craftingRecipe !== null) {
                $this->updateRecipeCosts($server, $ingredient->craftingRecipe);
            }
        }

        $craftingCost = $recipe->craftingCost($server);
        if ($craftingCost === null) {
            $craftingCost = new CraftingCost([
                'data_center' => self::dataCenterForServer($server),
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
     * @param  string  $server  The server name
     * @param  Recipe  $recipe  The recipe to update the purchase cost for
     */
    private function updatePurchaseCost(string $server, Recipe $recipe): void
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
     * @param  string $server The server name
     * @param  Item  $item  The item to update the market price for
     * @param  Collection<int, Listing>  $listings  The market board listings
     */
    public function updateMarketPrice(string $server, Item $item, Collection $listings): void
    {
        if ($listings->isEmpty()) {
            return;
        }

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
                'data_center' => self::dataCenterForServer($server),
                'server' => $server,
                'item_id' => $item->id,
                'price' => 0,
            ]);
            $item->marketPrices()->save($marketPrice);
            $item->load('marketPrices');
        }
        $marketPrice->updated_at = now();
        $marketPrice->price = intval(min($avg_cost, $median_cost)) ?: MarketPrice::DEFAULT_MARKET_PRICE;
        $marketPrice->save();
    }

    public static function dataCenterForServer(string $server): string
    {
        if (in_array($server, ['Adamantoise', 'Cactuar', 'Faerie', 'Gilgamesh', 'Jenova', 'Midgardsormr', 'Sargatanas', 'Siren'])) {
            return 'Aether';
        } elseif (in_array($server, ['Behemoth', 'Excalibur', 'Exodus', 'Famfrit', 'Hyperion', 'Lamia', 'Leviathan', 'Ultros'])) {
            return 'Primal';
        } elseif (in_array($server, ['Balmung', 'Brynhildr', 'Coeurl', 'Diabolos', 'Goblin', 'Malboro', 'Mateus', 'Zalera'])) {
            return 'Crystal';
        } elseif (in_array($server, ['Cerberus', 'Lich', 'Louisoix', 'Moogle', 'Odin', 'Omega', 'Phoenix', 'Ragnarok', 'Shiva', 'Twintania', 'Zodiark'])) {
            return 'Chaos';
        } elseif (in_array($server, ['Aegis', 'Atomos', 'Carbuncle', 'Garuda', 'Gungnir', 'Kujata', 'Ramuh', 'Tonberry', 'Typhon', 'Unicorn'])) {
            return 'Elemental';
        } elseif (in_array($server, ['Alexander', 'Bahamut', 'Durandal', 'Fenrir', 'Ifrit', 'Ridill', 'Tiamat', 'Ultima', 'Valefor', 'Yojimbo', 'Zeromus'])) {
            return 'Gaia';
        } elseif (in_array($server, ['Anima', 'Asura', 'Belias', 'Chocobo', 'Hades', 'Ixion', 'Mandragora', 'Masamune', 'Pandaemonium', 'Shinryu', 'Titan'])) {
            return 'Mana';
        } elseif (in_array($server, ['Aurora', 'Elemental', 'Gaia', 'Mana'])) {
            return 'EU';
        } elseif (in_array($server, ['Crystal', 'Primal', 'Aether'])) {
            return 'NA';
        } else {
            return 'Unknown';
        }
    }

    public static function validServers(): array
    {
        return [
            'Adamantoise', 'Aegis', 'Alexander', 'Anima', 'Asura', 'Atomos', 'Bahamut', 'Balmung', 'Behemoth', 'Belias', 'Brynhildr', 'Cactuar', 'Carbuncle', 'Cerberus', 'Chocobo', 'Coeurl', 'Diabolos', 'Durandal', 'Excalibur', 'Exodus', 'Famfrit', 'Fenrir', 'Garuda', 'Gilgamesh', 'Goblin', 'Gungnir', 'Hades', 'Hyperion', 'Ifrit', 'Ixion', 'Jenova', 'Kujata', 'Lamia', 'Leviathan', 'Lich', 'Louisoix', 'Malboro', 'Mandragora', 'Masamune', 'Mateus', 'Midgardsormr', 'Moogle', 'Odin', 'Omega', 'Pandaemonium', 'Phoenix', 'Ragnarok', 'Ramuh', 'Ridill', 'Sargatanas', 'Shinryu', 'Shiva', 'Siren', 'Tiamat', 'Titan', 'Tonberry', 'Typhon', 'Ultima', 'Ultros', 'Unicorn', 'Valefor', 'Yojimbo', 'Zalera', 'Zeromus', 'Zodiark',
        ];
    }

    private function updateOptimalCraftCost(string $server, Recipe $recipe): void
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

    private function updateMarketCraftCost(string $server, Recipe $recipe): void
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

    public function fetchMarketboardListings(string $server, array $itemIDs): array
    {
        return $this->universalisClient->fetchMarketBoardListings($server, $itemIDs);
    }

    public function refreshMarketboardListings(string $server, array $itemIDs): void
    {
        $listingsData = $this->universalisClient->fetchMarketBoardListings($server, $itemIDs);
        Listing::whereIn('item_id', $itemIDs)->delete();

        $this->processMarketBoardListings($server, $listingsData);

        $dataCenter = self::dataCenterForServer($server);
        $sales = collect($listingsData)->map(
            function ($listingData, $itemID) use ($dataCenter, $server) {

                /** @var array $l */
                $l = $listingData['recentHistory'] ?? [];

                return array_map(
                    function ($entry) use ($dataCenter, $server, $itemID): array {
                        return [
                            'item_id' => $itemID,
                            'data_center' => $dataCenter,
                            'server' => $server,
                            'quantity' => $entry['quantity'],
                            'price_per_unit' => $entry['pricePerUnit'],
                            'buyer_name' => $entry['buyerName'],
                            'timestamp' => Carbon::createFromTimestamp($entry['timestamp']),
                            'hq' => $entry['hq'],
                        ];
                    },
                    $l
                );
            }
        )->flatten(1);

        // Upsert in chunks of 100
        $sales->chunk(100)->each(
            function ($chunk) {
                Sale::upsert(
                    $chunk->toArray(),
                    ['item_id', 'timestamp', 'buyer_name'],
                    ['quantity', 'price_per_unit', 'hq']
                );
            }
        );
    }

    /**
     * Process the market board listings data from Universalis.
     *
     * @param  string $server The server name.
     * @param  array  $listingsData  The listings data.
     */
    private function processMarketBoardListings(string $server, array $listingsData): void
    {
        $dataCenter = self::dataCenterForServer($server);
        $listings = collect($listingsData)->map(
            function ($listingData, $itemID) use ($dataCenter, $server): array {

                /** @var array $l */
                $l = $listingData['listings'] ?? [];

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
                    $l
                );
            }
        )->flatten(1);

        // Upsert in chunks of 100
        $listings->chunk(100)->each(
            function ($chunk) {
                Listing::upsert(
                    $chunk->toArray(),
                    ['id'],
                    ['retainer_name', 'retainer_city', 'quantity', 'price_per_unit', 'hq', 'total', 'tax', 'last_review_time']
                );
            }
        );
    }

    /**
     * Retrieves the market board sales for a specific server and item.
     *
     * @param  string  $server  The server name.
     * @param  int  $itemID  The ID of the item.
     */
    public function refreshMarketBoardSales(string $server, int $itemID): void
    {
        $mbSales = $this->universalisClient->fetchMarketBoardSales($server, $itemID);

        $dataCenter = self::dataCenterForServer($server);
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

        Sale::upsert(
            $sales->toArray(),
            ['item_id', 'timestamp', 'buyer_name'],
            ['quantity', 'price_per_unit', 'hq']
        );
    }

    /**
     * Returns Sales aggregated daily for the last week
     *
     * @param  Collection<int, Sale>  $sales
     * @return Collection<int, array{date: mixed, quantity: mixed, avg_price: float|int|null, median_price: float|int|null, min_price: mixed, max_price: mixed}>
     */
    public function aggregateSales(Collection $sales): Collection
    {
        $aggregatedSales = collect($sales)->groupBy(
            function ($entry) {
                return $entry['timestamp']->format('Y-m-d');
            }
        )->map(
            function ($entries, $date) {
                return [
                    'date' => $date,
                    'quantity' => collect($entries)->sum('quantity'),
                    'avg_price' => collect($entries)->avg('price_per_unit'),
                    'median_price' => collect($entries)->median('price_per_unit'),
                    'min_price' => collect($entries)->min('price_per_unit'),
                    'max_price' => collect($entries)->max('price_per_unit'),
                ];
            }
        )->reverse()->values();

        // Add missing days in the last week without quantity
        $lastWeekDates = [
            date('Y-m-d', intval(now()->subDays(0)->timestamp)),
            date('Y-m-d', intval(now()->subDays(1)->timestamp)),
            date('Y-m-d', intval(now()->subDays(2)->timestamp)),
            date('Y-m-d', intval(now()->subDays(3)->timestamp)),
            date('Y-m-d', intval(now()->subDays(4)->timestamp)),
            date('Y-m-d', intval(now()->subDays(5)->timestamp)),
            date('Y-m-d', intval(now()->subDays(6)->timestamp)),
            date('Y-m-d', intval(now()->subDays(7)->timestamp)),
        ];
        $missingDates = collect($lastWeekDates)->diff($aggregatedSales->pluck('date'));
        $missingDates->each(
            function ($date) use (&$aggregatedSales) {
                $aggregatedSales->push(
                    [
                        'date' => $date,
                        'quantity' => 0,
                        'median_price' => 0,
                        'avg_price' => 0,
                        'min_price' => 0,
                        'max_price' => 0,
                    ]
                );
            }
        );

        return $aggregatedSales->sortBy('date')->values();
    }

    public function getLastWeekSaleCount(string $server, int $itemID): int
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
