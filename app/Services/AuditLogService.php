<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Log a security-related event.
     */
    public static function log(string $action, array $context = []): void
    {
        $user = Auth::user();
        
        $logData = [
            'action' => $action,
            'user_id' => $user?->id,
            'username' => $user?->username,
            'school_id' => $user?->school_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ];

        Log::channel('security')->info($action, $logData);
    }

    /**
     * Log authentication events.
     */
    public static function authEvent(string $event, ?string $username = null, array $context = []): void
    {
        self::log("auth.{$event}", [
            'username' => $username ?? Auth::user()?->username,
            ...$context,
        ]);
    }

    /**
     * Log data access events.
     */
    public static function dataAccess(string $model, int|string $id, string $action): void
    {
        self::log("data.{$action}", [
            'model' => $model,
            'record_id' => $id,
        ]);
    }

    /**
     * Log sensitive operations.
     */
    public static function sensitiveOperation(string $operation, array $context = []): void
    {
        self::log("sensitive.{$operation}", $context);
    }
}
