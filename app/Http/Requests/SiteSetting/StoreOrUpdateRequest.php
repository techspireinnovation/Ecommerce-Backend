<?php

namespace App\Http\Requests\SiteSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Site Setting fields
            'store_name' => 'required|string|max:100',
            'primary_mobile_no' => 'required|digits:10',
            'secondary_mobile_no' => 'nullable|digits:10',
            'primary_email' => 'required|email|max:100',
            'secondary_email' => 'nullable|email|max:100',
            'logo_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'fav_icon_image' => 'nullable|file|mimes:jpg,jpeg,png,ico,webp|max:1024',
            'instagram_link' => 'nullable|url',
            'facebook_link' => 'nullable|url',
            'whatsapp_link' => 'nullable|url',
            'linkedin_link' => 'nullable|url',

            // Address fields (all required)
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            // Type and label enforced for site address
            'type' => [
                'sometimes',
                Rule::in([2]),
            ],
            'label' => [
                'sometimes',
                Rule::in(['Store Location']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Site Setting messages
            'store_name.required' => 'Store name is required.',
            'primary_mobile_no.digits' => 'Primary mobile number must be exactly 10 digits.',
            'primary_email.email' => 'Primary email must be a valid email address.',
            'logo_image.mimes' => 'Logo must be jpg, jpeg, png or webp.',
            'fav_icon_image.mimes' => 'Favicon must be jpg, png, ico or webp.',

            // Address messages
            'street.required' => 'Street is required.',
            'city.required' => 'City is required.',
            'district.required' => 'District is required.',
            'province.required' => 'Province is required.',
            'zip.required' => 'ZIP code is required.',
            'latitude.required' => 'Latitude is required.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.required' => 'Longitude is required.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }

    /**
     * Override failedValidation to return JSON response with first error on top
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        // Grab the first error message
        $firstErrorMessage = reset($errors)[0] ?? 'Validation error';

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstErrorMessage,
            'errors' => $errors,
        ], 422));
    }
}
