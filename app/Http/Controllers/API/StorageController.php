<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\StorageRequest;
use App\Http\Services\StorageService;
use App\Models\Storage;
use Exception;
use Illuminate\Http\JsonResponse;

class StorageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            return ResponseHelper::make(StorageService::getWithUser());
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorageRequest $request
     * @return JsonResponse
     */
    public function store(StorageRequest $request): JsonResponse
    {
        try {
            $storage = StorageService::create($request);
            return ResponseHelper::make($storage, 'The storage created successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Storage $storage
     * @return JsonResponse
     */
    public function show(Storage $storage): JsonResponse
    {
        return ResponseHelper::make($storage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StorageRequest $request
     * @param Storage $storage
     * @return JsonResponse
     */
    public function update(StorageRequest $request, Storage $storage): JsonResponse
    {
        try {
            StorageService::update($storage, $request);
            return ResponseHelper::make($storage, 'The storage updated successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Storage $storage
     * @return JsonResponse
     */
    public function destroy(Storage $storage): JsonResponse
    {
        try {
            $storage->delete();
            return ResponseHelper::make(null, 'The storage deleted successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }
}
