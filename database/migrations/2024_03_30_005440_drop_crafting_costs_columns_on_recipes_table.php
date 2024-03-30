<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('purchase_cost');
            $table->dropColumn('market_craft_cost');
            $table->dropColumn('optimal_craft_cost');
        });
    }
};
