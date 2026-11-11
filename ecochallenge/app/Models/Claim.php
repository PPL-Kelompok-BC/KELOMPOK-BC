<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'challenge_id',
        'proof_path',
        'photo_hash',
        'photo_taken_at',
        'needs_review',
        'claim_date',
        'status',
        'points_awarded',
        'co2e_awarded_gram',
        'reviewed_by',
        'reviewed_at',
        'cancel_reason',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'photo_taken_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'needs_review' => 'boolean',
            'claim_date' => 'date',
            'points_awarded' => 'integer',
            'co2e_awarded_gram' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
