<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image',
        'created_by',
        'target_role',
        'show_on_website',
    ];

    protected $casts = [
        'show_on_website' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope announcements to those visible to a given role.
     * An announcement is visible if it targets "all" or the specific role.
     */
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->whereIn('target_role', ['all', $role]);
    }

    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->where('show_on_website', true);
    }
}
