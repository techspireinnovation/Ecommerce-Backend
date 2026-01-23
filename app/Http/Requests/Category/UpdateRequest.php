<?php
namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        $categoryId = $this->route('category');
        return [
            //category
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at')
            ],
            'image' => 'required|file|mimes:jpg,jpeg,png,svg,webp',
            'status' => 'sometimes|in:0,1',
            //seo details
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|array|min:1',
            'seo_keywords.*' => 'string|max:50',
            'seo_image' => 'required|file|mimes:jpg,jpeg,png,svg,webp',

        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'image.required' => 'Category image is required.',
            'image.mimes' => 'Category image must be jpg, jpeg, png, svg or webp.',
            'seo_title.required' => 'SEO title is required.',
            'seo_description.required' => 'SEO description is required.',
            'seo_keywords.required' => 'At least one SEO keyword is required.',
            'seo_keywords.array' => 'SEO keywords must be an array.',
            'seo_keywords.*.string' => 'Each SEO keyword must be a string.',
            'seo_image.required' => 'SEO image is required.',
            'seo_image.mimes' => 'SEO image must be jpg, jpeg, png, svg or webp.',
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