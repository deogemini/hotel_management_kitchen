<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('purchases', fn (Blueprint $table) => $table->boolean('affects_stock')->default(true)->after('notes')); }
    public function down(): void { Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn('affects_stock')); }
};
