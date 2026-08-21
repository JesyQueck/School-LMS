<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportCredential extends Model
{
    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'related_to',
        'user_id',
        'created_by',
        'viewed_at',
        'expires_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isViewed(): bool
    {
        return $this->viewed_at !== null;
    }
}
