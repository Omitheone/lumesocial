<?php

namespace LumeSocial\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_price' => $this->stripe_price,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at ? $this->trial_ends_at->toIso8601String() : null,
            'ends_at' => $this->ends_at ? $this->ends_at->toIso8601String() : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'metadata' => $this->metadata ?? [],
            'status' => $this->getStatus(),
        ];
    }

    /**
     * Get the subscription status.
     *
     * @return string
     */
    protected function getStatus(): string
    {
        if (!$this->resource) {
            return 'none';
        }

        if ($this->ends_at) {
            return 'cancelled';
        }

        if ($this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return 'trialing';
        }

        if ($this->stripe_status === 'active') {
            return 'active';
        }

        if ($this->stripe_status === 'past_due') {
            return 'past_due';
        }

        if ($this->stripe_status === 'incomplete') {
            return 'incomplete';
        }

        return $this->stripe_status;
    }
} 