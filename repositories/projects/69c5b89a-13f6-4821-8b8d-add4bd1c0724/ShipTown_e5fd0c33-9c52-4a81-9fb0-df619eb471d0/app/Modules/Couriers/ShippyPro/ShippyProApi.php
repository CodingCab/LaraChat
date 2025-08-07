<?php

namespace App\Modules\Couriers\ShippyPro;

use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\ShippingLabel;
use App\Services\CountryCodeConverterService;
use App\User;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ShippyProApi
{
    public static function client(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(env('SHIPPY_PRO_API_KEY', '') . ':'),
        ]);
    }

    public static function checkApiConnection(): bool
    {
        $response = self::client()->post('https://www.shippypro.com/api', [
            'Method' => 'Ping',
            'Params' => (object)[]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 && isset($data['Result']) && $data['Result']) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public static function createShipment($payload)
    {
        $response = self::client()->post('https://www.shippypro.com/api', [
            'Method' => 'Ship',
            'Params' => (object)$payload['Params'],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 && isset($data['Status']) && $data['Status'] === '1') {
            return $data;
        } else {
            if (isset($data['ErrorMessage'])) {
                throw new Exception('Error: ' . $data['ErrorMessage']);
            } else {
                throw new Exception('Unknown error occurred');
            }
        }
    }

    public static function prepareDefaultPayload(Order $order, OrderAddress $collectionAddress = null): array
    {
        $shippingAddress = $order->shippingAddress;

        return [
            'Params' => [
                'to_address' => [
                    'name' => $shippingAddress->full_name ?? '',
                    'company' => $shippingAddress->company ?? '',
                    'street1' => $shippingAddress->address1 ?? '',
                    'street2' => $shippingAddress->address2 ?? '',
                    'city' => $shippingAddress->city ?? '',
                    'state' => $shippingAddress->state_code ?? '',
                    'zip' => $shippingAddress->postcode ?? '',
                    'country' => CountryCodeConverterService::alpha3ToAlpha2($shippingAddress->country_code) ?? '',
                    'phone' => $shippingAddress->phone ?? '',
                    'email' => $shippingAddress->email ?? '',
                ],
                'from_address' => [
                    'name' => $collectionAddress->full_name ?? '',
                    'company' => $collectionAddress->company ?? '',
                    'street1' => $collectionAddress->address1 ?? '',
                    'street2' => $collectionAddress->address2 ?? '',
                    'city' => $collectionAddress->city ?? '',
                    'state' => $collectionAddress->state_code ?? '',
                    'zip' => $collectionAddress->postcode ?? '',
                    'country' => CountryCodeConverterService::alpha3ToAlpha2($collectionAddress->country_code) ?? '',
                    'phone' => $collectionAddress->phone ?? '',
                    'email' => $collectionAddress->email ?? '',
                ],
                'TransactionID' => $order->order_number,
                'ContentDescription' => 'miscellaneous',
                'OrderID' => "",
                'RateID' => "",
                'Async' => false,
            ],
        ];
    }

    public static function convertToDpdPolandFormat(Order $order, OrderAddress $collectionAddress = null, array $params = null): array
    {
        $finalParams = self::prepareDefaultPayload($order, $collectionAddress);

        if ($params) {
            $finalParams = array_merge_recursive($finalParams, $params);
        }

        return $finalParams;
    }

    /**
     * @throws ShippingServiceException
     */
    public static function convertToInPostPolandFormat(Order $order, OrderAddress $collectionAddress = null, array $params = null): array
    {
        $shippingAddress = $order->shippingAddress;

        if (!$shippingAddress->locker_box_code) {
            throw new ShippingServiceException('InPost Poland (Locker Standard): Locker box code is required.');
        }

        $finalParams = self::prepareDefaultPayload($order, $collectionAddress);

        $finalParams = array_merge_recursive($finalParams, [
            'Params' => [
                'CarrierOptions' => [
                    [
                        'name' => 'inpostplpoint',
                        'value' => '1',
                    ],
                    [
                        'name' => 'inpostplpointid',
                        'value' => $shippingAddress->locker_box_code,
                    ],
                ],
            ],
        ]);

        if ($params) {
            $finalParams = array_merge_recursive($finalParams, $params);
        }

        return $finalParams;
    }

    public static function convertToGenericFormat(Order $order, OrderAddress $collectionAddress = null, array $params = null): array
    {
        $finalParams = self::prepareDefaultPayload($order, $collectionAddress);

        if ($params) {
            $finalParams = array_merge_recursive($finalParams, $params);
        }

        return $finalParams;
    }


    /**
     * @throws Exception
     */
    public static function createShippingLabel(Order $order, $params = null, $courier = 'DPD'): Collection
    {
        $shipments = collect();

        /** @var User $user */
        $user = auth()->user();
        $collectionAddress = $user->warehouse->address;

        if ($courier === 'DPD') {
            $payload = ShippyProApi::convertToDpdPolandFormat($order, $collectionAddress, $params);
        } elseif ($courier === 'InPost') {
            $payload = ShippyProApi::convertToInPostPolandFormat($order, $collectionAddress, $params);
        } elseif ($courier === 'Generic') {
            $payload = ShippyProApi::convertToGenericFormat($order, $collectionAddress, $params);
        } else {
            throw new ShippingServiceException('Invalid courier specified.');
        }

        $shipment = ShippyProApi::createShipment($payload);

        if (is_array($shipment['PDF']) && !empty($shipment['PDF']) && isset($shipment['NewOrderID'])) {
            foreach ($shipment['PDF'] as $pdf) {
                $shippingLabel = new ShippingLabel;
                $shippingLabel->order_id = $order->id;
                $shippingLabel->user_id = $user->id;
                $shippingLabel->carrier = data_get($payload, 'Params.CarrierName', '');
                $shippingLabel->service = data_get($payload, 'Params.CarrierService', '');
                $shippingLabel->shipping_number = $shipment['TrackingNumber'];
                $shippingLabel->tracking_url = 'https://inpost.pl/sledzenie-przesylek?number=' . $shipment['TrackingNumber'];
                $shippingLabel->content_type = ShippingLabel::CONTENT_TYPE_PDF;
                $shippingLabel->base64_pdf_labels = $pdf;
                $shippingLabel->save();

                $shipments->add($shippingLabel);
            }
        }

        return $shipments;
    }

    public static function getCarrierName(string $carrierName): string
    {
        return $carrierName;
    }
}
