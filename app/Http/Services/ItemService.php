<?php

namespace App\Http\Services;

use App\Http\Requests\ApiRequest;
use App\Models\Item;

class ItemService
{
    public static function getWithUser()
    {
        return Item::select(['id', 'storage_id', 'name', 'description'])
            ->with('storage.user')->paginate();
    }

    public static function create(ApiRequest $request)
    {
        return Item::create([
            'storage_id'    => auth('user')->check() ?
                auth('user')->user()->storage->id : $request->get('storage_id'),
            'name'          => $request->get('name'),
            'description'   => $request->get('description'),
        ]);
    }

    public static function update(Item $item, ApiRequest $request)
    {
        return $item->update([
            'storage_id'    => $request->get('storage_id'),
            'name'          => $request->get('name'),
            'description'   => $request->get('description'),
        ]);
    }
}
