<?php

namespace LumeSocial\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LumeSocial\Models\PersonalAccessToken;
use Illuminate\Support\Str;

trait HasApiTokens
{
    /**
     * Get the access tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param array $abilities
     * @return \LumeSocial\Models\PersonalAccessToken
     */
    public function createToken(string $name, array $abilities = ['*']): PersonalAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
        ]);

        $token->plain_text_token = $plainTextToken;

        return $token;
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return \LumeSocial\Models\PersonalAccessToken|null
     */
    public function currentAccessToken(): ?PersonalAccessToken
    {
        $token = request()->bearerToken();
        
        if (!$token) {
            return null;
        }

        return $this->tokens()->where('token', hash('sha256', $token))->first();
    }

    /**
     * Determine if the current API token has a given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function tokenCan(string $ability): bool
    {
        return $this->currentAccessToken()?->can($ability) ?? false;
    }
} 