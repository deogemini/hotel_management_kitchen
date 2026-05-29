<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Lodge;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $defaultLodge = Lodge::firstOrCreate(
            ['name' => 'Lodge One'],
            ['location' => 'Main lodge', 'description' => 'Default lodge for existing rooms and records.']
        );

        if (!User::where('role', 'hotel_manager')->exists()) {
            $role = Role::where('name', 'hotel_manager')->first();
            User::create([
                'name' => 'Hotel Manager',
                'email' => 'manager@hotel.test',
                'phone' => '0700000000',
                'password' => bcrypt('password'),
                'role' => 'hotel_manager',
                'role_id' => $role?->id,
                'lodge_id' => $defaultLodge->id,
            ]);
        }

        if (!User::where('email', 'test@example.com')->exists()) {
            $role = Role::where('name', 'cashier')->first();
            User::factory()->create([
                'name' => 'Cashier User',
                'email' => 'test@example.com',
                'role' => 'cashier',
                'role_id' => $role?->id,
                'lodge_id' => $defaultLodge->id,
            ]);
        }
    }

    private function seedRolesAndPermissions(): void
    {
        $roles = [
            'hotel_manager' => ['Hotel Manager', 'Full access to hotel operations, reports, users, and settings.'],
            'cashier' => ['Cashier', 'Front desk, booking, payment, and restaurant cashier access.'],
            'chef' => ['Chef', 'Kitchen order viewing and status updates only.'],
        ];

        foreach ($roles as $name => [$displayName, $description]) {
            Role::updateOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'description' => $description]
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
            Permission::updateOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'module' => $module]
            );
        }

        $rolePermissions = [
            'hotel_manager' => Permission::pluck('name')->all(),
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
            $role = Role::where('name', $roleName)->first();
            $role?->permissions()->sync(Permission::whereIn('name', $permissionNames)->pluck('id'));
        }
    }
}
