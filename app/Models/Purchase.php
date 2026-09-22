<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'lodge_id', 'menu_item_id', 'quantity', 'unit_cost', 'total_cost',
        'supplier', 'purchased_at', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2', 'purchased_at' => 'date'];
    }

    public function menuItem(): BelongsTo { return $this->belongsTo(MenuItem::class); }
    public function lodge(): BelongsTo { return $this->belongsTo(Lodge::class); }
}
