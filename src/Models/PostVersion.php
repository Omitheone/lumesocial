<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostVersion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $with = ['media'];

    public function post(): BelongsTo
    {
        return $this->belongsTo('LumeSocial\Models\Post', 'post_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo('LumeSocial\Models\Organization', 'organization_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany('LumeSocial\Models\Media', 'mediable');
    }

    public function isLatest(): bool
    {
        return !self::where('post_id', $this->post_id)
            ->where('created_at', '>', $this->created_at)
            ->exists();
    }

    public function getContentAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }

    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getMetadataAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }

    public function setMetadataAttribute($value): void
    {
        $this->attributes['metadata'] = is_array($value) ? json_encode($value) : $value;
    }
}
