<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        'companies.manage',
        'service_charges.manage',
        'stocks.manage',
        'purchases.manage',
        'expenses.manage',
    ];

    public function up(): void
    {
        $cashierRoleId = DB::table('roles')->where('name', 'cashier')->value('id');

        if (! $cashierRoleId) {
            return;
        }

        foreach (DB::table('permissions')->whereIn('name', $this->permissions)->pluck('id') as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $cashierRoleId, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $cashierRoleId = DB::table('roles')->where('name', 'cashier')->value('id');
        $permissionIds = DB::table('permissions')->whereIn('name', $this->permissions)->pluck('id');

        if ($cashierRoleId) {
            DB::table('permission_role')
                ->where('role_id', $cashierRoleId)
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }
    }
};
