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
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('base_url')->default('https://sms.flex.co.tz');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('sender_id')->default('Flex');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('sms_settings')->insert([
            'base_url' => 'https://sms.flex.co.tz',
            'client_id' => 'F00102',
            'client_secret' => '41274e60-a864-46e9-9ef6-12rf54tg',
            'sender_id' => 'Flex',
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
