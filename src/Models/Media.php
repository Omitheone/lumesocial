<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Media extends Model
{
    protected $fillable = [
        'type',
        'path',
        'suggestion_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = [
        'url',
    ];

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(AiImageSuggestion::class, 'suggestion_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->attributes['path']);
    }

    public function getDownloadUrlAttribute(): string
    {
        return URL::signedRoute('media.download', [
            'media' => $this->id,
            'path' => $this->attributes['path']
        ], now()->addMinutes(5));
    }

    public function getAbsolutePathAttribute(): string
    {
        return storage_path('app/public/' . $this->attributes['path']);
    }

    public function delete(): bool
    {
        try {
            Storage::disk('public')->delete($this->attributes['path']);
        } catch (\Exception $e) {
            report($e);
        }
        
        return parent::delete();
    }

    public function exists(): bool
    {
        return Storage::disk('public')->exists($this->attributes['path']);
    }
}
