<?php

namespace App\Http\Services;

use App\Http\Requests\ApiRequest;
use App\Models\Storage;

class StorageService
{
    public static function getWithUser()
    {
        return Storage::select(['id', 'user_id'])->with('user')->paginate();
    }

    public static function create(ApiRequest $request)
    {
        return Storage::create([
            'user_id' => $request->get('user_id'),
        ]);
    }

    public static function update(Storage $storage, ApiRequest $request)
    {
        return $storage->update([
            'user_id' => $request->get('user_id'),
        ]);
    }
}
