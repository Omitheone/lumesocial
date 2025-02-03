<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LumeSocial\Models\SubscriptionPlan;
use LumeSocial\Models\Organization;
use LumeSocial\Models\AiSettings;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create subscription plans
        $plans = SubscriptionPlan::factory()->count(3)->create([
            'name' => fn($attrs, $plan) => match($plan->id) {
                1 => 'Basic',
                2 => 'Pro',
                3 => 'Enterprise',
                default => 'Custom'
            },
        ]);

        // Create organizations with AI settings
        Organization::factory()
            ->count(5)
            ->create()
            ->each(function ($org) {
                AiSettings::factory()->create([
                    'organization_id' => $org->id
                ]);
            });
    }
} 