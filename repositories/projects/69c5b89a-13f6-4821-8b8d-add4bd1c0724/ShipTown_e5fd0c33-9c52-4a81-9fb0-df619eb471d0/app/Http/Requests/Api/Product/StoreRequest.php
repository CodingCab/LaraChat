<?php

namespace App\Http\Requests\Api\Product;

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
            'sku' => 'string|required|max:50',
            'name' => 'string|required|max:100',
            'price' => 'required|numeric',
            'type' => 'string|required',
            'default_tax_code' => 'string|required',
            'pack_quantity' => 'nullable|integer|min:1',
            'product_number' => 'nullable|string|max:255'
        ];
    }
}
