<?php

namespace App\Http\Requests\Deal;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|file|mimes:jpg,jpeg,png,svg,webp',
            'type' => 'required|in:1,2,3',
            'amount' => 'nullable|integer|required_if:type,3',
            'start_date' => 'nullable|date|required_if:type,3|after_or_equal:today',
            'end_date' => 'nullable|date|required_if:type,3|after_or_equal:start_date',
            'status' => 'sometimes|in:0,1',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at')
                    ->whereNot('status', 1),
            ],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Deal title is required.',
            'title.string' => 'Deal title must be a string.',
            'title.max' => 'Deal title must not exceed 255 characters.',

            'description.required' => 'Deal description is required.',
            'description.string' => 'Deal description must be a string.',

            'image.required' => 'Deal image is required.',
            'image.mimes' => 'Deal image must be a file of type: jpg, jpeg, png, svg, webp.',

            'type.required' => 'Deal type is required.',
            'type.in' => 'Deal type must be 1 (percentage), 2 (fixed price), or 3 (flash sale).',

            'amount.required_if' => 'Amount is required for flash sale deals.',
            'amount.integer' => 'Amount must be an integer.',

            'start_date.required_if' => 'Start date is required for flash sale deals.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',

            'end_date.required_if' => 'End date is required for flash sale deals.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be the same or after the start date.',

            'status.in' => 'Status must be either 0 (active) or 1 (inactive).',

            'product_ids.required' => 'At least one product must be selected for this deal.',
            'product_ids.array' => 'Products must be provided in an array.',
            'product_ids.min' => 'At least one product must be selected.',
            'product_ids.*.integer' => 'Each product ID must be a valid integer.',
            'product_ids.*.exists' => 'One or more selected products are inactive or deleted.',

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
