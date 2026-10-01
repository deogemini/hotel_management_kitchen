<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'lodge_id',
        'guest_id',
        'booking_id',
        'subtotal',
        'paid_amount',
        'balance_amount',
        'status',
        'issued_by',
        'issued_at',
        'company_id', 'bill_to', 'issuer_details', 'due_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'bill_to' => 'array',
            'issuer_details' => 'array',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'issued_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
