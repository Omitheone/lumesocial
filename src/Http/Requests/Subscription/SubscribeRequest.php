<?php

namespace LumeSocial\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|string',
            'payment_id' => 'required|string',
            'end_date' => 'nullable|date|after:now'
        ];
    }
} 