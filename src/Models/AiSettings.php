<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'image_count',
        'temperature',
        'max_tokens',
    ];

    protected $casts = [
        'image_count' => 'integer',
        'temperature' => 'float',
        'max_tokens' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set default values
        $this->attributes['model'] = $attributes['model'] ?? 'gpt-3.5-turbo';
        $this->attributes['image_count'] = $attributes['image_count'] ?? 3;
        $this->attributes['temperature'] = $attributes['temperature'] ?? 0.7;
        $this->attributes['max_tokens'] = $attributes['max_tokens'] ?? 500;
    }

    /**
     * Get the organization that owns these settings.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
} 