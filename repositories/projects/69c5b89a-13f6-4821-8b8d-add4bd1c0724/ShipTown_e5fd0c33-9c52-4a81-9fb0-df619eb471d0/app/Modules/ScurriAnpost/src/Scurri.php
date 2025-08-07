<?php

namespace App\Modules\ScurriAnpost\src;

use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Modules\ScurriAnpost\src\Api\Client;

class Scurri
{
    /**
     * @throws ShippingServiceException
     */
    public static function makeShippingLabel(Order $order): ShippingLabel
    {
        $consignment_id = Client::createSingleConsignment([
            'order_number' => $order->order_number,
            'recipient' => [
                'address' => [
                    'country' => $order->shippingAddress->country_code,
                    'postcode' => $order->shippingAddress->postcode,
                    'city' => $order->shippingAddress->city,
                    'address1' => $order->shippingAddress->address1,
                    'address2' => $order->shippingAddress->address2,
                    'state' => $order->shippingAddress->state_code,
                ],
                'contact_number' => $order->shippingAddress->phone,
                'email_address' => '',
                'company_name' => $order->shippingAddress->company,
                'name' => $order->shippingAddress->full_name,
            ],
            'packages' => [
                [
                    'items' => [
                        [
                            'sku' => 'n/a',
                            'quantity' => 1,
                            'name' => 'Shipment',
                            'value' => 1,
                        ],
                    ],
                    'length' => 50,
                    'height' => 50,
                    'width' => 50,
                    'reference' => '',
                ],
            ],
        ]);

        // in order to obtain shipping number we need to generate documents
        $documents = Client::getDocuments($consignment_id);

        // we need to refresh it in order to obtain shipping number or possible errors
        $consignment = Client::getConsignment($consignment_id);

        if ($documents->failed()) {
            throw new ShippingServiceException('AnPost: '.$consignment->json('current_status.rejection_reason'));
        }

        return new ShippingLabel([
            'order_id' => $order->getKey(),
            'carrier' => $consignment->json('carrier'),
            'service' => $consignment->json('service'),
            'shipping_number' => $consignment->json('consignment_number'),
            'tracking_url' => $consignment->json('tracking_url'),
            'content_type' => ShippingLabel::CONTENT_TYPE_PDF,
            'base64_pdf_labels' => $documents->json('labels'),
        ]);
    }
}
