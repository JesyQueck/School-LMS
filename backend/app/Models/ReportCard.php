<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_RETURNED,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
    ];

    public const ALLOWED_TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_APPROVED, self::STATUS_RETURNED],
        self::STATUS_RETURNED => [self::STATUS_DRAFT, self::STATUS_APPROVED],
        self::STATUS_APPROVED => [self::STATUS_PUBLISHED, self::STATUS_RETURNED],
        self::STATUS_PUBLISHED => [],
    ];

    protected $guarded = [];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'is_published' => false,
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'generated_at' => 'datetime',
        'next_term_begins' => 'date',
        'published_at' => 'datetime',
        'status' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPublished(): bool
    {
        return $this->is_published || $this->status === self::STATUS_PUBLISHED;
    }

    public function canEdit(): bool
    {
        return ! $this->isPublished();
    }

    public function canPublish(): bool
    {
        return $this->status === self::STATUS_APPROVED && ! $this->is_published;
    }
}
