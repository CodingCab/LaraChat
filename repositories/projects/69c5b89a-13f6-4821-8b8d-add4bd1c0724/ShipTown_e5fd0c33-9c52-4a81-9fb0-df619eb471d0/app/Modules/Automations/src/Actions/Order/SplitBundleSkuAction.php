<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class SplitBundleSkuAction extends BaseOrderActionAbstract
{
    public MessageBag $validation_errors;

    /**
     * @var array|\string[][]
     */
    public array $input_validation_rules = [
        'data' => ['array'],
        'data.*' => ['string', 'distinct', 'exists:products_aliases,alias'],
    ];

    private Collection $productsAdded;

    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        $skus = array_filter(explode(',', $options));

        if (! $this->isValidInput($options)) {
            return false;
        }

        $this->order->update(['is_editing' => true]);

        $skuToFind = array_shift($skus);

        $bundleOrderProducts = $this->order->orderProducts()
            ->where(['sku_ordered' => $skuToFind])
            ->where('quantity_to_ship', '>', 0)
            ->get();

        $this->splitBundles($skus, $bundleOrderProducts);

        $this->order->update(['is_editing' => false]);

        return true;
    }

    private function isValidInput(string $options): bool
    {
        $skus = array_filter(explode(',', $options));

        if (count($skus) < 2) {
            $this->order->log('Automation: Incorrect action value. Correct format: BundleSKU,SKU1,SKU2,SKU3...');

            return false;
        }

        $validator = Validator::make(['data' => $skus], $this->input_validation_rules);

        if ($validator->fails()) {
            $this->validation_errors = $validator->errors();

            return false;
        }

        return true;
    }

    private function splitBundles($skus, $orderProducts): void
    {
        $skusToAdd = collect($skus);

        $orderProducts->each(function (OrderProduct $originalOrderProduct) use ($skusToAdd) {
            Log::debug('Splitting order product', [
                'sku_ordered' => $originalOrderProduct->sku_ordered,
                'quantity_to_ship' => $originalOrderProduct->quantity_to_ship,
            ]);

            $quantity_to_ship = $originalOrderProduct->quantity_to_ship;
            $this->productsAdded = new Collection;
            $newOrderProducts_totalPrice = 0;

            $originalOrderProduct->update(['quantity_split' => $quantity_to_ship]);
            $skusToAdd->each(function (string $sku) use ($originalOrderProduct, $quantity_to_ship, &$newOrderProducts_totalPrice) {
                $product = Product::findBySKU($sku);
                $newOrderProduct = new OrderProduct([]);
                $newOrderProduct->sku_ordered = $sku;
                $newOrderProduct->order_id = $originalOrderProduct->order_id;
                $newOrderProduct->product_id = $product->id;
                $newOrderProduct->name_ordered = $product->name;
                $newOrderProduct->price = $product->prices()->max('price');
                $newOrderProduct->quantity_ordered = $quantity_to_ship;

                $newOrderProducts_totalPrice += $newOrderProduct->price;
                $this->productsAdded->add($newOrderProduct);
            });

            $priceMultiplier = $newOrderProducts_totalPrice / $originalOrderProduct->price;

            $this->productsAdded->each(function (OrderProduct $orderProduct) use ($priceMultiplier) {
                $orderProduct->price = $priceMultiplier > 0 ? $orderProduct->price / $priceMultiplier : 0;
                $orderProduct->save();
            });
        });
    }
}
