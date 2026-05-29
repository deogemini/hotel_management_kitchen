<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'lodge_id',
        'payable_type',
        'payable_id',
        'guest_id',
        'booking_id',
        'restaurant_order_id',
        'invoice_id',
        'amount',
        'payment_method',
        'status',
        'reference_number',
        'notes',
        'received_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function restaurantOrder(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customerName(): string
    {
        if ($this->restaurantOrder) {
            return $this->restaurantOrder->guest?->full_name
                ?? $this->restaurantOrder->walk_in_customer_name
                ?? 'Walk-in customer';
        }

        return $this->guest?->full_name ?? '-';
    }

    public function purposeLabel(): string
    {
        if ($this->payable instanceof OtherCharge) {
            return $this->payable->service_type.' - '.$this->payable->description;
        }

        if ($this->restaurantOrder) {
            $details = $this->restaurantOrder->customer_type;

            if ($this->restaurantOrder->room?->room_number) {
                $details .= ' - Room '.$this->restaurantOrder->room->room_number;
            }

            return 'Food - '.$details;
        }

        if ($this->booking) {
            return 'Room booking';
        }

        if ($this->invoice) {
            return 'Invoice';
        }

        return '-';
    }
}
