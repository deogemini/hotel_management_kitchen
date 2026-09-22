<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE restaurant_orders MODIFY payment_method ENUM('Cash', 'Mobile money', 'LIPA NAMBA', 'Card', 'Room charge') NULL");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash', 'Mobile money', 'LIPA NAMBA', 'Card', 'Room charge') NOT NULL");
        DB::statement("ALTER TABLE other_charges MODIFY payment_method ENUM('Cash', 'Mobile money', 'LIPA NAMBA', 'Card', 'Room charge') NULL");
    }

    public function down(): void
    {
        DB::table('restaurant_orders')->where('payment_method', 'LIPA NAMBA')->update(['payment_method' => 'Mobile money']);
        DB::table('payments')->where('payment_method', 'LIPA NAMBA')->update(['payment_method' => 'Mobile money']);
        DB::table('other_charges')->where('payment_method', 'LIPA NAMBA')->update(['payment_method' => 'Mobile money']);
        DB::statement("ALTER TABLE restaurant_orders MODIFY payment_method ENUM('Cash', 'Mobile money', 'Card', 'Room charge') NULL");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash', 'Mobile money', 'Card', 'Room charge') NOT NULL");
        DB::statement("ALTER TABLE other_charges MODIFY payment_method ENUM('Cash', 'Mobile money', 'Card', 'Room charge') NULL");
    }
};
