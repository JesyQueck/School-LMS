<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ImportCredential extends Model
{
    protected $fillable = [
        'role',
        'name',
        'email',
        'login_identifier',
        'password',
        'temporary_password',
        'related_to',
        'user_id',
        'created_by',
        'viewed_at',
        'expires_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'temporary_password' => 'encrypted',
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

    public function loginIdentifier(): ?string
    {
        return $this->login_identifier ?? $this->user?->loginIdentifier();
    }

    public static function storeTemporaryPassword(User $user, string $password, int $createdBy): self
    {
        if (! in_array($user->role, ['student', 'teacher'], true)) {
            throw new InvalidArgumentException('Temporary password retrieval is only supported for student and teacher accounts.');
        }

        $records = static::where('user_id', $user->id);
        $credential = (clone $records)->latest('id')->first() ?? new static;

        (clone $records)->update([
            'password' => null,
            'temporary_password' => null,
        ]);

        $credential->fill([
            'role' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
            'login_identifier' => $user->loginIdentifier(),
            'password' => null,
            'related_to' => null,
            'user_id' => $user->id,
            'created_by' => $createdBy,
            'expires_at' => null,
            'temporary_password' => $password,
        ]);
        $credential->save();

        return $credential;
    }
}
