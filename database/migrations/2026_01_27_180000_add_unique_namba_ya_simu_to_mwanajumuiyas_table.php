<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mwanajumuiyas', function (Blueprint $table) {
            $table->unique('namba_ya_simu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mwanajumuiyas', function (Blueprint $table) {
            $table->dropUnique(['namba_ya_simu']);
        });
    }
};
