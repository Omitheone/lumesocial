<?php

namespace LumeSocial\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Inovector\Mixpost\Casts\AccountMediaCast;
use Inovector\Mixpost\Casts\EncryptArrayObject;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Events\AccountUnauthorized;
use Inovector\Mixpost\Facades\SocialProviderManager;
use Inovector\Mixpost\SocialProviders\Mastodon\MastodonProvider;
use Inovector\Mixpost\Support\SocialProviderPostConfigs;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'media' => AccountMediaCast::class,
        'data' => 'array',
        'authorized' => 'boolean',
        'access_token' => EncryptArrayObject::class,
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'metadata' => 'array',
        'last_used_at' => 'datetime'
    ];

    protected $hidden = [
        'access_token',
        'credentials'
    ];

    protected $appends = [
        'profile_url',
        'avatar_url',
        'display_name',
        'platform_icon',
        'connection_status'
    ];

    protected ?string $providerClass = null;

    protected static function booted()
    {
        static::updated(function ($account) {
            if ($account->wasChanged('media')) {
                Storage::disk($account->getOriginal('media')['disk'])->delete($account->getOriginal('media')['path']);
            }
        });

        static::deleted(function ($account) {
            if ($account->media) {
                Storage::disk($account->media['disk'])->delete($account->media['path']);
            }
        });
    }

    public function image(): ?string
    {
        if ($this->media) {
            return asset(ltrim($this->media['path'], '/'));
        }

        return null;
    }

    public function values(): array
    {
        return [
            'account_id' => $this->id,
            'provider_id' => $this->provider_id,
            'provider' => $this->provider,
            'name' => $this->name,
            'username' => $this->username,
            'data' => $this->data
        ];
    }

    public function getProviderClass()
    {
        if ($this->providerClass) {
            return $this->providerClass;
        }

        return $this->providerClass = SocialProviderManager::providers()[$this->provider] ?? null;
    }

    public function providerName(): string
    {
        if (!$provider = $this->getProviderClass()) {
            return $this->provider;
        }

        return $provider::name();
    }

    public function postConfigs(): array
    {
        if (!$provider = $this->getProviderClass()) {
            return SocialProviderPostConfigs::make()->jsonSerialize();
        }

        return $provider::postConfigs()->jsonSerialize();
    }

    public function isServiceActive(): bool
    {
        if (!$this->getProviderClass()) {
            return false;
        }

        if ($this->getProviderClass() === MastodonProvider::class) {
            return true;
        }

        return $this->getProviderClass()::service()::isActive();
    }

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function isUnauthorized(): bool
    {
        return !$this->authorized;
    }

    public function setUnauthorized(bool $dispatchEvent = true): void
    {
        $this->authorized = false;
        $this->save();

        if ($dispatchEvent) {
            AccountUnauthorized::dispatch($this);
        }
    }

    public function setAuthorized(): void
    {
        $this->authorized = true;
        $this->save();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo('LumeSocial\Models\Organization', 'organization_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany('LumeSocial\Models\Post', 'account_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany('LumeSocial\Models\AccountAnalytics', 'account_id');
    }

    public function getProfileUrlAttribute(): string
    {
        $platform = Str::lower($this->platform);
        $username = $this->username;

        return match ($platform) {
            'twitter' => "https://twitter.com/{$username}",
            'facebook' => "https://facebook.com/{$username}",
            'instagram' => "https://instagram.com/{$username}",
            'linkedin' => "https://linkedin.com/in/{$username}",
            'youtube' => "https://youtube.com/@{$username}",
            default => ''
        };
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->metadata['avatar_url'] ?? null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->metadata['display_name'] ?? $this->username ?? '';
    }

    public function getPlatformIconAttribute(): string
    {
        return match (Str::lower($this->platform ?? '')) {
            'twitter' => 'twitter',
            'facebook' => 'facebook',
            'instagram' => 'instagram',
            'linkedin' => 'linkedin',
            'youtube' => 'youtube',
            default => 'question'
        };
    }

    public function isConnected(): bool
    {
        return !empty($this->credentials) && !empty($this->credentials['access_token']);
    }

    public function getConnectionStatusAttribute(): string
    {
        return $this->isConnected() ? 'connected' : 'disconnected';
    }
}
