<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Api2cart\src\Models\Api2cartCallResponse;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Services\Api2cartService;

class ImportProductsJobs extends UniqueJob
{
    public function handle(): void
    {
        Api2cartConnection::query()
            ->where('enabled', true)
            ->each(function (Api2cartConnection $conn) {
                $this->fetchProducts($conn);
            });
    }
    public function fetchProducts(?Api2cartConnection $conn): void
    {
        $maxProductId = $this->getMaxID($conn);
        $sinceID = 0;

        do {
            $response = Api2cartService::getProductsList($conn, null, [
                'count' => 1000,
                'since_id' => $sinceID,
                'params' => 'force_all',
                'sort_by' => 'id',
                'sort_direction' => 'asc',
            ]);

            Api2cartCallResponse::query()->create([
                'type' => 'product.list.json',
                'url' => 'product.list.json',
                'response' => $response->getResult(),
            ]);

            if ($response->isNotSuccess()) {
                return;
            }

            if (empty($response->getResult()['product'])) {
                return;
            }

            $lastProduct = collect($response->getResult()['product'])->last();
            $sinceID = data_get($lastProduct, 'id');

            sleep(1);
        } while ($sinceID <= $maxProductId);
    }

    public function getMaxID($conn): int
    {
        $response = Api2cartService::getProductsList($conn, null, [
            'count' => 1,
            'params' => 'force_all',
            'sort_by' => 'id',
            'sort_direction' => 'desc',
        ]);

        return data_get($response->getResult(), 'product.0.id', 0);
    }
}
