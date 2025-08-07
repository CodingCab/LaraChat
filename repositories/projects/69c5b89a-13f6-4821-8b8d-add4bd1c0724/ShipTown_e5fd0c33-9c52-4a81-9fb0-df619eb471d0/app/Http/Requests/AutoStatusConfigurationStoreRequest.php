<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoStatusConfigurationStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|integer|exists:modules_autostatus_picking_configurations,id',
            'from_status_code' => 'required|string|max:255',
            'to_status_code' => 'required|string|max:255',
            'desired_order_count' => 'required|integer|min:0',
            'refill_only_at_0' => 'sometimes|boolean',
        ];
    }
}
