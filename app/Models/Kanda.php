<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kanda extends Model
{
    protected $fillable = [
        'jina_la_kanda',
        'eneo_la_kanda',
    ];

    public function jumuiyas()
    {
        return $this->hasMany(Jumuiya::class);
    }
}
