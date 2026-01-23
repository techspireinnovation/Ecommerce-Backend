<?php
namespace App\Http\Requests\ShippingMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'delivery_type' => 'required|in:1,2',
            'charge_amount' => 'required|numeric|min:0',
            'free_delivery_min_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'delivery_type.required' => 'Delivery type is required.',
            'delivery_type.in' => 'Delivery type must be 1 (Inside Valley) or 2 (Outside Valley).',
            'charge_amount.required' => 'Charge amount is required.',
            'charge_amount.numeric' => 'Charge amount must be a number.',
            'charge_amount.min' => 'Charge amount cannot be negative.',
            'free_delivery_min_amount.numeric' => 'Free delivery minimum amount must be a number.',
            'free_delivery_min_amount.min' => 'Free delivery minimum amount cannot be negative.',
            'status.in' => 'Status must be 0 (Active) or 1 (Inactive).',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstErrorMessage = reset($errors)[0] ?? 'Validation error';
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstErrorMessage,
            'error' => $errors
        ], 422));
    }
}
