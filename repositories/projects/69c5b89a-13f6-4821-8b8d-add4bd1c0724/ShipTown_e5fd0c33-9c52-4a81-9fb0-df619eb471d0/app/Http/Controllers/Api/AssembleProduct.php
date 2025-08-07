<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssembleProductStoreRequest;
use App\Http\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Exception;
use Illuminate\Support\Facades\DB;

class AssembleProduct extends Controller
{
    public function store(AssembleProductStoreRequest $request): ProductResource
    {
        $attributes = $request->validated();
        $product = Product::findOrFail($attributes['product_id']);
        $quantity = $attributes['quantity'];
        $inventory = Inventory::query()
            ->where([
                'product_id' => $request->get('product_id'),
                'warehouse_id' => $request->user()->warehouse_id,
            ])
            ->first();

        if ($product->type === 'simple') {
            $product->update(['type' => 'assembly']);
        }

        try {
            $product->load('assemblyProducts');

            foreach ($product->assemblyProducts as $assemblyProduct) {
                $element = Product::findOrFail($assemblyProduct->simple_product_id);
                $element->load('inventory');
                $inv = $element->inventory($request->user()->warehouse_code)->first();

                if (!$inv) {
                    throw new Exception('Inventory not found for product with SKU: ' . $element->sku);
                }

                $total = $quantity * $assemblyProduct->required_quantity;

                if ($inv->quantity_available < $total) {
                    throw new Exception('Not enough stock for product with SKU: ' . $element->sku . '. Available: ' . $inv->quantity_available . ', Required: ' . $total);
                }
            }

            DB::transaction(function () use ($product, $quantity, $inventory, $request) {
                foreach ($product->assemblyProducts as $assemblyProduct) {
                    $element = Product::findOrFail($assemblyProduct->simple_product_id);
                    $element->load('inventory');
                    $inv = $element->inventory($request->user()->warehouse_code)->first();

                    InventoryService::adjust(
                        $inv,
                        $quantity * $assemblyProduct->required_quantity * -1,
                        ['description' => "Assembled product element (SKU: $product->sku)"],
                    );
                }

                InventoryService::adjust(
                    $inventory,
                    $quantity,
                    ['description' => "Assembled product (SKU: $product->sku)"],
                );
            });
        } catch (Exception $exception) {
            $this->respondBadRequest($exception->getMessage());
        }

        return ProductResource::make($product->refresh());
    }
}
