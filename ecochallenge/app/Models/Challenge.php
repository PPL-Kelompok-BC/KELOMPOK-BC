<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
    protected $fillable = [
        'category_id', 'created_by', 'title', 'description', 'difficulty',
        'points', 'is_active', 'proof_criteria', 'estimated_co2e_gram', 'emission_source',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'estimated_co2e_gram' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Hanya challenge aktif yang tampil bagi mahasiswa (PBI-12, PBI-11). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
