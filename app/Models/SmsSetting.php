<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use TracksPerformer;

    protected $fillable = [
        'base_url',
        'client_id',
        'client_secret',
        'sender_id',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
