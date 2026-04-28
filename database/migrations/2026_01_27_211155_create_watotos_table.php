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
        Schema::create('watotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jumuiya_id')->constrained('jumuiyas')->onDelete('cascade');
            $table->string('jina_la_mtoto');
            $table->date('tarehe_ya_kuzaliwa')->nullable();
            $table->string('namba_ya_mzazi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watotos');
    }
};
