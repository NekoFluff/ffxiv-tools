<?php

use App\Models\Listing;
use App\Models\Sale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('sales')->update([
            'data_center' => 'Crystal',
            'server' => 'Goblin',
        ]);
    }
};
