<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('module')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    private function seedDefaults(): void
    {
        $roles = [
            'hotel_manager' => ['Hotel Manager', 'Full access to hotel operations, reports, users, and settings.'],
            'cashier' => ['Cashier', 'Front desk, booking, payment, and restaurant cashier access.'],
            'chef' => ['Chef', 'Kitchen order viewing and status updates only.'],
        ];

        foreach ($roles as $name => [$displayName, $description]) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'description' => $description, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $permissions = [
            ['dashboard.view', 'View Dashboard', 'Dashboard'],
            ['rooms.manage', 'Manage Rooms', 'Rooms'],
            ['guests.manage', 'Manage Guests', 'Guests'],
            ['bookings.manage', 'Manage Bookings', 'Bookings'],
            ['checkin.manage', 'Check In / Check Out', 'Bookings'],
            ['restaurant_orders.manage', 'Manage Restaurant Orders', 'Restaurant'],
            ['kitchen_orders.view', 'View Kitchen Orders', 'Kitchen'],
            ['kitchen_orders.update_status', 'Update Kitchen Order Status', 'Kitchen'],
            ['menu_items.manage', 'Manage Menu Items', 'Restaurant'],
            ['payments.manage', 'Manage Payments', 'Payments'],
            ['reports.view', 'View Reports', 'Reports'],
            ['users.manage', 'Manage Users and Roles', 'Administration'],
            ['settings.sms.manage', 'Manage SMS Settings', 'Settings'],
            ['audit_trails.view', 'View Audit Trails', 'Administration'],
        ];

        foreach ($permissions as [$name, $displayName, $module]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'module' => $module, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $rolePermissions = [
            'hotel_manager' => array_column($permissions, 0),
            'cashier' => [
                'dashboard.view',
                'rooms.manage',
                'guests.manage',
                'bookings.manage',
                'checkin.manage',
                'restaurant_orders.manage',
                'payments.manage',
            ],
            'chef' => [
                'kitchen_orders.view',
                'kitchen_orders.update_status',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            foreach ($permissionNames as $permissionName) {
                $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $aliases = ['admin' => 'hotel_manager', 'user' => 'cashier'];
        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            $roleName = $aliases[$user->role] ?? $user->role;
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }
    }
};
