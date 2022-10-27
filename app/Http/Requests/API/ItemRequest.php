<?php

namespace App\Http\Requests\API;

use App\Http\Requests\ApiRequest;

class ItemRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->isMethod('POST') ? [
            'storage_id'    => 'required|exists:storages,id',
            'name'          => 'required|string|between:3,255',
            'description'   => 'required|string|min:3',
        ] : [
            'name'          => 'required|string|between:3,255',
            'description'   => 'required|string|min:3',
        ];
    }
}
