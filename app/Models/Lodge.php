<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lodge extends Model
{
    use TracksPerformer;

    protected $fillable = ['name', 'location', 'phone_number', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
