<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\Objects;
use App\Services\ObjectStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'name' => 'nullable|string|max:255|not_regex:/[\\\/]|\.\./',
        ]);

        // Normaliser le nom de l'objet pour éviter les injections de chemin
        $file = $request->file('file');
        $rawName = $request->input('name', $file->getClientOriginalName());
        $objectName = basename(str_replace('\\', '/', $rawName));
        $bucketName = $bucket->name;
        $path = null;

        try {
            // Démarrer une transaction pour garantir l'atomicité
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Utiliser $file->get() au lieu de file_get_contents pour une meilleure sécurité
            $path = $this->objectStorageService->putObject($bucketName, $objectName, $file->get());

            $object = Objects::create([
                'bucket_id' => $bucket->id,
                'name' => $objectName,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'metadata' => json_encode([]),
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return response()->json($object, 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            // Tentative de nettoyage du fichier stocké en cas d'échec
            if ($path) {
                try {
                    $this->objectStorageService->deleteObject($bucketName, $objectName);
                } catch (\Exception $deleteException) {
                    // Échec silencieux de la suppression (best-effort)
                    // On pourrait logger cette erreur dans un système de production
                }
            }
            
            throw $e;
        }
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
