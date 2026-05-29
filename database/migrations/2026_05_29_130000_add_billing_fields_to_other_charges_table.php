<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_charges', function (Blueprint $table) {
            $table->string('service_type')->default('Other')->after('guest_id');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('balance_amount', 12, 2)->default(0)->after('paid_amount');
            $table->enum('payment_status', ['Unpaid', 'Partial', 'Paid'])->default('Unpaid')->after('balance_amount');
            $table->enum('payment_method', ['Cash', 'Mobile money', 'Card', 'Room charge'])->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('other_charges', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'paid_amount', 'balance_amount', 'payment_status', 'payment_method']);
        });
    }
};
