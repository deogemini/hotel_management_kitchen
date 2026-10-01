<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
            'logo_path' => null,
            'name' => 'HARD ROCK LODGE', 'tin' => '121-013-479',
            'phone' => '', 'email' => '', 'address' => '', 'currency' => 'TZS',
            'payment_name' => 'HARD ROCK LODGE', 'merchant_number' => '61253910',
            'payment_provider' => 'SELCOME', 'bank_name' => '', 'account_name' => '',
            'account_number' => '', 'swift_code' => '',
            'terms' => 'Payment is due within 7 days of receiving this invoice. We sincerely appreciate your business.',
        ], static::find(1)?->details ?? []);
    }

    public static function logoUrl(?string $path): ?string
    {
        if (! $path || ! preg_match('/\Ainvoice-logos\/[a-zA-Z0-9]+\.(png|jpg|jpeg|webp)\z/', $path, $matches)) {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            return null;
        }

        $type = in_array($matches[1], ['jpg', 'jpeg']) ? 'jpeg' : $matches[1];

        return 'data:image/'.$type.';base64,'.base64_encode($disk->get($path));
    }
}
