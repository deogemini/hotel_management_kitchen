<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('public_request_token')->nullable()->unique();
            $table->uuid('public_confirmation_token')->nullable()->unique();
            $table->unsignedInteger('number_of_adults')->default(1);
            $table->unsignedInteger('number_of_children')->default(0);
            $table->text('special_requests')->nullable();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('public_bed_type')->nullable();
            $table->unsignedSmallInteger('public_capacity')->nullable();
            $table->boolean('public_tax_included')->nullable();
            $table->boolean('public_breakfast_included')->nullable();
        });

        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lodge_id')->nullable()->constrained('lodges')->nullOnDelete();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('introduction_title')->nullable();
            $table->text('introduction')->nullable();
            $table->string('contact_email')->default('info@hardrockexecutivelodge.co.tz');
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('map_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('check_in_time')->nullable();
            $table->string('check_out_time')->nullable();
            $table->string('currency_code', 3)->default('TZS');
            $table->text('guest_policies')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('booking_policy')->nullable();
            $table->longText('cancellation_policy')->nullable();
            $table->longText('payment_policy')->nullable();
            $table->boolean('privacy_policy_approved')->default(false);
            $table->boolean('booking_policy_approved')->default(false);
            $table->boolean('cancellation_policy_approved')->default(false);
            $table->boolean('payment_policy_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('website_entries', function (Blueprint $table) {
            $table->id();
            $table->string('category', 24)->index();
            $table->string('image_category', 24)->default('Facilities');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('website_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('message');
            $table->string('delivery_status', 24)->default('saved');
            $table->string('status', 24)->default('New')->index();
            $table->timestamps();
        });

        DB::table('permissions')->updateOrInsert(
            ['name' => 'website.manage'],
            ['display_name' => 'Manage Website Content', 'module' => 'Website', 'description' => 'Manage public website content and enquiries.', 'created_at' => now(), 'updated_at' => now()]
        );

        $roleId = DB::table('roles')->where('name', 'hotel_manager')->value('id');
        $permissionId = DB::table('permissions')->where('name', 'website.manage')->value('id');
        if ($roleId && $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'website.manage')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Schema::dropIfExists('website_enquiries');
        Schema::dropIfExists('website_entries');
        Schema::dropIfExists('website_settings');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['public_bed_type', 'public_capacity', 'public_tax_included', 'public_breakfast_included']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['public_request_token']);
            $table->dropUnique(['public_confirmation_token']);
            $table->dropColumn(['public_request_token', 'public_confirmation_token', 'number_of_adults', 'number_of_children', 'special_requests']);
        });
    }
};
