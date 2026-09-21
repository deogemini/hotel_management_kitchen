<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'bar_counter' => ['Bar Counter', 'Bar counter operations.'],
            'reception' => ['Reception', 'Reception and front desk operations.'],
        ] as $name => [$displayName, $description]) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', ['bar_counter', 'reception'])->delete();
    }
};
