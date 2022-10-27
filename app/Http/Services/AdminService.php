<?php

namespace App\Http\Services;

use App\Http\Requests\ApiRequest;
use App\Models\Admin;

class AdminService
{
    public static function create(ApiRequest $request)
    {
        return Admin::create([
            'name'      => $request->get('name'),
            'email'     => $request->get('email'),
            'password'  => bcrypt($request->get('password'))
        ]);
    }

    public static function getByEmail($email)
    {
        return Admin::where('email', $email)->first();
    }
}
