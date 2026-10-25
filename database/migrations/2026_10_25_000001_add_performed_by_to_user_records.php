<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'rooms', 'room_images', 'guests', 'bookings', 'menu_items', 'restaurant_orders',
        'other_charges', 'invoices', 'payments', 'companies', 'purchases', 'stock_movements',
        'expenses', 'suppliers', 'lodges', 'kandas', 'jumuiyas', 'mwanajumuiyas', 'zakas',
        'watotos', 'shukranis', 'users', 'roles', 'sms_settings', 'invoice_settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'performed_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        foreach ([
            'rooms' => 'created_by',
            'guests' => 'created_by',
            'bookings' => 'created_by',
            'menu_items' => 'created_by',
            'restaurant_orders' => 'created_by',
            'other_charges' => 'created_by',
            'invoices' => 'issued_by',
            'payments' => 'received_by',
            'purchases' => 'created_by',
            'stock_movements' => 'created_by',
            'expenses' => 'created_by',
            'suppliers' => 'created_by',
        ] as $tableName => $actorColumn) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $actorColumn)) {
                DB::table($tableName)
                    ->whereNull('performed_by')
                    ->whereNotNull($actorColumn)
                    ->update(['performed_by' => DB::raw($actorColumn)]);
            }
        }

        if (Schema::hasTable('audit_trails')) {
            foreach ([
                'rooms' => App\Models\Room::class,
                'room_images' => App\Models\RoomImage::class,
                'guests' => App\Models\Guest::class,
                'bookings' => App\Models\Booking::class,
                'menu_items' => App\Models\MenuItem::class,
                'restaurant_orders' => App\Models\RestaurantOrder::class,
                'other_charges' => App\Models\OtherCharge::class,
                'invoices' => App\Models\Invoice::class,
                'payments' => App\Models\Payment::class,
                'purchases' => App\Models\Purchase::class,
                'stock_movements' => App\Models\StockMovement::class,
                'expenses' => App\Models\Expense::class,
                'suppliers' => App\Models\Supplier::class,
                'lodges' => App\Models\Lodge::class,
                'kandas' => App\Models\Kanda::class,
                'jumuiyas' => App\Models\Jumuiya::class,
                'mwanajumuiyas' => App\Models\Mwanajumuiya::class,
                'zakas' => App\Models\Zaka::class,
                'watotos' => App\Models\Watoto::class,
                'shukranis' => App\Models\Shukrani::class,
                'users' => App\Models\User::class,
                'roles' => App\Models\Role::class,
                'sms_settings' => App\Models\SmsSetting::class,
                'invoice_settings' => App\Models\InvoiceSetting::class,
            ] as $tableName => $modelType) {
                if (! Schema::hasTable($tableName)) {
                    continue;
                }

                DB::table($tableName)
                    ->select('id')
                    ->whereNull('performed_by')
                    ->orderBy('id')
                    ->chunkById(500, function ($records) use ($tableName, $modelType): void {
                        foreach ($records as $record) {
                            $userId = DB::table('audit_trails')
                                ->where('auditable_type', $modelType)
                                ->where('auditable_id', $record->id)
                                ->whereNotNull('user_id')
                                ->orderByDesc('id')
                                ->value('user_id');

                            if ($userId) {
                                DB::table($tableName)->where('id', $record->id)->update(['performed_by' => $userId]);
                            }
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'performed_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['performed_by']);
                $table->dropColumn('performed_by');
            });
        }
    }
};
