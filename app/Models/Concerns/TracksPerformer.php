<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait TracksPerformer
{
    protected static function bootTracksPerformer(): void
    {
        static::saving(function (Model $model): void {
            if (auth()->id()) {
                $model->setAttribute('performed_by', auth()->id());
            }
        });
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
