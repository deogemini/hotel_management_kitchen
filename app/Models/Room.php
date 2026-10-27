<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use TracksPerformer;

    public const TYPES = ['Single', 'Executive', 'Deluxe', 'Suite'];
    public const STATUSES = ['Available', 'Occupied', 'Reserved', 'Maintenance'];
    public const FEATURES = ['AC', 'TV', 'WiFi', 'Hot shower', 'Fridge', 'Balcony', 'Feni', 'Kabati', 'Kiti', 'Kiti na Meza'];

    protected $fillable = [
        'room_number',
        'lodge_id',
        'room_type',
        'price_per_night',
        'status',
        'features',
        'description',
        'public_bed_type',
        'public_capacity',
        'public_tax_included',
        'public_breakfast_included',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_per_night' => 'decimal:2',
            'public_capacity' => 'integer',
            'public_tax_included' => 'boolean',
            'public_breakfast_included' => 'boolean',
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

    public function lodge(): BelongsTo
    {
        return $this->belongsTo(Lodge::class);
    }
}
