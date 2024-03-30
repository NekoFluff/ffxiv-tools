<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('data_center');
            $table->string('server');
            $table->integer('price');
            $table->timestamps();

            $table->unique(['item_id', 'data_center', 'server']);
        });
    }
};
