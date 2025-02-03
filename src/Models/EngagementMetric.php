<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EngagementMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'metric_type',
        'value',
        'date',
    ];

    protected $casts = [
        'value' => 'integer',
        'date' => 'date',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo('LumeSocial\Models\Post', 'post_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('metric_type', $type);
    }
} 