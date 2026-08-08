<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('recipient_role', $role);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
