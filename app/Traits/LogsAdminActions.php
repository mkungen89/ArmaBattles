<?php

namespace App\Traits;

use App\Models\AdminAuditLog;

trait LogsAdminActions
{
    protected function logAction(string $action, ?string $targetType = null, ?int $targetId = null, ?array $metadata = null): void
    {
        AdminAuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
