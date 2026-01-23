<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class UpdateImageHandlerService
{
    /**
     * Validate an image field that can be a file or a string (URL/path)
     *
     * @param mixed $value
     * @param string $fieldName
     * @return array [valid => bool, message => string|null]
     */
    public function validateFileOrString($value, string $fieldName = 'Image'): array
    {
        if (empty($value)) {
            return ['valid' => false, 'message' => "$fieldName cannot be empty."];
        }

        // If it's an uploaded file
        if ($value instanceof UploadedFile) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];
            if (!in_array($value->getMimeType(), $allowedMimeTypes)) {
                return ['valid' => false, 'message' => "$fieldName must be jpg, jpeg, png, svg or webp."];
            }
        }
        // If it's a string (URL or path)
        elseif (is_string($value)) {
            if (trim($value) === '') {
                return ['valid' => false, 'message' => "$fieldName cannot be empty."];
            }
        } else {
            return ['valid' => false, 'message' => "$fieldName must be a file or a valid URL/path."];
        }

        return ['valid' => true, 'message' => null];
    }

   
}
