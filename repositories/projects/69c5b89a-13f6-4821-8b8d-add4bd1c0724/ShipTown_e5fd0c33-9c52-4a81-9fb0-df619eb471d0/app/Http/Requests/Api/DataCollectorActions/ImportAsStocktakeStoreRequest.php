<?php

namespace App\Http\Requests\Api\DataCollectorActions;

use Illuminate\Foundation\Http\FormRequest;

class ImportAsStocktakeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_collection_id' => 'required|integer|exists:data_collections,id',
        ];
    }
}
