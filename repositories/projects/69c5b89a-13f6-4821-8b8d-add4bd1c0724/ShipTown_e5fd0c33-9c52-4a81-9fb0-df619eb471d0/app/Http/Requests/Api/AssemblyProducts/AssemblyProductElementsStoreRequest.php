<?php

namespace App\Http\Requests\Api\AssemblyProducts;

use Illuminate\Foundation\Http\FormRequest;

class AssemblyProductElementsStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'sku' => 'string|required|max:50',
        ];
    }
}
