<?php

namespace App\Services\Unit;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UnitPhotoService
{
    protected $disk = 'public';
    protected $basePath = 'unit-photos';
    
    public function uploadPhoto(UploadedFile $photo): string
    {
        $filename = $this->generateFilename($photo);
        $path = $this->basePath . '/' . $filename;
        
        $photo->storeAs($this->basePath, $filename, $this->disk);
        
        return $path;
    }
    
    public function deletePhoto(string $photoPath): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($photoPath)) {
                return Storage::disk($this->disk)->delete($photoPath);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getPhotoUrl(?string $photoPath): ?string
    {
        if (empty($photoPath)) {
            return $this->getDefaultPhotoUrl();
        }
        
        if (!Storage::disk($this->disk)->exists($photoPath)) {
            return $this->getDefaultPhotoUrl();
        }
        
        return asset('storage/' . $photoPath);
    }
    
    public function getDefaultPhotoUrl(): string
    {
        return 'https://ui-avatars.com/api/?name=Unit&background=3b82f6&color=ffffff&size=400';
    }
    
    public function validatePhoto(UploadedFile $photo): array
    {
        $errors = [];
        
        $maxSize = 5 * 1024 * 1024;
        if ($photo->getSize() > $maxSize) {
            $errors[] = 'Photo size must be less than 5MB';
        }
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($photo->getMimeType(), $allowedMimes)) {
            $errors[] = 'Photo must be a JPEG, PNG, WebP, or GIF image';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    private function generateFilename(UploadedFile $photo): string
    {
        $extension = $photo->getClientOriginalExtension();
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $originalName);
        $cleanName = substr($cleanName, 0, 50);
        
        return "{$cleanName}_{$timestamp}_{$random}.{$extension}";
    }
}