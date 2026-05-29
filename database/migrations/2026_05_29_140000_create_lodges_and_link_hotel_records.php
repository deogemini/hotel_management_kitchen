<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lodges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('location')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $defaultLodgeId = DB::table('lodges')->insertGetId([
            'name' => 'Lodge One',
            'location' => 'Main lodge',
            'phone_number' => null,
            'description' => 'Default lodge for existing rooms and records.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['users', 'rooms', 'guests', 'bookings', 'menu_items', 'restaurant_orders', 'other_charges', 'payments', 'invoices'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'lodge_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('lodge_id')->nullable()->after('id')->constrained('lodges')->nullOnDelete();
            });

            DB::table($tableName)->update(['lodge_id' => $defaultLodgeId]);
        }
    }

    public function down(): void
    {
        foreach (['invoices', 'payments', 'other_charges', 'restaurant_orders', 'menu_items', 'bookings', 'guests', 'rooms', 'users'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'lodge_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('lodge_id');
            });
        }

        Schema::dropIfExists('lodges');
    }
};
