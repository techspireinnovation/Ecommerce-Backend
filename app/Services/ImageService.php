<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $manager;
    protected int $maxWidth = 1600;
    protected int $quality = 80;

    public function __construct()
    {
        // Intervention Image v3 (GD driver)
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Store uploaded image, scale down if needed, and return path
     */
    public function store(UploadedFile $file, string $folder = 'images'): string
    {
        $image = $this->manager->read($file->getRealPath());

        $image->scaleDown(width: $this->maxWidth);

        $filename = uniqid('', true) . '.' . $file->getClientOriginalExtension();
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->put(
            $path,
            $image->toJpeg($this->quality)
        );

        return $path;
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

    /**
     * Replace previous image with a new one
     */
    public function replace(?string $oldPath, UploadedFile $newFile, string $folder = 'images'): string
    {
        $this->delete($oldPath);
        return $this->store($newFile, $folder);
    }
}
