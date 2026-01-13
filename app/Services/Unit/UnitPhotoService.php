<?php

namespace App\Services\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class UnitPhotoService
{
    protected $disk = 'public';
    protected $basePath = 'unit-photos';
    
    /**
     * Get disk name (public getter)
     */
    public function getDisk(): string
    {
        return $this->disk;
    }
    
    /**
     * Get base path (public getter)
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
    
    /**
     * Upload photo and return storage path
     */
    public function uploadPhoto(UploadedFile $photo, ?Unit $unit = null): string
    {
        // Generate unique filename
        $filename = $this->generateFilename($photo);
        $path = $this->basePath . '/' . $filename;
        
        // Store the photo
        $photo->storeAs($this->basePath, $filename, $this->disk);
        
        // Delete old photo if exists
        if ($unit && $unit->photo) {
            $this->deletePhoto($unit->photo);
        }
        
        return $path;
    }
    
    /**
     * Delete photo from storage
     */
    public function deletePhoto(string $photoPath): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($photoPath)) {
                return Storage::disk($this->disk)->delete($photoPath);
            }
            return true; // Already deleted
        } catch (\Exception $e) {
            Log::error('Failed to delete photo: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get URL for photo
     */
    public function getPhotoUrl(?string $photoPath): ?string
    {
        // Return default if no photo
        if (empty($photoPath)) {
            return $this->getDefaultPhotoUrl();
        }
        
        // Check if file exists in storage
        if (!Storage::disk($this->disk)->exists($photoPath)) {
            Log::warning("Photo not found in storage: {$photoPath}");
            return $this->getDefaultPhotoUrl();
        }
        
        // Generate URL for public disk
        return asset('storage/' . $photoPath);
    }
    
    /**
     * Get default photo URL (placeholder)
     */
    public function getDefaultPhotoUrl(): string
    {
        // Using a placeholder service
        return 'https://ui-avatars.com/api/?name=Unit&background=3b82f6&color=ffffff&size=400';
    }
    
    /**
     * Validate photo before upload
     */
    public function validatePhoto(UploadedFile $photo): array
    {
        $errors = [];
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($photo->getSize() > $maxSize) {
            $errors[] = 'Photo size must be less than 5MB';
        }
        
        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($photo->getMimeType(), $allowedMimes)) {
            $errors[] = 'Photo must be a JPEG, PNG, WebP, or GIF image';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $photo): string
    {
        $extension = $photo->getClientOriginalExtension();
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        // Clean original filename
        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $originalName);
        $cleanName = substr($cleanName, 0, 50);
        
        return "{$cleanName}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Ensure required directories exist
     */
    public function ensureDirectoriesExist(): void
    {
        $directories = [
            $this->basePath,
            $this->basePath . '/thumbnails',
        ];
        
        foreach ($directories as $directory) {
            $fullPath = storage_path('app/public/' . $directory);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }
    }
    
    /**
     * Get storage information for debugging
     */
    public function getStorageInfo(): array
    {
        $this->ensureDirectoriesExist();
        
        return [
            'disk' => $this->disk,
            'base_path' => $this->basePath,
            'storage_path' => storage_path('app/public/' . $this->basePath),
            'public_url' => asset('storage/' . $this->basePath),
            'directories' => [
                'main' => [
                    'exists' => file_exists(storage_path('app/public/' . $this->basePath)),
                    'path' => storage_path('app/public/' . $this->basePath),
                ],
                'thumbnails' => [
                    'exists' => file_exists(storage_path('app/public/' . $this->basePath . '/thumbnails')),
                    'path' => storage_path('app/public/' . $this->basePath . '/thumbnails'),
                ],
            ],
            'symlink' => [
                'exists' => file_exists(public_path('storage')),
                'is_link' => is_link(public_path('storage')),
                'target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
            ],
        ];
    }
    
    /**
     * Simple test method
     */
    public function testService(): array
    {
        $this->ensureDirectoriesExist();
        
        return [
            'status' => 'operational',
            'class' => get_class($this),
            'disk' => $this->getDisk(),
            'base_path' => $this->getBasePath(),
            'directories_ready' => [
                'main' => file_exists(storage_path('app/public/' . $this->basePath)),
                'thumbnails' => file_exists(storage_path('app/public/' . $this->basePath . '/thumbnails')),
            ],
            'default_url' => $this->getDefaultPhotoUrl(),
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}