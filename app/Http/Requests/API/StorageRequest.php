<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StorageRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->isMethod('POST') ? [
            'user_id' => 'required|exists:users,id|unique:storages,user_id',
        ] : [
            'user_id' => 'required|exists:users,id|unique:storages,user_id,' .
                $this->route('storage')->id,
        ];
    }
}
