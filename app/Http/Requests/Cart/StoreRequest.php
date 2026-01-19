<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Services\StockService;

class StoreRequest extends FormRequest
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        parent::__construct();
        $this->stockService = $stockService;
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',

            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at')
                    ->whereNotIn('status', [1, 4]),
            ],

            'items.*.product_variant_storage_id' => [
                'required',
                'integer',
                Rule::exists('product_variant_storages', 'id')
                    ->whereNull('deleted_at'),
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1] ?? null;
                    if ($index === null) return;

                    $variantId = $this->input("items.$index.product_variant_storage_id");
                    if (!$variantId) return;

                    if (!$this->stockService->isQuantityAvailableForCart($variantId, $value)) {
                        $available = $this->stockService->getAvailableQuantity($variantId);
                        $fail("Requested quantity exceeds available stock ({$available}).");
                    }
                },
            ],
        ];
    }

    /**
     * Additional cart-specific validation
     * - Prevent duplicate product + variant in the same request
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->items) return;

            $pairs = [];

            foreach ($this->items as $index => $item) {
                $pairKey = $item['product_id'].'-'.$item['product_variant_storage_id'];

                if (isset($pairs[$pairKey])) {
                    $validator->errors()->add(
                        "items.$index",
                        'Duplicate product variant detected in cart items.'
                    );
                }

                $pairs[$pairKey] = true;
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one cart item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one cart item must be added.',

            'items.*.product_id.required' => 'Product is required.',
            'items.*.product_id.integer' => 'Product ID must be an integer.',
            'items.*.product_id.exists' => 'Selected product is inactive or unavailable.',

            'items.*.product_variant_storage_id.required' => 'Product variant is required.',
            'items.*.product_variant_storage_id.integer' => 'Variant ID must be an integer.',
            'items.*.product_variant_storage_id.exists' => 'Selected variant does not exist.',

            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.integer' => 'Quantity must be an integer.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
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
