<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('retainer_name');
            $table->integer('retainer_city');
            $table->integer('quantity');
            $table->boolean('hq');
            $table->integer('price_per_unit');
            $table->integer('total');
            $table->integer('tax');
            $table->dateTime('last_review_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
