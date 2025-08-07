<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductsMergeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku1' => 'required|string|exists:products_aliases,alias',
            'sku2' => 'required|string|exists:products_aliases,alias',
        ];
    }
}
