<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Category
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->whereNull('deleted_at')
            ],

            // Allow either a file upload or a URL for category image
            'image' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->hasFile('image') && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('Category image must be a valid file or a URL.');
                    }
                },
                'nullable', // must be nullable here because custom validation handles it
            ],

            'status' => 'sometimes|in:0,1',

            // SEO details
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|array|min:1',
            'seo_keywords.*' => 'string|max:50',

            // Allow either a file upload or a URL for SEO image
            'seo_image' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->hasFile('seo_image') && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('SEO image must be a valid file or a URL.');
                    }
                },
                'nullable',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',

            'status.in' => 'Status must be 0 (active) or 1 (inactive).',

            'seo_title.required' => 'SEO title is required.',
            'seo_description.required' => 'SEO description is required.',
            'seo_keywords.required' => 'At least one SEO keyword is required.',
            'seo_keywords.array' => 'SEO keywords must be an array.',
            'seo_keywords.*.string' => 'Each SEO keyword must be a string.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstErrorMessage = reset($errors)[0] ?? 'Validation error';

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstErrorMessage,
            'errors' => $errors,
        ], 422));
    }
}
