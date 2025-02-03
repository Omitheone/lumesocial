<?php

namespace Database\Factories;

use LumeSocial\Models\Organization;
use LumeSocial\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->slug(),
            'current_subscription_id' => null,
            'subscription_plan_id' => SubscriptionPlan::factory(),
        ];
    }
} 