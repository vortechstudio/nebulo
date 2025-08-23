<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\Objects;
use App\Services\ObjectStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ObjectController extends Controller
{
    protected $objectStorageService;

    public function __construct(ObjectStorageService $objectStorageService)
    {
        $this->objectStorageService = $objectStorageService;
    }

    public function index(Bucket $bucket)
    {
        $this->authorize('view', $bucket);
        return $bucket->objects;
    }

    public function store(Request $request, Bucket $bucket)
    {
        $this->authorize('update', $bucket);

        $request->validate([
            'file' => 'required|file',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $objectName = $request->input('name', $file->getClientOriginalName());
        $bucketName = $bucket->name;

        $path = $this->objectStorageService->putObject($bucketName, $objectName, file_get_contents($file->getRealPath()));

        $object = Objects::create([
            'bucket_id' => $bucket->id,
            'name' => $objectName,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'metadata' => json_encode([]),
        ]);

        return response()->json($object, 201);
    }

    public function show(Bucket $bucket, Objects $object)
    {
        $this->authorize('view', $bucket);
        $this->authorize('view', $object);

        if ($object->bucket_id !== $bucket->id) {
            abort(404);
        }

        // Extraire le bucketName et objectName à partir du path stocké
        $pathParts = explode('/', $object->path);
        $bucketName = $pathParts[0];
        $objectName = $object->name;

        $content = $this->objectStorageService->getObject($bucketName, $objectName);

        return response($content)
            ->header('Content-Type', $object->mime_type)
            ->header('Content-Length', $object->size);
    }

    public function destroy(Bucket $bucket, Objects $object)
    {
        $this->authorize('delete', $bucket);
        $this->authorize('delete', $object);

        if ($object->bucket_id !== $bucket->id) {
            abort(404);
        }

        // Extraire le bucketName à partir du path stocké
        $pathParts = explode('/', $object->path);
        $bucketName = $pathParts[0];
        $objectName = $object->name;

        $this->objectStorageService->deleteObject($bucketName, $objectName);
        $object->delete();

        return response()->noContent();
    }
}
