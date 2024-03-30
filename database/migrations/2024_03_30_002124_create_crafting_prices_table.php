<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('crafting_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->string('data_center');
            $table->string('server');
            $table->integer('purchase_cost');
            $table->integer('market_craft_cost');
            $table->integer('optimal_craft_cost');
            $table->timestamps();
        });
    }
};
