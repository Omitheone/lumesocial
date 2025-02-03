<?php

namespace LumeSocial\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use LumeSocial\Models\Media;

class MediaUrlGenerator
{
    protected string $disk = 'public';

    public function __construct(protected ?string $customDisk = null)
    {
        if ($customDisk) {
            $this->disk = $customDisk;
        }
    }

    public function getUrl(string $path): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . $path);
        }

        // For non-public disks, generate a signed URL
        return $this->getSignedUrl($path);
    }

    public function getMediaUrl(Media $media): string
    {
        return $this->getUrl($media->path);
    }

    public function getSignedUrl(string $path, int $expiration = 5): string
    {
        return URL::signedRoute('media.download', [
            'path' => $path,
            'disk' => $this->disk,
            'expires' => now()->addMinutes($expiration)->timestamp,
        ]);
    }

    public function getMediaSignedUrl(Media $media, int $expiration = 5): string
    {
        return $this->getSignedUrl($media->path, $expiration);
    }

    public function getAbsolutePath(string $path): string
    {
        if ($this->disk === 'public') {
            return storage_path('app/public/' . $path);
        }

        return storage_path('app/' . $this->disk . '/' . $path);
    }

    public function getMediaAbsolutePath(Media $media): string
    {
        return $this->getAbsolutePath($media->path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function getDisk(): string
    {
        return $this->disk;
    }
} 