<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $recipes = DB::table('recipes')->get();
        $chunkedRecipes = $recipes->chunk(1000);

        foreach ($chunkedRecipes as $chunk) {
            $insertData = [];

            foreach ($chunk as $recipe) {
                $insertData[] = [
                    'recipe_id' => $recipe->id,
                    'data_center' => 'Crystal',
                    'server' => 'Goblin',
                    'purchase_cost' => $recipe->purchase_cost,
                    'market_craft_cost' => $recipe->market_craft_cost,
                    'optimal_craft_cost' => $recipe->optimal_craft_cost,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('crafting_costs')->insert($insertData);
        }
    }
};
