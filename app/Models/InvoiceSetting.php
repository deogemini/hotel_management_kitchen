<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $fillable = ['details'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public static function details(): array
    {
        return array_replace([
            'name' => 'HARD ROCK LODGE', 'tin' => '121-013-479',
            'phone' => '', 'email' => '', 'address' => '', 'currency' => 'TZS',
            'payment_name' => 'HARD ROCK LODGE', 'merchant_number' => '61253910',
            'payment_provider' => 'SELCOME', 'bank_name' => '', 'account_name' => '',
            'account_number' => '', 'swift_code' => '',
            'terms' => 'Payment is due within 7 days of receiving this invoice. We sincerely appreciate your business.',
        ], static::find(1)?->details ?? []);
    }
}
