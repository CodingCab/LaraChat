<?php

namespace App\Modules\DpdIreland\src;

use App\Modules\DpdIreland\src\Exceptions\ConsignmentValidationException;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Class Shipment.
 */
class Consignment
{
    const SERVICE_OPTION_COD = 1;

    const SERVICE_OPTION_PREMIUM_DOCUMENT_SERVICE = 2;

    const SERVICE_OPTION_RETURN_RECEIPT = 3;

    const SERVICE_OPTION_SWAPIT = 4;

    const SERVICE_OPTION_NORMAL = 5;

    const SERVICE_TYPE_OVERNIGHT = 1;

    const SERVICE_TYPE_SATURDAY = 2;

    const SERVICE_TYPE_TIMED = 3;

    const SERVICE_TYPE_SPECIAL = 4;

    const SERVICE_TYPE_CANCELLED_ON_ARRIVAL = 5;

    const SERVICE_TYPE_2_DAY_SERVICE = 6;

    public array $rules = [
        'RecordID' => 'sometimes',
        'ShipmentId' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'ReceiverType' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'in:Private,Business'],
        'ReceiverEORI' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'SenderEORI' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'SPRNRegNo' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'ShipmentType' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'in:Documents,Merchandise'],
        'ShipmentInvoiceCurrency' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'same:FreightCurrency'],
        'ShipmentIncoterms' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'in:DAP,EDAP'],
        'ShipmentParcelsWeight' => ['numeric', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'InvoiceNumber' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL'],
        'FreightCost' => ['numeric', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'required_with:FreightCurrency'],
        'FreightCurrency' => ['string', 'required_unless:DeliveryAddress.CountryCode,IE,IRL', 'required_with:FreightCost', 'same:ShipmentInvoiceCurrency'],

        'DeliveryAddress.Contact' => 'required',
        'DeliveryAddress.ContactTelephone' => 'required',
        'DeliveryAddress.ContactEmail' => 'sometimes',
        'DeliveryAddress.BusinessName' => 'sometimes',
        'DeliveryAddress.AddressLine1' => 'sometimes',
        'DeliveryAddress.AddressLine2' => 'sometimes',
        'DeliveryAddress.AddressLine3' => 'required',
        'DeliveryAddress.AddressLine4' => 'required',
        'DeliveryAddress.PostCode' => 'sometimes',
        'DeliveryAddress.CountryCode' => 'required|in:IE,IRL,UK,GB,CHE,DEU,DE,AUT,BEL,BGR,HRV,CYP,CZE,DNK,EST,FIN,FRA,DEU,GRC,HUN,IRL,ITA,LVA,LTU,LUX,MLT,NLD,POL,PRT,ROU,SVK,SVN,ESP,SWE',

        //        'CollectionAddress.Contact'           => 'required',
        //        'CollectionAddress.ContactTelephone'  => 'required',
        //        'CollectionAddress.ContactEmail'      => 'sometimes',
        //        'CollectionAddress.BusinessName'      => 'sometimes',
        //        'CollectionAddress.AddressLine1'      => 'sometimes',
        //        'CollectionAddress.AddressLine2'      => 'sometimes',
        //        'CollectionAddress.AddressLine3'      => 'required',
        //        'CollectionAddress.AddressLine4'      => 'required',
        //        'CollectionAddress.PostCode'          => 'sometimes',
        //        'CollectionAddress.CountryCode'       => 'required|in:IE,IRL,UK,GB,CHE',
        //
        //        'ReturnAddress.Contact'           => 'required',
        //        'ReturnAddress.ContactTelephone'  => 'required',
        //        'ReturnAddress.ContactEmail'      => 'sometimes',
        //        'ReturnAddress.BusinessName'      => 'sometimes',
        //        'ReturnAddress.AddressLine1'      => 'sometimes',
        //        'ReturnAddress.AddressLine2'      => 'sometimes',
        //        'ReturnAddress.AddressLine3'      => 'required',
        //        'ReturnAddress.AddressLine4'      => 'required',
        //        'ReturnAddress.PostCode'          => 'sometimes',
        //        'ReturnAddress.CountryCode'       => 'required|in:IE,IRL,UK,GB,CHE',
        //
        //        'BillingAddress.Contact'           => 'required',
        //        'BillingAddress.ContactTelephone'  => 'required',
        //        'BillingAddress.ContactEmail'      => 'sometimes',
        //        'BillingAddress.BusinessName'      => 'sometimes',
        //        'BillingAddress.AddressLine1'      => 'sometimes',
        //        'BillingAddress.AddressLine2'      => 'sometimes',
        //        'BillingAddress.AddressLine3'      => 'required',
        //        'BillingAddress.AddressLine4'      => 'required',
        //        'BillingAddress.PostCode'          => 'sometimes',
        //        'BillingAddress.CountryCode'       => 'required|in:IE,IRL,UK,GB,CHE',

        'CustomsLines' => ['array', 'required_unless:DeliveryAddress.CountryCode,IE,IRL,DEU,DE,AUT,BEL,BGR,HRV,CYP,CZE,DNK,EST,FIN,FRA,DEU,GRC,HUN,IRL,ITA,LVA,LTU,LUX,MLT,NLD,POL,PRT,ROU,SVK,SVN,ESP,SWE'],

        //            'CustomsLine' => [
        //                'CommodityCode'             => '6109100010',
        //                'CountryOfOrigin'           => '372',
        //                'Description'               => 'Red pencils',
        //                'Quantity'                  => '1',
        //                'Measurement'               => '1U38',
        //                'TotalLineValue'            => 123.45,
        //                'TaricAdd1'                 => 'tet',
        //                'TaricAdd2'                 => 'test',
        //                'ExtraLicensingRequired'    => 0,
        //                'Box44Lines'                => [
        //                    'Box44Line' => [
        //                        'Box44Code' => 'Y900',
        //                        'Box44Value' => 231,
        //                    ]
        //                ]
        //            ]
        //        ],
    ];

    private array $templateArray = [
        'RecordID' => 1,
        'CustomerAccount' => '',
        'TotalParcels' => 1,
        'Relabel' => 0,
        'ServiceOption' => self::SERVICE_OPTION_NORMAL,
        'ServiceType' => self::SERVICE_TYPE_OVERNIGHT,
        'ConsignmentReference' => '',
        'ShipmentId' => '',
        'ReceiverType' => '',
        'ReceiverEORI' => '',
        'SenderEORI' => '',
        'SPRNRegNo' => '',
        'ShipmentType' => '',
        'ShipmentInvoiceCurrency' => 'EUR',
        'ShipmentIncoterms' => '',
        'ShipmentParcelsWeight' => 0,
        'InvoiceNumber' => '',
        'FreightCost' => 0,
        'FreightCurrency' => 'EUR',
        'DeliveryAddress' => [
            'Contact' => '',
            'ContactTelephone' => '',
            'ContactEmail' => '',
            'BusinessName' => '',
            'AddressLine1' => '',
            'AddressLine2' => '',
            'AddressLine3' => '',
            'AddressLine4' => '',
            'PostCode' => '',
            'CountryCode' => '',
        ],
        'CollectionAddress' => [
            'Contact' => '',
            'ContactTelephone' => '',
            'ContactEmail' => '',
            'BusinessName' => '',
            'AddressLine1' => '',
            'AddressLine2' => '',
            'AddressLine3' => '',
            'AddressLine4' => '',
            'PostCode' => '',
            'CountryCode' => '',
        ],
        'ReturnAddress' => [
            'Contact' => '',
            'ContactTelephone' => '',
            'ContactEmail' => '',
            'BusinessName' => '',
            'AddressLine1' => '',
            'AddressLine2' => '',
            'AddressLine3' => '',
            'AddressLine4' => '',
            'PostCode' => '',
            'CountryCode' => '',
        ],
        'BillingAddress' => [
            'Contact' => '',
            'ContactTelephone' => '',
            'ContactEmail' => '',
            'BusinessName' => '',
            'AddressLine1' => '',
            'AddressLine2' => '',
            'AddressLine3' => '',
            'AddressLine4' => '',
            'PostCode' => '',
            'CountryCode' => '',
        ],
        'References' => [
            'Reference' => [
                'ReferenceName' => '',
                'ReferenceValue' => '',
                'ParcelNumber' => 1,
            ],
        ],
        'CustomsLines' => [
            //            'CustomsLine' => [
            //                'CommodityCode'             => '',
            //                'CountryOfOrigin'           => '',
            //                'Description'               => '',
            //                'Quantity'                  => '',
            //                'Measurement'               => '',
            //                'TotalLineValue'            => 0,
            //                'TaricAdd1'                 => '',
            //                'TaricAdd2'                 => '',
            //                'ExtraLicensingRequired'    => 0,
            //                'Box44Lines'                => [
            //                    'Box44Line' => [
            //                        'Box44Code' => '',
            //                        'Box44Value' => 0,
            //                    ]
            //                ]
            //            ]
        ],
    ];

    private Collection $payload;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var DpdIreland
     */
    private $config;

    /**
     * Shipment constructor.
     *
     *
     * @throws ConsignmentValidationException
     * @throws ModelNotFoundException
     */
    public function __construct(array $payload)
    {
        $this->payload = collect($payload);

        $this->validator = Validator::make($payload, $this->rules);

        if ($this->validator->fails()) {
            Log::warning('Consignment validation fails', $payload);
            throw new ConsignmentValidationException($this->validator->errors());
        }

        $this->config = DpdIreland::firstOrFail();
    }

    public function toXml(): string
    {
        $data = [
            'Consignment' => $this->toArray(),
        ];

        return ArrayToXml::convert(
            $data,
            'PreAdvice',
            true,
            'iso-8859-1',
        );
    }

    public function toString(): string
    {
        $data = $this->toXml();

        return str_replace(PHP_EOL, '', $data);
    }

    public function toArray(): array
    {
        $fixedValues = collect([
            'CustomerAccount' => $this->config->user,
            'DeliveryAddress' => (new DpdAddress($this->payload['DeliveryAddress']))->toArray(),
            'BillingAddress' => (new DpdAddress($this->payload['DeliveryAddress']))->toArray(),
            'CollectionAddress' => $this->getCollectionAddress(),
            'ReturnAddress' => $this->getCollectionAddress(),
        ]);

        $template = collect($this->templateArray);

        return $template->merge($this->payload)
            ->merge($fixedValues)
            ->only($template->keys())
            ->toArray();
    }

    private function getCollectionAddress(): array
    {
        $CollectionAddress = data_get($this->payload, 'CollectionAddress', $this->config->getCollectionAddress());

        $address = new DpdAddress($CollectionAddress);

        return $address->toArray();
    }
}
