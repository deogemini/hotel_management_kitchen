<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSetting extends Model
{
    protected $hidden = ['smtp_password'];

    protected $fillable = [
        'lodge_id', 'hero_title', 'hero_subtitle', 'hero_image_path',
        'introduction_title', 'introduction', 'contact_email', 'phone_number',
        'address', 'map_url', 'whatsapp_number', 'check_in_time', 'check_out_time',
        'currency_code', 'guest_policies', 'privacy_policy', 'booking_policy',
        'cancellation_policy', 'payment_policy', 'privacy_policy_approved',
        'booking_policy_approved', 'cancellation_policy_approved', 'payment_policy_approved',
        'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username',
        'smtp_password', 'mail_from_address', 'mail_from_name',
    ];

    protected function casts(): array
    {
        return [
            'privacy_policy_approved' => 'boolean',
            'booking_policy_approved' => 'boolean',
            'cancellation_policy_approved' => 'boolean',
            'payment_policy_approved' => 'boolean',
            'smtp_enabled' => 'boolean',
            'smtp_port' => 'integer',
            'smtp_password' => 'encrypted',
        ];
    }

    public function lodge(): BelongsTo
    {
        return $this->belongsTo(Lodge::class);
    }
}
