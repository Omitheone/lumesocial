<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'organization_id',
        'metric_type',
        'value',
        'date',
        'metadata',
    ];

    protected $casts = [
        'value' => 'integer',
        'date' => 'date',
        'metadata' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
} 