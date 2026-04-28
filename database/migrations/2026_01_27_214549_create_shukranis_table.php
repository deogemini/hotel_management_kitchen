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
        Schema::create('shukranis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watoto_id')->constrained('watotos')->onDelete('cascade');
            $table->decimal('kiasi', 12, 2);
            $table->string('risiti_namba')->nullable();
            $table->string('mode_ya_malipo')->nullable();
            $table->string('hali_ya_malipo')->nullable();
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shukranis');
    }
};
