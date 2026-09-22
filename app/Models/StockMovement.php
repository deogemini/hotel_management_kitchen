<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['lodge_id', 'menu_item_id', 'type', 'quantity', 'stock_before', 'stock_after', 'unit_price', 'reference_type', 'reference_id', 'movement_date', 'created_by', 'notes'];
    protected function casts(): array { return ['unit_price' => 'decimal:2', 'movement_date' => 'date']; }
    public function menuItem(): BelongsTo { return $this->belongsTo(MenuItem::class); }
}
