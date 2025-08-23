<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ObjectStorageService;
use Illuminate\Support\Facades\DB;

class BucketController extends Controller
{
    protected $objectStorageService;

    public function __construct()
    {
        $this->objectStorageService = new ObjectStorageService();
        // Add automatic resource authorization
        $this->authorizeResource(Bucket::class, 'bucket');
    }

    public function index()
    {
        // Filter by current user to align with policy and avoid exposing other users' buckets
        return Bucket::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        // authorizeResource automatically handles 'create' authorization
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:buckets,name,NULL,id,user_id,'.Auth::id(),
            'limit_size' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            $created = $this->objectStorageService->createBucket($validated['name']);

            if(!$created) {
                abort(409, "Le Bucket existe déjà");
            }

            try {
                return Bucket::create([
                    'name' => $validated['name'],
                    'limit_size' => $validated['limit_size'],
                    'description' => $validated['description'] ?? null,
                    'user_id' => Auth::id()
                ]);
            } catch (\Exception $e) {
                $this->objectStorageService->deleteBucket($validated['name']);
                throw $e;
            }
        });
    }

    public function show(Bucket $bucket)
    {
        $this->authorize('view', $bucket);
        return $bucket;
    }

    public function update(Request $request, Bucket $bucket)
    {
        $this->authorize('update', $bucket);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:buckets,name,'.$bucket->id.',id,user_id,'.Auth::id(),
            'limit_size' => 'required|integer',
        ]);

        return DB::transaction(function () use ($bucket, $validated) {
            $old = $bucket->name;
            $new = $validated['name'];
            $renamed = $this->objectStorageService->renameBucket($old, $new);
            if (!$renamed) {
                abort(409, 'Échec du renommage du bucket côté stockage.');
            }
            try {
                $bucket->update(['name' => $new]);
                return $bucket;
            } catch (\Throwable $e) {
                // tentative de rollback côté stockage
                $this->objectStorageService->renameBucket($new, $old);
                throw $e;
            }
        });
    }

    public function destroy(Bucket $bucket)
    {
        $this->authorize('delete', $bucket);

        // Check if bucket has objects before attempting deletion
        if ($bucket->objects()->exists()) {
            return response()->json([
                'message' => 'Cannot delete bucket that contains objects. Please delete all objects first.'
            ], 409);
        }

        // Attempt to delete the bucket from storage
        $storageDeleted = $this->objectStorageService->deleteBucket($bucket->name);

        if (!$storageDeleted) {
            return response()->json([
                'message' => 'Failed to delete bucket from storage. Please try again later.'
            ], 500);
        }

        // Only delete from database if storage deletion was successful
        $bucket->delete();
        return response()->noContent();
    }
}
