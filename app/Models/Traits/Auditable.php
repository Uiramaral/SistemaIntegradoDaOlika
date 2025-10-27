<?php

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function($model){
            self::writeAudit('created', $model, null, $model->getAttributes());
        });

        static::updated(function($model){
            self::writeAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function($model){
            self::writeAudit('deleted', $model, $model->getOriginal(), null);
        });
    }

    protected static function writeAudit(string $action, $model, $old=null, $new=null)
    {
        try {
            $req = request();

            AuditLog::create([
                'user_id'    => optional($req->user())->id,
                'action'     => $action,
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'changes'    => compact('old','new'),
                'ip'         => $req->ip(),
                'ua'         => substr((string)$req->userAgent(),0,255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('audit-fail', ['e'=>$e->getMessage()]);
        }
    }
}
