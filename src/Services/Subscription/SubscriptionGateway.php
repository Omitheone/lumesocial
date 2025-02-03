<?php

namespace LumeSocial\Services\Subscription;

use LumeSocial\Models\Organization;
use LumeSocial\Models\Subscription;

interface SubscriptionGateway
{
    /**
     * Subscribe an organization to a plan.
     *
     * @param Organization $organization
     * @param string $plan
     * @param array $options
     * @return object
     */
    public function subscribe(Organization $organization, string $plan, array $options = []): object;

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription
     * @return bool
     */
    public function cancel(Subscription $subscription): bool;

    /**
     * Resume a cancelled subscription.
     *
     * @param Subscription $subscription
     * @return bool
     */
    public function resume(Subscription $subscription): bool;
} 