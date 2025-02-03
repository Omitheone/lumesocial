<?php

namespace LumeSocial\Services\Subscription;

use LumeSocial\Models\Organization;
use LumeSocial\Models\Subscription;
use Stripe\Customer;
use Stripe\StripeClient;
use Exception;

class StripeGateway implements SubscriptionGateway
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function subscribe(Organization $organization, string $plan, array $options = []): object
    {
        $customer = $this->getOrCreateCustomer($organization);

        if (isset($options['payment_method'])) {
            $this->updateDefaultPaymentMethod($customer->id, $options['payment_method']);
        }

        return $this->stripe->subscriptions->create([
            'customer' => $customer->id,
            'items' => [['price' => $this->getPriceId($plan)]],
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => $options['metadata'] ?? [],
        ]);
    }

    public function cancel(Subscription $subscription): bool
    {
        try {
            $this->stripe->subscriptions->cancel($subscription->stripe_id);
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function resume(Subscription $subscription): bool
    {
        try {
            $this->stripe->subscriptions->resume($subscription->stripe_id);
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    protected function getOrCreateCustomer(Organization $organization): Customer
    {
        if ($organization->stripe_id) {
            return $this->stripe->customers->retrieve($organization->stripe_id);
        }

        $customer = $this->stripe->customers->create([
            'email' => $organization->billing_email,
            'name' => $organization->name,
            'metadata' => [
                'organization_id' => $organization->id,
            ],
        ]);

        $organization->update(['stripe_id' => $customer->id]);

        return $customer;
    }

    protected function updateDefaultPaymentMethod(string $customerId, string $paymentMethodId): void
    {
        $this->stripe->paymentMethods->attach($paymentMethodId, [
            'customer' => $customerId,
        ]);

        $this->stripe->customers->update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
    }

    protected function getPriceId(string $plan): string
    {
        return config("subscription.plans.{$plan}.price_id");
    }
} 