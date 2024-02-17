<?php

namespace App\Console\Commands;

use App\Http\Controllers\UniversalisController;
use App\Http\Controllers\XIVController;
use App\Models\Recipe;
use Illuminate\Console\Command;

class CalculateRecipeProfit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:recipeProfit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate the profit of a list of recipes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $itemNames = ['Amdapori Wall Lantern', 'Large Woven Rug', 'Ahriman Vase', 'Nymian Wall Lantern', 'Drachen Armor Augmentation', 'Highland Barding', 'Large Garden Pond', 'Double Feather Bed', 'Levin Barding', "Champion's Lance", 'Taffeta Loincloth', 'Jade Crook', 'Woolen Gaskins', 'Sheep Rug', 'Oasis Pillar', 'Taffeta Shawl', 'Potted Dragon Tree', 'Riviera Wardrobe', 'Cobalt Haubergeon', 'Aeolian Scimitar', 'Kotatsu Table', 'Manor Sofa', 'Hardsilver Bangle of Casting', 'Manor Couch', 'Glade Wardrobe', 'Walnut Cartonnier', 'Ash Cabinet', 'Ice Barding', "Belah'dian Crystal Lantern", 'Tidal Barding', 'Wine Barrel', 'Titanium Mask of Striking', 'South Seas Couch', 'Tatami Mat', 'Masonwork Interior Wall', 'Cobalt Winglet', 'Mythril Ear Cuffs', 'Display Stand', 'Cotton Doublet Vest of Crafting', 'Tier 1 Aquarium', 'Marble Flooring', 'Manor Fireplace', 'Oasis Couch', 'Mythrite Bangle of Aiming', 'Holy Cedar Composite Bow', 'Bathroom Floor Tiles', "Initiate's Thighboots", 'Horn Staff', 'Easel', 'Glade Pillar', 'Glade Half Partition', 'Mythril Choker', 'Oasis Stall', 'Sanguine Scepter', 'Dhalmelskin Leggings of Scouting', 'Mythril Cuirass', 'Potted Spider Plant', 'Oak Composite Bow', "Madman's Whispering Rod", 'Ivy Pillar', 'Manor Candelabra', "Boarskin Smithy's Gloves", "Vamper's Knives", 'Boarskin Ringbands', "Erudite's Picatrix of Casting", 'Toadskin Jacket', 'Budding Rosewood Wand', 'Mythril Sollerets', 'Raptorskin Jerkin', 'Moonfire Sandals', "Chirurgeon's Curtain", 'Mahogany Partition Door', 'Oaken Bench', 'Cobalt Halberd', 'Mythrite Earrings of Gathering', 'Rainbow Ribbon of Fending', 'Wyvernskin Workboots', 'Yew Longbow', 'Corner Counter', "Barbarian's Bardiche", 'Brass Wristlets of Crafting', 'Archaeoskin Breeches of Crafting', 'Electrum Circlet (Spinel)', 'Planter Box', 'Wolf Earrings', 'Straight Stepping Stones', "Potted Oliphant's Ear", 'Tailor-made Eel Pie', 'Red Carpet', "Bridesmaid's Tights", 'Masonwork Stove', 'Mythril Earrings', 'Corner Hedge Partition', 'Company Chest', 'Mythrite Bangle of Fending', 'Fingerless Raptorskin Gloves', 'Linen Shirt', 'Mythril Wristlets of Crafting', 'Steel Frypan', 'Oak Longbow'];

        $xivController = new XIVController();
        $universalisController = new UniversalisController();

        $results = [];

        foreach ($itemNames as $itemName) {
            $recipe = Recipe::whereRelation('item', 'name', $itemName)->first();
            if (!$recipe) {
                $recipe = $xivController->searchRecipeByName($itemName);
                sleep(1);
            } else {
                // Refresh the market board data for the recipe
                if ($recipe->updated_at->diffInHours(now()) > 24) {
                    $mb_data = $universalisController->getMarketBoardListings("Goblin", $recipe->itemIDs());
                    if (!$mb_data) {
                        continue;
                    }
                    $recipe->populateCosts($mb_data);
                }
            }

            if ($recipe) {
                if ($recipe->optimal_craft_cost == 0) {
                    continue;
                }

                $recipe->alignAmounts(1);

                $this->info('-------------- ' . $recipe->item->name . ' (' . $recipe->item_id . ') --------------');
                $this->info("Market Cost: " . $recipe->item->market_price);
                $this->info("Purchase Cost: " . $recipe->purchase_cost);
                $this->info("Market Craft Cost: " . $recipe->market_craft_cost);
                $this->info("Optimal Craft: " . $recipe->optimal_craft_cost);
                $this->info("Profit: " . $recipe->item->market_price * $recipe->amount_result - $recipe->optimal_craft_cost);
                $this->info("Profit Ratio: " . ($recipe->item->market_price * $recipe->amount_result / $recipe->optimal_craft_cost  * 100) - 100 . "%");

                $results[] = [
                    'name' => $recipe->item->name,
                    'item_id' => $recipe->item_id,
                    'market_price' => $recipe->item->market_price,
                    'purchase_cost' => $recipe->purchase_cost,
                    'market_craft_cost' => $recipe->market_craft_cost,
                    'optimal_craft_cost' => $recipe->optimal_craft_cost,
                    'profit' => $recipe->item->market_price * $recipe->amount_result - $recipe->optimal_craft_cost,
                    'profit_ratio' => ($recipe->item->market_price * $recipe->amount_result / $recipe->optimal_craft_cost  * 100) - 100,
                ];
            }

        }

        usort($results, function ($a, $b) {
            return $b['profit_ratio'] <=> $a['profit_ratio'];
        });

        $this->info('-------------- Results --------------');
        $this->info(json_encode($results));

        $datetime = date('Y-m-d_H-i-s');
        $filename = 'recipe_profit_' . $datetime . '.json';
        file_put_contents($filename, json_encode($results));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info('Runtime: ' . $executionTime . ' seconds');
    }
}
