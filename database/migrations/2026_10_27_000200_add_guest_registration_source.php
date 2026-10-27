<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('registration_source', 24)->default('staff');
        });

        DB::table('guests')
            ->whereNull('created_by')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('bookings')
                    ->whereColumn('bookings.guest_id', 'guests.id')
                    ->whereNotNull('bookings.public_request_token');
            })
            ->update(['registration_source' => 'website']);
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('registration_source');
        });
    }
};
