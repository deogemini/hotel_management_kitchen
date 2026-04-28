<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public const STATUSES = ['Pending', 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled'];

    protected $fillable = [
        'booking_number',
        'guest_id',
        'room_id',
        'room_type',
        'check_in_date',
        'check_out_date',
        'number_of_nights',
        'number_of_guests',
        'status',
        'room_rate',
        'room_total',
        'deposit_amount',
        'balance_amount',
        'checked_in_at',
        'checked_out_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'room_rate' => 'decimal:2',
            'room_total' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function restaurantOrders(): HasMany
    {
        return $this->hasMany(RestaurantOrder::class);
    }

    public function otherCharges(): HasMany
    {
        return $this->hasMany(OtherCharge::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
