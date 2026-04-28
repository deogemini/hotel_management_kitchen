<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watoto extends Model
{
    protected $fillable = [
        'jumuiya_id',
        'jina_la_mtoto',
        'tarehe_ya_kuzaliwa',
        'namba_ya_mzazi',
    ];

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function shukranis()
    {
        return $this->hasMany(Shukrani::class, 'watoto_id');
    }
}
