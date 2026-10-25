<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'is_room_payment')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->boolean('is_room_payment')->default(false)->after('booking_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'is_room_payment')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('is_room_payment');
            });
        }
    }
};
