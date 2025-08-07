<?php

namespace App\Http\Resources;

use App\Models\DataCollectionRecord;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DataCollectionRecord
 */
class DataCollectionRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'product_id' => $this->product_id,
            'quantity_requested' => $this->quantity_requested,
            'quantity_scanned' => $this->quantity_scanned,
            'quantity_to_scan' => $this->quantity_to_scan,
            'is_scanned' => $this->is_scanned,
            'sales_tax_code' => $this->sales_tax_code,
            'unit_tax' => $this->unit_tax,
            'calculated_unit_tax' => $this->calculated_unit_tax,
            'total_tax' => $this->total_tax,
            'calculated_total_tax' => $this->calculated_total_tax,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'product' => ProductResource::make($this->product),
            'parent_product' => ProductResource::make($this->parentProduct),
        ];
    }
}
