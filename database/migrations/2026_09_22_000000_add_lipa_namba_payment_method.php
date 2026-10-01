<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeMethods(['Cash', 'Mobile money', 'LIPA NAMBA', 'Card', 'Room charge']);
    }

    public function down(): void
    {
        foreach (['restaurant_orders', 'payments', 'other_charges'] as $table) {
            DB::table($table)->where('payment_method', 'LIPA NAMBA')->update(['payment_method' => 'Mobile money']);
        }
        $this->changeMethods(['Cash', 'Mobile money', 'Card', 'Room charge']);
    }

    private function changeMethods(array $methods): void
    {
        foreach (['restaurant_orders', 'payments', 'other_charges'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name, $methods) {
                $table->enum('payment_method', $methods)->nullable($name !== 'payments')->change();
            });
        }
    }
};
