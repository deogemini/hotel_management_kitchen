<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lodge_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('tin')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->json('details');
            $table->timestamps();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->json('bill_to')->nullable();
            $table->json('issuer_details')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['bill_to', 'issuer_details', 'due_date', 'notes']);
        });
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('companies');
    }
};
