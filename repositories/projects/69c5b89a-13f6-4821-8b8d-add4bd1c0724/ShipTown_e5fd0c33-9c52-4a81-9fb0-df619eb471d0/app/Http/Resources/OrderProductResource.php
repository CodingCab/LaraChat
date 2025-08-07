<?php

namespace App\Http\Resources;

use App\Models\OrderProduct;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderProduct
 */
class OrderProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'parent_product_id' => $this->parent_product_id,
            'product_id' => $this->product_id,
            'sku_ordered' => $this->sku_ordered,
            'name_ordered' => $this->name_ordered,
            'is_shipped' => $this->is_shipped,
            'price' => $this->price,
            'unit_tax' => $this->unit_tax,
            'tax_rate' => $this->tax_rate,
            'unit_full_price' => $this->unit_full_price,
            'unit_discount' => $this->unit_discount,
            'unit_sold_price' => $this->unit_sold_price,
            'total_sold_price' => $this->total_sold_price,
            'total_discount' => $this->total_discount,
            'quantity_ordered' => $this->quantity_ordered,
            'quantity_split' => $this->quantity_split,
            'quantity_picked' => $this->quantity_picked,
            'quantity_skipped_picking' => $this->quantity_skipped_picking,
            'quantity_shipped' => $this->quantity_shipped,
            'quantity_to_ship' => $this->quantity_to_ship,
            'inventory_source_location_id' => $this->inventory_source_location_id,
            'inventory_source_shelf_location' => $this->inventory_source_shelf_location,
            'inventory_source_quantity' => $this->inventory_source_quantity,
            'order' => new OrderResource($this->whenLoaded('order')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'parent_product' => new ProductResource($this->whenLoaded('parentProduct')),
        ];
    }
}
