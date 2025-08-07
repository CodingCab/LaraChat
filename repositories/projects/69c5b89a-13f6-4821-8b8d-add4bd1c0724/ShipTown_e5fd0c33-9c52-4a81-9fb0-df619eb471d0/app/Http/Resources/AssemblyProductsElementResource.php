<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssemblyProductsElementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assembly_product_id' => $this->assembly_product_id,
            'simple_product_id' => $this->simple_product_id,
            'required_quantity' => $this->required_quantity,
            'assemblyProduct' => new ProductResource($this->whenLoaded('assemblyProduct')),
            'simpleProduct' => new ProductResource($this->whenLoaded('simpleProduct')),
        ];
    }
}
