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
        Schema::create('jumuiyas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanda_id')->constrained('kandas')->onDelete('cascade');
            $table->string('jina_la_jumuiya');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jumuiyas');
    }
};
