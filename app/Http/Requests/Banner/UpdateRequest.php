<?php

namespace App\Http\Requests\Banner;

use App\Models\Banner;
use App\Services\UpdateImageHandlerService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
    private UpdateImageHandlerService $updateImageHandlerService;

    public function __construct(UpdateImageHandlerService $updateImageHandlerService)
    {
        $this->updateImageHandlerService = $updateImageHandlerService;
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:1,2,3,4',
            'image' => [
                'required',
                function ($attribute, $value, $fail) {
                    $result = $this->updateImageHandlerService->validateFileOrString($value, 'Banner image');
                    if (!$result['valid']) {
                        $fail($result['message']);
                    }
                }
            ],
            'status' => 'sometimes|in:0,1',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $bannerId = $this->route('banner');

            if ($type) {
                $typeLabels = [
                    1 => 'Home Page',
                    2 => 'Hero Page',
                    3 => 'Ads',
                    4 => 'About Page',
                ];

                // Check for existing active banner of this type, excluding current record
                $exists = Banner::query()
                    ->where('type', $type)
                    ->where('status', 0)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $bannerId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'type',
                        "Only one active banner is allowed for {$typeLabels[$type]}."
                    );
                }
            }
        });
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Banner title is required.',
            'type.required' => 'Banner type is required.',
            'type.in' => 'Invalid banner type.',
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
