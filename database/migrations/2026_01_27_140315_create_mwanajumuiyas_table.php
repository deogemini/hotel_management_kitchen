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
        Schema::create('mwanajumuiyas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jumuiya_id')->constrained('jumuiyas')->onDelete('cascade');
            $table->string('jina_la_mwanajumuiya');
            $table->string('kadi_namba')->unique();
            $table->string('namba_ya_simu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mwanajumuiyas');
    }
};
