<?php

namespace App\Http\Requests\Product;

use App\Services\UpdateImageHandlerService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSeoRequest extends FormRequest
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
        return [
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|array|min:1',
            'seo_keywords.*' => 'string|max:50',

            'seo_image' => [
                'required',
                function ($attribute, $value, $fail) {
                    $result = $this->updateImageHandlerService->validateFileOrString($value, 'SEO image');
                    if (!$result['valid']) {
                        $fail($result['message']);
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'seo_title.required' => 'SEO title is required.',
            'seo_description.required' => 'SEO description is required.',
            'seo_keywords.required' => 'At least one SEO keyword is required.',
            'seo_keywords.array' => 'SEO keywords must be an array.',

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