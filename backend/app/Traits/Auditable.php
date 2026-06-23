<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            static::audit('created', [], $model->getAttributes(), $model);
        });

        static::updated(function ($model): void {
            static::audit('updated', $model->getOriginal(), $model->getChanges(), $model);
        });

        static::deleted(function ($model): void {
            static::audit('deleted', $model->getAttributes(), [], $model);
        });
    }

    private static function audit(string $event, array $old, array $new, $model): void
    {
        Audit::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}
