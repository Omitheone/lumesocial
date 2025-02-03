<?php

namespace Database\Factories;

use LumeSocial\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'price' => fake()->numberBetween(1000, 10000),
            'features' => ['feature1', 'feature2'],
            'trial_days' => 14,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
} 