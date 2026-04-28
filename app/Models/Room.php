<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    public const TYPES = ['Single', 'Double', 'Twin', 'Deluxe', 'Suite'];
    public const STATUSES = ['Available', 'Occupied', 'Reserved', 'Maintenance'];
    public const FEATURES = ['AC', 'TV', 'WiFi', 'Hot shower', 'Fridge', 'Balcony'];

    protected $fillable = [
        'room_number',
        'room_type',
        'price_per_night',
        'status',
        'features',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_per_night' => 'decimal:2',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
