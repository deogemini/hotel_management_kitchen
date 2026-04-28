<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shukrani extends Model
{
    protected $fillable = [
        'watoto_id',
        'kiasi',
        'mode_ya_malipo',
        'hali_ya_malipo',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'kiasi' => 'decimal:2',
    ];

    public function mtoto()
    {
        return $this->belongsTo(Watoto::class, 'watoto_id');
    }
}
