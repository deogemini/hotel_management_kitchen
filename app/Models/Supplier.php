<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use TracksPerformer;

    protected $fillable = ['lodge_id', 'name', 'contact_person', 'phone', 'email', 'address', 'notes', 'created_by'];

    public function lodge(): BelongsTo { return $this->belongsTo(Lodge::class); }
}
