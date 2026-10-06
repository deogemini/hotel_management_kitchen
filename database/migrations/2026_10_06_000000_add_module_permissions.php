<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['companies.manage', 'Manage Companies', 'Guests'],
        ['service_charges.manage', 'Manage Guest Services', 'Guest Services'],
        ['stocks.manage', 'Manage Stock', 'Stock'],
        ['purchases.manage', 'Manage Purchases', 'Purchases'],
        ['suppliers.manage', 'Manage Suppliers', 'Purchases'],
        ['expenses.manage', 'Manage Expenses', 'Expenses'],
        ['lodges.manage', 'Manage Lodges', 'Administration'],
        ['settings.invoice.manage', 'Manage Invoice Settings', 'Settings'],
    ];

    public function up(): void
    {
        foreach ($this->permissions as [$name, $displayName, $module]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'module' => $module,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $permissionId = DB::table('permissions')->where('name', $name)->value('id');

            foreach (['hotel_manager', 'Owner'] as $roleName) {
                $roleId = DB::table('roles')->where('name', $roleName)->value('id');

                if ($roleId) {
                    DB::table('permission_role')->updateOrInsert(
                        ['role_id' => $roleId, 'permission_id' => $permissionId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

        }

        $ownerRoleId = DB::table('roles')->where('name', 'Owner')->value('id');

        if ($ownerRoleId) {
            foreach (DB::table('permissions')->pluck('id') as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $ownerRoleId, 'permission_id' => $permissionId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($this->permissions, 0))
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
