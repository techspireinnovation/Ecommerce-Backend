<?php

namespace App\Http\Requests\Product;

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

            // Product
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->whereNull('deleted_at')
            ],
            'brand_id' => [
                'required',
                'integer',
                Rule::exists('brands', 'id')->whereNull('deleted_at')
            ],
            'subcategory_id' => [
                'required',
                'integer',
                Rule::exists('sub_categories', 'id')->whereNull('deleted_at')
            ],
            'summary' => 'required|string',
            'overview' => 'required|string',
            'price' => 'required|integer|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'status' => 'sometimes|in:0,1,3,4',

            // Tags / Highlights / Policies
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',

            'highlights' => 'required|array|min:1',
            'highlights.*.title' => 'required|string|max:100',
            'highlights.*.description' => 'required|string',

            'policies' => 'required|array|min:1',
            'policies.*.title' => 'required|string|max:100',
            'policies.*.content' => 'required|string',
            'policies.*.type' => 'required|in:1,2,3',


            // Specifications
            'specifications' => 'required|array',
            'specifications.*.title' => 'required|string|max:100',
            'specifications.*.items' => 'required|array|min:1',
            'specifications.*.items.*.key' => 'required|string|max:100',
            'specifications.*.items.*.value' => 'required|string|max:255',

            // Variants
            'variants' => 'required|array|min:1',
            'variants.*.color' => 'required|string|max:100',
            'variants.*.images' => 'required|array|min:1',
            'variants.*.images.*' => 'required|file|mimes:jpg,jpeg,png,svg,webp',

            // Variant Storages
            'variants.*.storages' => 'required|array|min:1',
            'variants.*.storages.*.storage' => 'required|string|max:100',
            'variants.*.storages.*.quantity' => 'required|integer|min:0',
            'variants.*.storages.*.low_stock_threshold' => 'required|integer|min:0',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $policies = $this->input('policies', []);

            $typeLabels = [
                1 => 'Warranty',
                2 => 'Shipping',
                3 => 'Return',
            ];

            $typeCounts = collect($policies)
                ->pluck('type')
                ->countBy();

            foreach ($typeLabels as $type => $label) {
                if (($typeCounts[$type] ?? 0) > 1) {
                    $validator->errors()->add(
                        'policies',
                        "Only one {$label} policy is allowed per product."
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required.',
            'name.unique' => 'This product name already exists.',
            'brand_id.required' => 'Brand is required.',
            'brand_id.exists' => 'Selected brand does not exist.',
            'subcategory_id.required' => 'Subcategory is required.',
            'subcategory_id.exists' => 'Selected subcategory does not exist.',
            'summary.required' => 'Product summary is required.',
            'overview.required' => 'Product overview is required.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Product price must be at least 0.',
            'discount_percentage.max' => 'Discount cannot exceed 100%.',

            'highlights.required' => 'At least one highlight is required.',
            'highlights.*.title.required' => 'Highlight title is required.',
            'highlights.*.description.required' => 'Highlight description is required.',

            'policies.required' => 'At least one policy is required.',
            'policies.*.title.required' => 'Policy title is required.',
            'policies.*.content.required' => 'Policy content is required.',
            'policies.*.type.required' => 'Policy type is required.',

            'specifications.*.title.required' => 'Specification title is required.',
            'specifications.*.items.required' => 'Specification items are required.',
            'specifications.*.items.*.key.required' => 'Specification key is required.',
            'specifications.*.items.*.value.required' => 'Specification value is required.',

            'variants.required' => 'At least one product variant is required.',
            'variants.*.color.required' => 'Variant color is required.',
            'variants.*.images.required' => 'At least one variant image is required.',
            'variants.*.images.*.file' => 'Each variant image must be a valid file.',

            'variants.*.storages.required' => 'At least one storage option is required.',
            'variants.*.storages.*.storage.required' => 'Storage name is required.',
            'variants.*.storages.*.quantity.required' => 'Quantity is required.',
            'variants.*.storages.*.low_stock_threshold.required' => 'Low stock threshold is required.',
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
