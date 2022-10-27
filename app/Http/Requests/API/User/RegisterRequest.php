<?php

namespace App\Http\Requests\API\User;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'      => 'required|string|between:3,255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',
        ];
    }
}
