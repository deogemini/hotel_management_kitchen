<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['lodge_id', 'name', 'tin', 'phone', 'email', 'address'];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when(! $user->hasRole('hotel_manager'), fn (Builder $query) => $query->where('lodge_id', $user->lodge_id));
    }
}
