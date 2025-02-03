<?php

namespace LumeSocial\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|exists:subscription_plans,id',
            'action' => ['required', Rule::in(['upgrade', 'downgrade'])]
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'A subscription plan is required',
            'plan_id.exists' => 'The selected subscription plan is invalid',
            'action.required' => 'An action (upgrade/downgrade) is required',
            'action.in' => 'The action must be either upgrade or downgrade'
        ];
    }
} 