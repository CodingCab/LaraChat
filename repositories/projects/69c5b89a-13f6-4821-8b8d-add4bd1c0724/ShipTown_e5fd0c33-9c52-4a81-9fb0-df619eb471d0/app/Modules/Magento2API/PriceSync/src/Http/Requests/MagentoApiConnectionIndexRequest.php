<?php

namespace App\Modules\Magento2API\PriceSync\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
