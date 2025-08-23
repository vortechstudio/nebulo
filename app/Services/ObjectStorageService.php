<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ObjectStorageService
{
    protected $disk;

    public function __construct(?FilesystemAdapter $disk = null)
    {
        $this->disk = $disk ?? Storage::disk('objectstorage');
    }

    public function createBucket(string $bucketName): bool
    {
        if ($this->disk->exists($bucketName)) {
            return false;
        }
        try {
            $this->disk->makeDirectory($bucketName);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    public function renameBucket(string $bucketName, string $newName): bool
    {
        // Ensure source exists and target does not, to avoid overwriting
        if (!$this->disk->exists($bucketName) || $this->disk->exists($newName)) {
            return false;
        }
        // Attempt to rename, reporting any I/O errors
        try {
            return $this->disk->move($bucketName, $newName);
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    public function deleteBucket(string $bucketName): bool
    {
        if (!$this->disk->exists($bucketName)) {
            return false;
        }

        try {
            $this->disk->deleteDirectory($bucketName);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    public function putObject(string $bucketName, string $objectName, $content, array $metadata = []): string
    {
        // Logic to store the object
        // The path will be like: bucketName/objectName
        $path = $bucketName . '/' . $objectName;
        $this->disk->put($path, $content);

        // Store metadata if needed (e.g., in a database or separate file)
        // For now, we'll just return the path
        return $path;
    }

    public function getObject(string $bucketName, string $objectName)
    {
        // Logic to retrieve the object
        $path = $bucketName . '/' . $objectName;
        if ($this->disk->exists($path)) {
            return $this->disk->get($path);
        }
        return null;
    }

    public function deleteObject(string $bucketName, string $objectName): bool
    {
        // Logic to delete the object
        $path = $bucketName . '/' . $objectName;
        if ($this->disk->exists($path)) {
            return $this->disk->delete($path);
        }
        return false;
    }

    public function objectExists(string $bucketName, string $objectName): bool
    {
        $path = $bucketName . '/' . $objectName;
        return $this->disk->exists($path);
    }

    public function getObjectSize(string $bucketName, string $objectName): ?int
    {
        $path = $bucketName . '/' . $objectName;
        if ($this->disk->exists($path)) {
            return $this->disk->size($path);
        }
        return null;
    }

    public function getObjectMimeType(string $bucketName, string $objectName): ?string
    {
        $path = $bucketName . '/' . $objectName;
        if ($this->disk->exists($path)) {
            return $this->disk->mimeType($path);
        }
        return null;
    }
}
