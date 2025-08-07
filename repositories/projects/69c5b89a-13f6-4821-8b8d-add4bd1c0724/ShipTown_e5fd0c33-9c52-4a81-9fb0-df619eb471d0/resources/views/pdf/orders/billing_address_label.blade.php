@extends('pdf.template')
@section('content')
    <div>{{ $billing_address ? $billing_address['company'] : '' }}</div>
    <div>{{ $billing_address ? $billing_address['first_name'] : '' }} {{ $billing_address ?  $billing_address['last_name'] : '' }}</div>
    <div>{{ $billing_address ? $billing_address['address1'] : '' }}</div>
    <div>{{ $billing_address ? $billing_address['address2'] : '' }}</div>
    <div>{{ $billing_address ? $billing_address['city'] : '' }} {{ $billing_address ?  $billing_address['postcode'] : '' }}</div>
    <div>{{ $billing_address ? $billing_address['state_name'] : '' }}</div>
    <div style="font-weight: bold">{{ $billing_address ? $billing_address['country_name'] : '' }}</div>
    <div style="font-size: xx-small">T: {{ $billing_address ? $billing_address['phone'] : '' }}</div>
    <br>
    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($order_number, 'C39', 1,15) }}" alt="barcode" />
    <div style="font-size: xx-small">Order #{{ $order_number }}</div>

    <style>
        @page {
            size: 101.6mm 76.2mm;
            margin: 3mm;
        }
    </style>
@endsection

