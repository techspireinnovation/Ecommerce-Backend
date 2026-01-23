<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be 0 (active) or 1(moved to cart).',
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
