<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand');
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands')
                    ->ignore($brandId)
                    ->whereNull('deleted_at'),
            ],
            'image' => 'sometimes|file|mimes:jpg,jpeg,png,webp',
            'status' => 'sometimes|in:0,1',

            // SEO fields
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|array|min:1',
            'seo_keywords.*' => 'string|max:50',
            'seo_image' => 'sometimes|file|mimes:jpg,jpeg,png,webp',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'name.unique' => 'This brand name already exists.',
            'image.mimes' => 'Brand image must be jpg, jpeg, png, or webp.',
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
