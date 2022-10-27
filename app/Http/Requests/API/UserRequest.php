<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ApiRequest;

class UserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->isMethod('POST') ? [
            'name'      => 'required|string|between:3,255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
        ] : [
            'name'      => 'required|string|between:3,255',
            'email'     => 'required|email|unique:users,email,' . $this->route('user')->id,
            'password'  => 'required|min:8',
        ];
    }
}
