<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisassembleProductStoreRequest;
use App\Http\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Exception;
use Illuminate\Support\Facades\DB;

class DisassembleProduct extends Controller
{
    public function store(DisassembleProductStoreRequest $request): ProductResource
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

        try {
            $product->load('assemblyProducts');

            if ($inventory->quantity_available < $quantity) {
                throw new Exception('You cannot disassemble more than available stock for product with SKU: ' . $product->sku);
            }

            if (!$product->assemblyProducts) {
                throw new Exception('Product with SKU: ' . $product->sku . ' is not an assembly product.');
            }

            DB::transaction(function () use ($product, $quantity, $inventory, $request) {
                foreach ($product->assemblyProducts as $assemblyProduct) {
                    $element = Product::findOrFail($assemblyProduct->simple_product_id);
                    $element->load('inventory');
                    $inv = $element->inventory($request->user()->warehouse_code)->first();

                    if (!$inv) {
                        throw new Exception('Inventory not found for product with SKU: ' . $element->sku);
                    }

                    $total = $quantity * $assemblyProduct->required_quantity;

                    InventoryService::adjust(
                        $inv,
                        $total,
                        ['description' => "Disassembled product element (SKU: $product->sku)"],
                    );
                }

                InventoryService::adjust(
                    $inventory,
                    $quantity * -1,
                    ['description' => "Disassembled product (SKU: $product->sku)"],
                );
            });
        } catch (Exception $exception) {
            $this->respondBadRequest($exception->getMessage());
        }

        return ProductResource::make($product->refresh());
    }
}
