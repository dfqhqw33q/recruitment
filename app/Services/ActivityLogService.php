<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(
        string $action,
        string $module,
        string $description,
        ?string $entityType = null,
        $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function login(?string $action = 'login'): void
    {
        $this->log($action, 'Authentication', auth()->user()?->name . ' logged in.');
    }

    public function logout(): void
    {
        if (auth()->check()) {
            $this->log('logout', 'Authentication', auth()->user()->name . ' logged out.');
        }
    }
}
