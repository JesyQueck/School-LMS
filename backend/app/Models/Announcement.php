<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $guarded = [];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope announcements to those visible to a given role.
     * An announcement is visible if it targets "all" or the specific role.
     */
    public function scopeForRole($query, string $role)
    {
        return $query->whereIn('target_role', ['all', $role]);
    }
}
