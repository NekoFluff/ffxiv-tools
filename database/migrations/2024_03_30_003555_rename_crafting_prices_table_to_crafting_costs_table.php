<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('crafting_prices', function (Blueprint $table) {
            $table->rename('crafting_costs');
        });
    }
};
