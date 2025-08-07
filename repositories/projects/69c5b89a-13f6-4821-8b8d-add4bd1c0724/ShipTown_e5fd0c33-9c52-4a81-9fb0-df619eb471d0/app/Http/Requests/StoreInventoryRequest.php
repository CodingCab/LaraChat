<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:inventory,id'],
            'reorder_point' => ['sometimes', 'numeric'],
            'restock_level' => ['sometimes', 'numeric'],
            'shelve_location' => ['sometimes', 'string'],
            'shelf_location' => ['sometimes', 'string'],
        ];
    }
}
