<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use TracksPerformer;

    protected $fillable = ['lodge_id', 'name', 'contact_person', 'phone', 'email', 'address', 'notes', 'created_by'];

    public function lodge(): BelongsTo { return $this->belongsTo(Lodge::class); }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier', 'name')
            ->where(function ($query) {
                $query->whereColumn('purchases.lodge_id', 'suppliers.lodge_id')
                    ->orWhereNull('suppliers.lodge_id');
            });
    }
}
