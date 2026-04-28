<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginIpAttempt extends Model
{
    protected $fillable = [
        'ip_address',
        'failed_attempts',
        'last_failed_at',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'last_failed_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }
}
