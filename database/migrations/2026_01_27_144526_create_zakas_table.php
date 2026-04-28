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
        Schema::create('zakas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mwanajumuiya_id')->constrained('mwanajumuiyas')->onDelete('cascade');
            $table->decimal('kiasi', 12, 2);
            $table->string('risiti_namba')->unique();
            $table->string('mode_ya_malipo');
            $table->string('hali_ya_malipo')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zakas');
    }
};
