<?php

namespace Database\Factories;

use LumeSocial\Models\AiSettings;
use LumeSocial\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiSettingsFactory extends Factory
{
    protected $model = AiSettings::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'api_key' => fake()->uuid(),
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'debug_mode' => false,
        ];
    }
} 