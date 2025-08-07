<?php

namespace App\Http\Resources;

use App\Models\OrderAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderAddress
 */
class OrderAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'company' => $this->company,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'locker_box_code' => $this->locker_box_code,
            'email' => $this->email,
            'fax' => $this->fax,
            'first_name' => $this->first_name,
            'gender' => $this->gender,
            'id' => $this->id,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'postcode' => $this->postcode,
            'region' => $this->region,
            'tax_exempt' => $this->tax_exempt,
            'state_code' => $this->state_code,
            'state_name' => $this->state_name,
            'website' => $this->website,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'tax_id' => $this->tax_id,
            'discount_code' => $this->discount_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
