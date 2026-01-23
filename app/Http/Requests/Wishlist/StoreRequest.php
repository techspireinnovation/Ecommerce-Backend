<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at')
                    ->whereNotIn('status', [1, 4]),
            ],

            'product_variant_storage_id' => [
                'required',
                'integer',
                Rule::exists('product_variant_storages', 'id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.integer' => 'Product ID must be an integer.',
            'product_id.exists' => 'Selected product is inactive or unavailable.',

            'product_variant_storage_id.required' => 'Product variant is required.',
            'product_variant_storage_id.integer' => 'Variant ID must be an integer.',
            'product_variant_storage_id.exists' => 'Selected variant does not exist.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $firstError = reset($errors)[0] ?? 'Validation error';

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors,
            ], 422)
        );
    }
}
