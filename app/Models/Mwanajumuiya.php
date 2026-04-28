<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mwanajumuiya extends Model
{
    protected $fillable = [
        'jumuiya_id',
        'jina_la_mwanajumuiya',
        'kadi_namba',
        'namba_ya_simu',
    ];

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function zakas()
    {
        return $this->hasMany(Zaka::class);
    }
}
