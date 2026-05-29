<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'stock_quantity',
        'low_stock_quantity',
        'is_available',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_quantity' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }
}
