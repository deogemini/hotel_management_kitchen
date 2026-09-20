<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Owner',
            'display_name' => 'Owner',
            'description' => 'Can delete bookings and manage owner-level booking actions.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionId = DB::table('permissions')->where('name', 'bookings.manage')->value('id');

        if ($permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'Owner')->value('id');

        if ($roleId) {
            DB::table('permission_role')->where('role_id', $roleId)->delete();
            DB::table('users')->where('role_id', $roleId)->update([
                'role_id' => null,
                'role' => 'cashier',
            ]);
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
