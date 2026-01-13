<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Store uploaded image and return path
     */
    public function store(UploadedFile $file, string $folder = 'images'): string
    {
        return $file->store($folder, 'public');
    }

    /**
     * Delete an image by path
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
