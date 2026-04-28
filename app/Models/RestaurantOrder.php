<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantOrder extends Model
{
    public const STATUSES = ['Pending', 'Preparing', 'Ready', 'Served', 'Cancelled'];

    protected $fillable = [
        'order_number',
        'customer_type',
        'guest_id',
        'booking_id',
        'room_id',
        'walk_in_customer_name',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'paid_amount',
        'balance_amount',
        'created_by',
        'chef_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }
}
