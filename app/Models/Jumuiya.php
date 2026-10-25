<?php

namespace App\Models;

use App\Models\Concerns\TracksPerformer;
use Illuminate\Database\Eloquent\Model;

class Jumuiya extends Model
{
    use TracksPerformer;

    protected $fillable = [
        'kanda_id',
        'jina_la_jumuiya',
    ];

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function wanajumuiya()
    {
        return $this->hasMany(Mwanajumuiya::class);
    }

    public function watoto()
    {
        return $this->hasMany(Watoto::class);
    }
}
