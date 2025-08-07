@extends('pdf.template')
@section('content')
    @php
        $users_warehouse_code = auth()->user()->warehouse_code;
        $products = collect($product_sku)->map(function($sku) {
            return \App\Models\Product::whereSku($sku)->with(['prices'])->first()->toArray();
        })->toArray();

    @endphp

    @if(empty($products))
        <div style="width: 100%; text-align: center; margin-top: 10mm;">
            <div class="product_name" style="height: 10mm; text-align: center;">Enter Product SKU</div>
        </div>
    @endif

    @foreach($products as $product)
        <div style="width: 100%; text-align: left">
            <div class="product_sku">{{ $product['sku'] }}</div>
{{--            <div class="product_name">{{ $product['name'] }}</div>--}}
            <div class="product_barcode"><img src="data:image/svg,{{ DNS1D::getBarcodeSVG($product['sku'], 'C39', 0.55, 24, 'black', false) }}" alt="barcode"/></div>
            <div class="euroSymbol">PLN</div>
            <div class="product_price">{{ $users_warehouse_code ? number_format(round($product['prices'][$users_warehouse_code]['price'] / 1.23, 2), 2) : $product['price'] }}</div>
            <div class="product_price">{{ number_format($users_warehouse_code ? $product['prices'][$users_warehouse_code]['price'] : $product['price'], 2) }}</div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <style>
        @page {
            size: 80mm 20mm;
            margin: 1mm;
        }

        .product_name {
            font-size: 12pt;
            margin-top: 3px;
            margin-left: 5px;
            margin-right: 5px;
            text-align: left;
            word-wrap: anywhere;
        }

        .euroSymbol {
            position: absolute;
            transform: rotate(-90deg);
            right: 80px;
            bottom: 14px;
            font-size: 24pt;
            /*text-align: right;*/
            font-family: sans-serif;
            /*font-weight: bold;*/
            word-wrap: anywhere;
        }

        .product_price {
            font-size: 20pt;
            margin-right: 5px;
            text-align: right;
            font-family: sans-serif;
            /*font-weight: bold;*/
            word-wrap: anywhere;
        }

        .product_sku {
            position: absolute;
            left: 5px;
            top: 0px;
            font-size: 18pt;
        }

        .product_barcode {
            position: absolute;
            left: 5px;
            bottom: 5px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>

@endsection
