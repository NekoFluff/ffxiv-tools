<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_retainer', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('retainer_id')->constrained()->onDelete('cascade');
            $table->unique(['item_id', 'retainer_id']);
            $table->timestamps();
        });
    }
};
