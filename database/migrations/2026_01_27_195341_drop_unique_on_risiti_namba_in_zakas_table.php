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
        Schema::table('zakas', function (Blueprint $table) {
            $table->dropUnique('zakas_risiti_namba_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zakas', function (Blueprint $table) {
            $table->unique('risiti_namba');
        });
    }
};
