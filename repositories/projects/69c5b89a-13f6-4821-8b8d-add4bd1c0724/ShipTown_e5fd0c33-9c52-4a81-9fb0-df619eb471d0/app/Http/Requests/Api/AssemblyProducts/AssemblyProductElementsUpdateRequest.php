<?php

namespace App\Http\Requests\Api\AssemblyProducts;

use Illuminate\Foundation\Http\FormRequest;

class AssemblyProductElementsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric|min:1',
        ];
    }
}
