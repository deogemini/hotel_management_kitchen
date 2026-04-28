<?php

namespace App\Models;

use App\Jobs\SendZakaSmsJob;
use Illuminate\Database\Eloquent\Model;

class Zaka extends Model
{
    protected static function booted()
    {
        static::created(function (Zaka $zaka) {
            SendZakaSmsJob::dispatch($zaka)->afterResponse();
        });
    }

    protected $fillable = [
        'mwanajumuiya_id',
        'kiasi',
        'risiti_namba',
        'mode_ya_malipo',
        'hali_ya_malipo',
        'paid_at',
        'sms_sent',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'kiasi' => 'decimal:2',
        'sms_sent' => 'boolean',
    ];

    public function mwanajumuiya()
    {
        return $this->belongsTo(Mwanajumuiya::class);
    }
}
