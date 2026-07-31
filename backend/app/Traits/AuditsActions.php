<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Http\Request;

trait AuditsActions
{
    protected function audit(Request $request, string $action, ?string $targetModel = null, ?int $targetId = null, ?array $oldValue = null, ?array $newValue = null): void
    {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'target_model' => $targetModel,
            'target_id' => $targetId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $request->ip(),
        ]);
    }
}
