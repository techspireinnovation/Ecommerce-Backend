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
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $variantId = $this->input('product_variant_storage_id');
                    if (!$this->stockService->isQuantityAvailableForCart($variantId, $value)) {
                        $available = $this->stockService->getAvailableQuantity($variantId);
                        $fail("Requested quantity exceeds available stock ({$available}).");
                    }
                },
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

            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
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
