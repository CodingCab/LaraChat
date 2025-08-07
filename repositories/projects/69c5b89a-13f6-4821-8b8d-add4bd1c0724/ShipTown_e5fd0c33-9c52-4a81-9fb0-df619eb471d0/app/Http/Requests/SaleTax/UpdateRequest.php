<?php

namespace App\Http\Requests\SaleTax;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $warehouse_id
 */
class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string',
            'rate' => 'required|numeric',
        ];
    }
}
