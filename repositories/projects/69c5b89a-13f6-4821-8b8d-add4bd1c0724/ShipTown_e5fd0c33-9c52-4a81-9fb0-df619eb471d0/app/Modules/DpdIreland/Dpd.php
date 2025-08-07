<?php

namespace App\Modules\DpdIreland;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\DpdIreland\src\Client;
use App\Modules\DpdIreland\src\Consignment;
use App\Modules\DpdIreland\src\Exceptions\AuthorizationException;
use App\Modules\DpdIreland\src\Exceptions\PreAdviceRequestException;
use App\Modules\DpdIreland\src\Responses\PreAdvice;
use App\User;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class Dpd.
 */
class Dpd
{
    const NOT_AUTH = 'NOT_AUTH';

    /**
     * @throws src\Exceptions\ConsignmentValidationException
     * @throws Exception
     * @throws AuthorizationException
     * @throws GuzzleException
     * @throws PreAdviceRequestException|GuzzleException
     */
    public static function shipOrder(Order $order, ?User $user = null): PreAdvice
    {
        $consignment = self::getConsignmentData($order);

        $preAdvice = self::requestPreAdvice($consignment);

        logger('DPD PreAdvice generated', [
            'consignment' => $consignment->toArray(),
            'preAdvice' => $preAdvice->toArray(),
        ]);

        return $preAdvice;
    }

    /**
     * @throws PreAdviceRequestException|GuzzleException|AuthorizationException
     */
    public static function requestPreAdvice(Consignment $consignment): PreAdvice
    {
        $response = Client::postXml($consignment->toXml());

        $preAdvice = new PreAdvice($response);

        if ($preAdvice->isSuccess()) {
            return $preAdvice;
        }

        Log::error('DPD PreAdvice request failed', [
            'xml_received' => $preAdvice->toString(),
            'xml_sent' => $consignment->toString(),
        ]);

        if ($preAdvice->getPreAdviceErrorCode() === self::NOT_AUTH) {
            Client::clearCache();
            throw new AuthorizationException('DPD: '.$preAdvice->getPreAdviceErrorDetails());
        } else {
            throw new PreAdviceRequestException('DPD: '
                .data_get($preAdvice->toArray(), 'RecordErrorDetails')
                .data_get($preAdvice->toArray(), 'PreAdviceErrorDetails.0')
                .data_get($preAdvice->toArray(), 'Consignment.RecordErrorDetails'));
        }
    }

    /**
     * @throws src\Exceptions\ConsignmentValidationException
     */
    private static function getConsignmentData(Order $order): Consignment
    {
        $payload = [
            'RecordID' => $order->order_number,
            'ConsignmentReference' => $order->order_number,
            'ShipmentId' => $order->order_number,
            'ReceiverType' => 'Private',
            'ReceiverEORI' => 'n/a',
            'SenderEORI' => 'n/a',
            'SPRNRegNo' => 'n/a',
            'ShipmentType' => 'Merchandise',
            'ShipmentInvoiceCurrency' => 'EUR',
            'ShipmentIncoterms' => 'DAP',
            'ShipmentParcelsWeight' => 10,
            'InvoiceNumber' => $order->order_number,
            'FreightCost' => $order->total_shipping ?? 0.01,
            'FreightCurrency' => 'EUR',
            'DeliveryAddress' => [
                'Contact' => $order->shippingAddress->full_name,
                'ContactTelephone' => $order->shippingAddress->phone,
                'ContactEmail' => $order->shippingAddress->email,
                'AddressLine1' => $order->shippingAddress->address1,
                'AddressLine2' => $order->shippingAddress->address2,
                'AddressLine3' => $order->shippingAddress->city,
                'AddressLine4' => $order->shippingAddress->state_name ?: $order->shippingAddress->city,
                'PostCode' => $order->shippingAddress->postcode,
                'CountryCode' => $order->shippingAddress->country_code,
            ],
            'References' => [
                'Reference' => [
                    'ReferenceName' => 'Order',
                    'ReferenceValue' => $order->order_number,
                    'ParcelNumber' => 1,
                ],
            ],

        ];

        if (in_array($order->shippingAddress->country_code, ['CHE', 'RUS'])) {
            $payload['CustomsLines'] = [
                'CustomsLine' => $order->orderProducts->map(function (OrderProduct $orderProduct) {
                    return [
                        'CommodityCode' => $orderProduct->product->commodity_code,
                        'CountryOfOrigin' => '372', // 372 - Ireland
                        'Description' => $orderProduct->name_ordered,
                        'Quantity' => $orderProduct->quantity_ordered,
                        'Measurement' => 'unit',
                        'TotalLineValue' => $orderProduct->quantity_ordered * $orderProduct->price,
                        'TaricAdd1' => '',
                        'TaricAdd2' => '',
                        'ExtraLicensingRequired' => 0,
                    ];
                })->toArray(),
            ];
        }

        return new Consignment($payload);
    }
}
