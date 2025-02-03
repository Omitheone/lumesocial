<?php

namespace Inovector\Mixpost\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LumeSocial\Http\Controllers\Controller;
use LumeSocial\Models\SubscriptionPlan;
use LumeSocial\Models\Organization;
use LumeSocial\Services\Subscription\SubscriptionManager;
use LumeSocial\Http\Requests\Subscription\SubscribeRequest;
use LumeSocial\Http\Requests\Subscription\UpdateSubscriptionRequest;
use LumeSocial\Http\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    protected $manager;

    public function __construct(SubscriptionManager $manager)
    {
        $this->manager = $manager;
    }

    public function index(Request $request): JsonResponse
    {
        $organization = $request->user()->currentOrganization();
        
        return response()->json([
            'data' => new SubscriptionResource($organization->subscription()),
            'available_plans' => config('subscription.plans'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:' . implode(',', array_keys(config('subscription.plans'))),
            'payment_method' => 'required|string',
        ]);

        $organization = $request->user()->currentOrganization();
        
        $subscription = $this->manager->subscribe(
            $organization,
            $validated['plan'],
            ['payment_method' => $validated['payment_method']]
        );

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $organization = $request->user()->currentOrganization();
        $subscription = $organization->subscription();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found.'], 404);
        }

        $this->manager->cancel($organization, $subscription->name);

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $organization = $request->user()->currentOrganization();
        $subscription = $organization->subscription();

        if (!$subscription) {
            return response()->json(['message' => 'No subscription found.'], 404);
        }

        if (!$subscription->ends_at) {
            return response()->json(['message' => 'Subscription is already active.'], 400);
        }

        $this->manager->resume($organization, $subscription->name);

        return response()->json([
            'message' => 'Subscription resumed successfully.',
            'data' => new SubscriptionResource($subscription->fresh()),
        ]);
    }

    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:' . implode(',', array_keys(config('subscription.plans'))),
        ]);

        $organization = $request->user()->currentOrganization();
        $subscription = $this->manager->upgrade($organization, $validated['plan']);

        return response()->json([
            'message' => 'Subscription upgraded successfully.',
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    public function downgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:' . implode(',', array_keys(config('subscription.plans'))),
        ]);

        $organization = $request->user()->currentOrganization();
        $subscription = $this->manager->downgrade($organization, $validated['plan']);

        return response()->json([
            'message' => 'Subscription downgraded successfully.',
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    public function check(Request $request, string $feature): JsonResponse
    {
        $organization = $request->user()->currentOrganization();
        
        return response()->json([
            'has_access' => $this->manager->hasFeature($organization, $feature)
        ]);
    }
} 