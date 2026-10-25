<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('restaurant_orders', 'order_date')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->date('order_date')->nullable()->after('order_number');
            });
        }

        DB::table('restaurant_orders')->whereNull('order_date')->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                DB::table('restaurant_orders')->where('id', $order->id)->update([
                    'order_date' => $order->created_at
                        ? Carbon::parse($order->created_at)->toDateString()
                        : now()->toDateString(),
                ]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurant_orders', 'order_date')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropColumn('order_date');
            });
        }
    }
};
