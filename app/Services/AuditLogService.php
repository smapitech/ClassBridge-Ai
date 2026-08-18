<?php namespace App\Services;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService {
    public static function log(string $action, string $module, ?string $description = null, ?array $metadata = null): void
    {
        AuditLog::create([
            'school_id' => Auth::check() ? Auth::user()->school_id : null,
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
        ]);
    }
}