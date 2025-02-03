<?php

namespace LumeSocial\Services\Subscription;

use Illuminate\Support\Facades\DB;
use LumeSocial\Models\User;
use LumeSocial\Models\SubscriptionPlan;
use LumeSocial\Events\SubscriptionCreated;
use LumeSocial\Events\SubscriptionCancelled;
use LumeSocial\Models\Organization;
use LumeSocial\Models\SubscriptionPlan as LumeSocialSubscriptionPlan;
use Carbon\Carbon;
use LumeSocial\Models\Subscription;
use LumeSocial\Services\Subscription\SubscriptionGateway;

class SubscriptionManager
{
    public function __construct(
        protected SubscriptionGateway $gateway
    ) {}

    public function subscribe(Organization $organization, string $plan, array $options = []): Subscription
    {
        $subscription = $this->gateway->subscribe($organization, $plan, $options);

        return $organization->subscriptions()->create([
            'name' => $plan,
            'stripe_id' => $subscription->id,
            'stripe_status' => $subscription->status,
            'stripe_price' => $subscription->price->id,
            'quantity' => $options['quantity'] ?? 1,
            'trial_ends_at' => $this->getTrialEndDate($options),
            'ends_at' => null,
            'metadata' => $options['metadata'] ?? [],
        ]);
    }

    public function cancel(Organization $organization, string $plan): bool
    {
        $subscription = $organization->subscription($plan);

        if (!$subscription) {
            return false;
        }

        $this->gateway->cancel($subscription);

        return $subscription->update([
            'ends_at' => now(),
            'metadata' => array_merge($subscription->metadata ?? [], [
                'cancelled_at' => now()->toDateTimeString(),
            ]),
        ]);
    }

    public function resume(Organization $organization, string $plan): bool
    {
        $subscription = $organization->subscription($plan);

        if (!$subscription || !$subscription->ends_at) {
            return false;
        }

        $this->gateway->resume($subscription);

        return $subscription->update([
            'ends_at' => null,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'resumed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }

    public function upgrade(Organization $organization, string $newPlan): Subscription
    {
        $currentSubscription = $organization->subscription();
        
        if ($currentSubscription) {
            $metadata = [
                'upgraded_from' => $currentSubscription->name,
                'upgraded_at' => now()->toDateTimeString(),
            ];
            
            $this->cancel($organization, $currentSubscription->name);
        }

        return $this->subscribe($organization, $newPlan, [
            'metadata' => $metadata ?? [],
        ]);
    }

    public function downgrade(Organization $organization, string $newPlan): Subscription
    {
        $currentSubscription = $organization->subscription();
        
        if ($currentSubscription) {
            $metadata = [
                'downgraded_from' => $currentSubscription->name,
                'downgraded_at' => now()->toDateTimeString(),
            ];
            
            $this->cancel($organization, $currentSubscription->name);
        }

        return $this->subscribe($organization, $newPlan, [
            'metadata' => $metadata ?? [],
        ]);
    }

    protected function getTrialEndDate(array $options): ?Carbon
    {
        if (isset($options['trial_days'])) {
            return now()->addDays($options['trial_days']);
        }

        if (isset($options['trial_ends_at'])) {
            return Carbon::parse($options['trial_ends_at']);
        }

        return null;
    }

    public function isSubscribed(Organization $organization): bool
    {
        if (!$organization->subscription_plan_id) {
            return false;
        }

        if ($organization->subscription_status !== 'active') {
            return false;
        }

        if ($organization->subscription_ends_at && 
            Carbon::parse($organization->subscription_ends_at)->isPast()) {
            return false;
        }

        return true;
    }

    public function hasFeature(Organization $organization, string $feature): bool
    {
        if (!$this->isSubscribed($organization)) {
            return false;
        }

        $plan = $organization->subscriptionPlan;
        
        if (!$plan) {
            return false;
        }

        return in_array($feature, $plan->features ?? []);
    }

    public function getRemainingDays(Organization $organization): ?int
    {
        if (!$this->isSubscribed($organization)) {
            return null;
        }

        $endsAt = $organization->subscription_ends_at;
        if (!$endsAt) {
            return null;
        }

        return Carbon::parse($endsAt)->diffInDays(now());
    }

    public function isExpiringSoon(Organization $organization, int $thresholdDays = 7): bool
    {
        if (!$this->isSubscribed($organization)) {
            return false;
        }

        $remainingDays = $this->getRemainingDays($organization);
        return $remainingDays !== null && $remainingDays <= $thresholdDays;
    }

    public function getCurrentPlan(Organization $organization): ?SubscriptionPlan
    {
        return $organization->subscriptionPlan;
    }

    public function withinLimits(Organization $organization, string $type, int $value): bool
    {
        if (!$organization->subscriptionPlan) {
            return false;
        }

        $limits = $organization->subscriptionPlan->limits ?? [];
        return isset($limits[$type]) && $value <= $limits[$type];
    }
} 