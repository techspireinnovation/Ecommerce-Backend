<?php

namespace App\Http\Requests\Category;

use App\Services\UpdateImageHandlerService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    private UpdateImageHandlerService $updateImageHandlerService;

    public function __construct(UpdateImageHandlerService $updateImageHandlerService)
    {
        $this->updateImageHandlerService = $updateImageHandlerService;
    }
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('category');

        return [
            // Category
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at')
            ],

            'image' => [
                'required',
                function ($attribute, $value, $fail) {
                    $result = $this->updateImageHandlerService->validateFileOrString($value, 'Category image');
                    if (!$result['valid']) {
                        $fail($result['message']);
                    }
                }
            ],

            'seo_image' => [
                'required',
                function ($attribute, $value, $fail) {
                    $result = $this->updateImageHandlerService->validateFileOrString($value, 'SEO image');
                    if (!$result['valid']) {
                        $fail($result['message']);
                    }
                }
            ],

            'status' => 'sometimes|in:0,1',

            // SEO
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|array|min:1',
            'seo_keywords.*' => 'string|max:50',


        ];
    }

    public function messages()
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
            'error' => $errors,
        ], 422));
    }
}
