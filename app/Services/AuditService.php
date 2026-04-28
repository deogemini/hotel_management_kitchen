<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function log(string $action, ?Model $model = null, ?array $changes = null): void
    {
        $request = request();
        $user = auth()->user();
        $filtered = [];
        if ($changes) {
            foreach ($changes as $k => $v) {
                if (in_array($k, ['password', 'remember_token'])) {
                    continue;
                }
                $filtered[$k] = $v;
            }
        }

        AuditTrail::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $model ? $model->getMorphClass() : null,
            'auditable_id' => $model?->getKey(),
            'changes' => $filtered ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => optional($request->route())->getName(),
            'method' => $request->getMethod(),
        ]);
    }
}
