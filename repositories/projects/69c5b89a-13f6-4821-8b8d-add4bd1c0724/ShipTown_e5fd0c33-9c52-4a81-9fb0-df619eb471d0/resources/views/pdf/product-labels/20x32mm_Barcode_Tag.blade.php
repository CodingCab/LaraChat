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
            <div class="box_size">{{ "10szt" }}</div>
            <div class="product_name">{{ $product['name'] }}</div>
{{--            <div class="product_price">{{ $users_warehouse_code ? $product['prices'][$users_warehouse_code]['price'] : $product['price'] }}</div>--}}
{{--            <div class="product_barcode"><img src="data:image/svg,{{ DNS1D::getBarcodeSVG($product['sku'], 'C39', 0.55, 14, 'black', false) }}" alt="barcode"/></div>--}}
            <div class="logo">korallo</div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <style>
        @page {
            size: 32mm 20mm;
            margin: 1mm;
        }

        .logo {
            position: absolute;
            bottom: 5px;
            font-size: 14pt;
            /*center*/
            left: 20px;
        }

        .product_sku {
            position: absolute;
            left: 5px;
            font-size: 10pt;
            font-weight: bold;
        }

        .box_size {
            position: absolute;
            right: 5px;
            font-size: 6pt;
        }

        .product_name {
            position: absolute;
            left: 5px;
            top: 20px;
            font-size: 5pt;
            word-wrap: anywhere;
        }

        .euroSymbol {
            margin-right: 10px;
            font-size: 6pt;
        }

        .product_price {
            position: absolute;
            font-size: 6pt;
            top: 30px;
            right: 5px;
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
