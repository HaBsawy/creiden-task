<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\ItemRequest;
use App\Http\Requests\API\UserItemRequest;
use App\Http\Services\ItemService;
use App\Models\Item;
use Exception;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            return ResponseHelper::make(ItemService::getWithUser());
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ItemRequest $request
     * @return JsonResponse
     */
    public function store(ItemRequest $request): JsonResponse
    {
        try {
            $item = ItemService::create($request);
            return ResponseHelper::make($item, 'The item created successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserItemRequest $request
     * @return JsonResponse
     */
    public function storeUserItem(UserItemRequest $request): JsonResponse
    {
        try {
            $item = ItemService::create($request);
            return ResponseHelper::make($item, 'The item created successfully',
                true, 201);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Item $item
     * @return JsonResponse
     */
    public function show(Item $item): JsonResponse
    {
        return ResponseHelper::make($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ItemRequest $request
     * @param Item $item
     * @return JsonResponse
     */
    public function update(ItemRequest $request, Item $item): JsonResponse
    {
        try {
            ItemService::update($item, $request);
            return ResponseHelper::make($item, 'The item updated successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Item $item
     * @return JsonResponse
     */
    public function destroy(Item $item): JsonResponse
    {
        try {
            $item->delete();
            return ResponseHelper::make(null, 'The item deleted successfully',
                true, 202);
        } catch (Exception) {
            return ResponseHelper::wentWrong();
        }
    }
}
