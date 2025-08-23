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
    }

    public function index()
    {
        return Bucket::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:buckets,name,NULL,id,user_id,'.Auth::id(),
            'limit_size' => 'required|integer',
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
                    'user_id' => Auth::id()
                ]);
            } catch (\Exception $e) {
                $this->objectStorageService->deleteBuckets($validated['name']);
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

        $this->objectStorageService->renameBucket($bucket->name, $validated['name']);

        $bucket->update($validated);
        return $bucket;
    }

    public function destroy(Bucket $bucket)
    {
        $this->authorize('delete', $bucket);

        $this->objectStorageService->deleteBuckets($bucket->name);

        $bucket->delete();
        return response()->noContent();
    }
}
