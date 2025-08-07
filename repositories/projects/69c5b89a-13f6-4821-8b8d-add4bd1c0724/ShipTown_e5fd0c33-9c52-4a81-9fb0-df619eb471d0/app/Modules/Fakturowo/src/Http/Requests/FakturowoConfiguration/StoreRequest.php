<?php

namespace App\Modules\Fakturowo\src\Http\Requests\FakturowoConfiguration;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'api_key' => 'required|string',
            'connection_code' => 'required|string',
            'api_url' => 'sometimes|string|nullable',
        ];
    }
}
