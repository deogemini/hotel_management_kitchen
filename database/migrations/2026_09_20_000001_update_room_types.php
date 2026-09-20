<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rooms MODIFY room_type ENUM('Single', 'Executive', 'Deluxe', 'Suite') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rooms MODIFY room_type ENUM('Single', 'Double', 'Twin', 'Deluxe', 'Suite') NOT NULL");
    }
};
