<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
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
    ];

    protected $casts = [
        'photo_taken_at' => 'datetime',
        'needs_review' => 'boolean',
        'claim_date' => 'date',
        'points_awarded' => 'integer',
        'co2e_awarded_gram' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}