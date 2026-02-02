<?php

namespace App\Http\Requests\ShippingMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ShippingMethod;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'weight_to' => $this->has('weight_to') ? round(max(0, (float) $this->weight_to), 2) : null,
            'charge' => $this->has('charge') ? round(max(0, (float) $this->charge), 2) : null,
            'free_shipping_threshold' => $this->has('free_shipping_threshold')
                ? round(max(0, (float) $this->free_shipping_threshold), 2)
                : null,
        ]);
    }
    public function rules()
    {
        $methodId = $this->route('shipping_method');

        return [
            'delivery_type' => 'required|in:1,2',

            'weight_to' => [
                'required',
                'numeric',
                'min:0',
                function ($attr, $value, $fail) use ($methodId) {
                    $last = ShippingMethod::query()->where('delivery_type', $this->delivery_type)
                        ->where('id', '<>', $methodId)
                        ->whereNull('deleted_at')
                        ->orderByDesc('weight_to')
                        ->first();

                    if ($last && $value <= $last->weight_to) {
                        $fail("weight_to must be greater than previous weight ({$last->weight_to} kg).");
                    }
                }
            ],

            'charge' => [
                'required',
                'numeric',
                'min:0',
                function ($attr, $value, $fail) use ($methodId) {
                    $last = ShippingMethod::query()->where('delivery_type', $this->delivery_type)
                        ->where('id', '<>', $methodId)
                        ->whereNull('deleted_at')
                        ->orderByDesc('weight_to')
                        ->first();

                    if ($last && $value <= $last->charge) {
                        $fail("Charge must be greater than the previous charge ({$last->charge} Rs).");
                    }
                }
            ],

            'free_shipping_threshold' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attr, $value, $fail) use ($methodId) {
                    $last = ShippingMethod::query()->where('delivery_type', $this->delivery_type)
                        ->where('id', '<>', $methodId)
                        ->whereNull('deleted_at')
                        ->orderByDesc('weight_to')
                        ->first();

                    if ($last && $value !== null && $last->free_shipping_threshold !== null && $value <= $last->free_shipping_threshold) {
                        $fail("Free shipping threshold must be greater than the previous value ({$last->free_shipping_threshold} Rs).");
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'weight_to.required' => 'Maximum weight is required.',
            'weight_to.numeric' => 'Maximum weight must be a valid number.',
            'weight_to.min' => 'Maximum weight cannot be negative.',

            'charge.required' => 'Shipping charge is required.',
            'charge.numeric' => 'Shipping charge must be a valid number.',
            'charge.min' => 'Shipping charge cannot be negative.',

            'free_shipping_threshold.numeric' => 'Free shipping threshold must be a valid number.',
            'free_shipping_threshold.min' => 'Free shipping threshold cannot be negative.',

            'status.in' => 'Status must be 0 (Inactive) or 1 (Active).',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstErrorMessage = reset($errors)[0] ?? 'Validation error';

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstErrorMessage,
            'error' => $errors,
        ], 422));
    }
}