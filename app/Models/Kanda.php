<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;

class Kanda extends Model
{
    use TracksPerformer;

    protected $fillable = [
        'jina_la_kanda',
        'eneo_la_kanda',
    ];

    public function jumuiyas()
    {
        return $this->hasMany(Jumuiya::class);
    }
}
