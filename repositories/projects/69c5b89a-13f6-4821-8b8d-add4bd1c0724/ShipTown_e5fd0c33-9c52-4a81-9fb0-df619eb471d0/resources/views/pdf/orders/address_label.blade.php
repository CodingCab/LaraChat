@extends('pdf.template')
@section('content')
    <div>{{ $shipping_address['company'] }}</div>
    <div>{{ $shipping_address['first_name'] }} {{ $shipping_address['last_name'] }}</div>
    <div>{{ $shipping_address['address1'] }}</div>
    <div>{{ $shipping_address['address2'] }}</div>
    <div>{{ $shipping_address['city'] }} {{ $shipping_address['postcode'] }}</div>
    <div>{{ $shipping_address['state_name'] }}</div>
    <div style="font-weight: bold">{{ $shipping_address['country_name'] }}</div>
    <div style="font-size: xx-small">T: {{ $shipping_address['phone'] }}</div>
    <br>
    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($order_number, 'C128', 1,15) }}" alt="barcode" />
    <div style="font-size: xx-small">Order #{{ $order_number }}</div>

    <style>
        @page {
            size: 101.6mm 76.2mm;
            margin: 3mm;
        }
    </style>
@endsection

