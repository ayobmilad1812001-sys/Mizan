<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Record a governance action: who changed what, from which values to which,
     * and from where. Only deliberate, sensitive changes are logged here —
     * ordinary transactions already carry their own permanent trail.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        string $action,
        Model $auditable,
        ?User $user = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null,
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => ($user ?? auth()->user())?->id,
            'action' => $action,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
            // A shared shop terminal is identified by its browser, which is the only
            // handle we have on "which till was this done from".
            'terminal_name' => $request ? substr((string) $request->userAgent(), 0, 50) : null,
        ]);
    }
}
