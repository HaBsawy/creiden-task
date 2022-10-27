<?php

namespace App\Http\Services;

use App\Http\Requests\ApiRequest;
use App\Models\User;

class UserService
{
    public static function getWithStorage()
    {
        return User::select(['id', 'name', 'email'])->with('storage')->paginate();
    }

    public static function getByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public static function create(ApiRequest $request)
    {
        return User::create([
            'name'      => $request->get('name'),
            'email'     => $request->get('email'),
            'password'  => bcrypt($request->get('password'))
        ]);
    }

    public static function update(User $user, ApiRequest $request)
    {
        return $user->update([
            'name'      => $request->get('name'),
            'email'     => $request->get('email'),
            'password'  => bcrypt($request->get('password'))
        ]);
    }
}
