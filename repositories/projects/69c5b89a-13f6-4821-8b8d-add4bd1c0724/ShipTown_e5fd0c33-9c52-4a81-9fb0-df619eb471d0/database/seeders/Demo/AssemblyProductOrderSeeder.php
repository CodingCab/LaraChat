<?php

namespace Database\Seeders\Demo;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\Services\InventoryService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssemblyProductOrderSeeder extends Seeder
{
    public function run(): void
    {
        $assembledTable = Product::query()->updateOrCreate([
            'sku' => 'ASSEM-100001',
        ], [
            'name' => t('Wooden Table 5407'),
            'type' => 'assembly',
            'price' => 0,
            'sale_price' => 0,
            'default_tax_code' => 'VAT_23',
        ]);

        $user = User::query()->firstOrCreate(['email' => 'demo-admin@ship.town']);

        Inventory::query()
            ->where('warehouse_code', $user->warehouse->code)
            ->where('product_id', $assembledTable->getKey())
            ->each(function (Inventory $inventory) use ($assembledTable) {
                InventoryService::stocktake($inventory, 0, [
                    'description' => t('Initial stocktake'),
                    'occurred_at' => now(),
                ]);
            });

        $leg = Product::query()->updateOrCreate([
            'sku' => 'ASSEM-100001-01',
        ],[
            'name' => t('Wooden leg 5407'),
            'type' => 'simple',
            'price' => 100,
            'sale_price' => 100,
            'default_tax_code' => 'VAT_23',
        ]);

        Inventory::query()
            ->where('warehouse_code', $user->warehouse->code)
            ->where('product_id', $leg->getKey())
            ->each(function (Inventory $inventory) {
                InventoryService::stocktake($inventory, 18, [
                    'description' => t('Initial stocktake'),
                    'occurred_at' => now(),
                ]);
            });

        $tableTop = Product::query()->updateOrCreate([
            'sku' => 'ASSEM-100001-02',
        ],[
            'name' => t('Table Top 5407'),
            'type' => 'simple',
            'price' => 500,
            'sale_price' => 500,
            'default_tax_code' => 'VAT_23',
        ]);

        Inventory::query()
            ->where('warehouse_code', $user->warehouse->code)
            ->where('product_id', $tableTop->getKey())
            ->each(function (Inventory $inventory) {
                InventoryService::stocktake($inventory, 8, [
                    'description' => t('Initial stocktake'),
                    'occurred_at' => now(),
                ]);
            });

        $packOfScrews = Product::query()->updateOrCreate([
            'sku' => 'ASSEM-100001-03',
        ],[
            'name' => t('Pack of screws for ASSEM-100001'),
            'type' => 'simple',
            'price' => 20,
            'sale_price' => 20,
            'default_tax_code' => 'VAT_23',
        ]);

        Inventory::query()
            ->where('warehouse_code', $user->warehouse->code)
            ->where('product_id', $packOfScrews->getKey())
            ->each(function (Inventory $inventory) {
                InventoryService::stocktake($inventory, 8, [
                    'description' => t('Initial stocktake'),
                    'occurred_at' => now(),
                ]);
            });

        AssemblyProductsElement::query()->updateOrCreate([
            'assembly_product_id' => $assembledTable->getKey(),
            'simple_product_id' => $tableTop->getKey(),
        ], [
            'required_quantity' => 1,
        ]);

        AssemblyProductsElement::query()->updateOrCreate([
            'assembly_product_id' => $assembledTable->getKey(),
            'simple_product_id' => $leg->getKey(),
        ], [
            'required_quantity' => 4,
        ]);

        AssemblyProductsElement::query()->updateOrCreate([
            'assembly_product_id' => $assembledTable->getKey(),
            'simple_product_id' => $packOfScrews->getKey(),
        ], [
            'required_quantity' => 1,
        ]);

        $order = Order::query()->updateOrCreate([
            'order_number' => 'AP100001',
        ], [
            'status_code' => 'multi_box',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        $order->orderProducts()->delete();

        OrderProduct::query()->create([
            'order_id' => $order->getKey(),
            'product_id' => $assembledTable->getKey(),
            'quantity_ordered' => 1,
            'quantity_split' => 1,
            'price' => $assembledTable->price,
            'name_ordered' => $assembledTable->name,
            'sku_ordered' => $assembledTable->sku,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->getKey(),
            'parent_product_id' => $assembledTable->getKey(),
            'product_id' => $leg->getKey(),
            'quantity_ordered' => 4,
            'price' => $leg->price,
            'name_ordered' => $leg->name,
            'sku_ordered' => $leg->sku,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->getKey(),
            'parent_product_id' => $assembledTable->getKey(),
            'product_id' => $tableTop->getKey(),
            'quantity_ordered' => 1,
            'price' => $tableTop->price,
            'name_ordered' => $tableTop->name,
            'sku_ordered' => $tableTop->sku,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->getKey(),
            'product_id' => $packOfScrews->getKey(),
            'quantity_ordered' => 1,
            'price' => $packOfScrews->price,
            'name_ordered' => $packOfScrews->name,
            'sku_ordered' => $packOfScrews->sku,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('IFNULL(total_order, 0)')]);
    }

    private function createIrishShippingAddress()
    {
        return OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
    }
}
