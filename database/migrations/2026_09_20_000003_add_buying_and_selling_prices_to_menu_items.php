<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->decimal('buying_price', 12, 2)->default(0)->after('price');
            $table->decimal('selling_price', 12, 2)->default(0)->after('buying_price');
        });

        DB::table('menu_items')->update(['selling_price' => DB::raw('price')]);
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'selling_price']);
        });
    }
};
