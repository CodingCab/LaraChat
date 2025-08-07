<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\ExternalProduct;
use App\Modules\Api2cart\src\Models\Api2cartCallResponse;

class ProcessImportedProductsJob extends UniqueJob
{
    public function handle(): void
    {
        Api2cartCallResponse::query()->where([
                'type' => 'product.list.json',
                'processed_at' => null,
            ])
            ->orderBy('id')
            ->chunkById(10, function ($api2cartCallResponse) {
                $api2cartCallResponse->each(function (Api2cartCallResponse $call) {
                    $records = collect($call->response['product'])->map(function ($item) {
                        return [
                            'external_id' => $item['id'],
                            'external_sku' => $item['u_sku'],
                            'external_name' => $item['name'],
                            'external_price' => $item['price'],
                            'external_quantity' => $item['quantity'],
                            'raw_data' => json_encode($item),
                        ];
                    });

                    ExternalProduct::query()->upsert($records->toArray(), ['sku', 'name']);

                    $call->update(['processed_at' => now()]);

                    usleep(100000); // Sleep for 0.1 seconds
                });
            });
    }
}
