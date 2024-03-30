<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $items = DB::table('items')->get();
        $chunkedItems = $items->chunk(1000);

        foreach ($chunkedItems as $chunk) {
            $insertData = [];

            foreach ($chunk as $item) {
                $insertData[] = [
                    'item_id' => $item->id,
                    'data_center' => 'Crystal',
                    'server' => 'Goblin',
                    'price' => $item->market_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('market_prices')->insert($insertData);
        }
    }
};
