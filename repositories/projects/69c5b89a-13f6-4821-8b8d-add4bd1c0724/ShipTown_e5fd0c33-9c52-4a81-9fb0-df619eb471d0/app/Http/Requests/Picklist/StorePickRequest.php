<?php

namespace App\Http\Requests\Picklist;

use Illuminate\Foundation\Http\FormRequest;

class StorePickRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_picked' => 'numeric|required_without:quantity_skipped_picking',
            'quantity_skipped_picking' => 'numeric|required_without:quantity_picked',
            'order_product_ids' => 'required|array',
            'order_product_ids.*' => 'numeric|exists:orders_products,id',
        ];
    }
}
