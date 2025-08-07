<?php

namespace App\Modules\Fakturowo\src\Services;

use App\Models\Order;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Services\CountryCodeConverterService;

class FakturowoService
{
    public static function prepareInvoiceData(Order $order, $products, bool $addDeliveryCharge): array
    {
        $invoiceData = [
            'dokument_email' => $order->billingAddress->email,
            'dokument_zamowienie' => $order->order_number,
            'dokument_pokaz_zamowienie' => 1,
            'dokument_zaplata' => 31,
            'dokument_pokaz_zaplacono' => 1,
            'dokument_pokaz_zaplata' => 1,
            'nabywca_miasto' => $order->billingAddress->city,
            'nabywca_kod' => $order->billingAddress->postcode,
            'nabywca_ulica' => $order->billingAddress->address1,
            'nabywca_kraj' => CountryCodeConverterService::alpha3ToAlpha2(
                $order->billingAddress->country_code
            ) ?? $order->billingAddress->country_name,
        ];

        if ($order->billingAddress->tax_id && $order->billingAddress->company) {
            $invoiceData['nabywca_osoba'] = 0;
            $invoiceData['nabywca_nip'] = $order->billingAddress->tax_id;
            $invoiceData['nabywca_nazwa'] = $order->billingAddress->company;
        } else {
            $invoiceData['nabywca_osoba'] = 1;
            $invoiceData['nabywca_imie'] = $order->billingAddress->first_name;
            $invoiceData['nabywca_nazwisko'] = $order->billingAddress->last_name;
        }

        $invoiceTotal = 0;

        $products->each(function (InvoiceOrderProduct $record, $index) use (&$invoiceData, &$invoiceTotal) {
            $lp = $index + 1;
            $op = $record->orderProduct;

            $totalSoldPrice = ($op->total_sold_price / $op->quantity_ordered) * $record->quantity_invoiced;
            $totalSoldPrice = round($totalSoldPrice, 2);

            $invoiceData["produkt_nazwa_$lp"] = "$op->name_ordered ($op->sku_ordered)";
            $invoiceData["produkt_ilosc_$lp"] = $record->quantity_invoiced;
            $invoiceData["produkt_jm_$lp"] = 2;
            $invoiceData["produkt_stawka_vat_$lp"] = $op->tax_rate;
            $invoiceData["produkt_wartosc_brutto_$lp"] = $totalSoldPrice;

            $invoiceTotal += $totalSoldPrice;
        });

        if ($addDeliveryCharge) {
            $invoiceData = array_merge($invoiceData, [
                'produkt_nazwa' => $order->shipping_method_name,
                'produkt_ilosc' => 1,
                'produkt_jm' => 2,
                'produkt_stawka_vat' => '23',
                'produkt_wartosc_brutto' => round($order->total_shipping, 2),
            ]);

            $invoiceTotal += round($order->total_shipping, 2);
        }

        $invoiceData['dokument_zaplacono'] = round($invoiceTotal, 2);

        return $invoiceData;
    }
}
