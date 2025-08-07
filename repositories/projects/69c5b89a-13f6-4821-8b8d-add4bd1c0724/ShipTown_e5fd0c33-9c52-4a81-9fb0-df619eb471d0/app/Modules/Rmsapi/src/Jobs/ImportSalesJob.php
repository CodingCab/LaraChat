<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Heartbeat;
use App\Modules\Rmsapi\src\Api\Client as RmsapiClient;
use App\Modules\Rmsapi\src\Api\RequestResponse;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportSalesJob extends UniqueJob
{
    private RmsapiConnection $rmsConnection;

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->rmsConnection->getKey()]);
    }

    public function __construct(int $rmsapiConnectionId)
    {
        $this->rmsConnection = RmsapiConnection::query()->find($rmsapiConnectionId);
    }

    public function handle(): bool
    {
        $per_page = 1000;

        do {
            try {
                $response = $this->importSalesRecords($per_page);

                if ($response->getResult()) {
                    $this->saveImportedRecords($response->getResult());
                }

                $recordsLeftToDownload = $response->asArray()['total'] - count($response->getResult());

                Log::info('Job processing', [
                    'job' => self::class,
                    'warehouse_code' => $this->rmsConnection->location_id,
                    'rmsapi_connection_id' => $this->rmsConnection->getKey(),
                    'records_downloaded' => count($response->getResult()),
                    'records_to_download' => $response->asArray()['total'] - count($response->getResult()),
                ]);

                Heartbeat::query()->updateOrCreate([
                    'code' => 'modules_rmsapi_successful_sales_fetch_warehouseId_'.$this->rmsConnection->location_id,
                ], [
                    'level' => Heartbeat::LEVEL_ERROR,
                    'error_message' => 'RMSAPI Sales not synced for last hour WarehouseID: '.$this->rmsConnection->location_id,
                    'expires_at' => now()->addHour(),
                ]);
            } catch (GuzzleException $e) {
                Log::warning('RMSAPI ImportSalesJob Failed sales fetch', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);

                return false;
            }

            sleep(1);
        } while ($recordsLeftToDownload > 0);

        return true;
    }

    /**
     * @throws GuzzleException
     */
    protected function importSalesRecords(int $per_page): RequestResponse
    {
        $this->rmsConnection->refresh();

        $params = [
            'per_page' => $per_page,
            'order_by' => 'db_change_stamp:asc',
            'min:db_change_stamp' => $this->rmsConnection->sales_last_timestamp,
        ];

        return RmsapiClient::GET($this->rmsConnection, 'api/transaction-entries', $params);
    }

    public function saveImportedRecords(array $salesRecords)
    {
        $time = now()->toDateTimeString();

        $recordsCollection = collect($salesRecords);

        $data = $recordsCollection->map(function ($saleRecord) use ($time) {
            $isImportedFromPM = Str::startsWith($saleRecord['comment'], ['PM_OrderProductShipment_']);

            return [
                'connection_id' => $this->rmsConnection->getKey(),
                'warehouse_id' => $this->rmsConnection->warehouse_id,
                'uuid' => $saleRecord['uuid'],
                'type' => $saleRecord['type'],
                'sku' => $saleRecord['sku'],
                'unit_cost' => $saleRecord['cost'],
                'total_sales_tax' => $saleRecord['total_sales_tax'],
                'price' => $saleRecord['price'],
                'quantity' => $saleRecord['quantity'],
                'transaction_time' => $saleRecord['transaction_time'],
                'transaction_number' => $saleRecord['transaction_number'],
                'transaction_entry_id' => $saleRecord['transaction_entry_id'],
                'comment' => $saleRecord['comment'],
                'raw_import' => json_encode($saleRecord),
                'reserved_at' => $isImportedFromPM ? $time : null,
                'processed_at' => $isImportedFromPM ? $time : null,
                'created_at' => $time,
                'updated_at' => $time,
            ];
        });

        // for performance reasons we will use insert instead of create
        RmsapiSaleImport::query()->insert($data->toArray());

        $this->rmsConnection->update(['sales_last_timestamp' => $recordsCollection->last()['db_change_stamp']]);
    }
}
