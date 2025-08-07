@extends('pdf.template')
@section('content')

    @foreach($labels as $label)
        @php
            $fontSize = strlen($label) > 3 ? '70px' : '140px';
        @endphp
        <div style="overflow: hidden;">
            <h1 style="text-align: center; font-size: {{$fontSize}}; margin-top: 50px; word-wrap: anywhere; line-height: 90%;">{{ $label }}</h1>
        </div>
        <img style="width: 150px; height: 150px; margin-top:90px; margin-left: 105px;" src="data:image/svg,{{ DNS2D::getBarcodeSVG('shelf:'.$label, 'QRCODE') }}" alt="barcode" />
        <p style="text-align: center; font-size: 18px;  margin-top: 10px; word-wrap: anywhere;">shelf:{{ $label }}</p>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <style>
        @page {
            size: 101.6mm 152.4mm;
            margin: 3mm;
        }

        .page-break {
            page-break-after: always;
        }

        h1, p {
            margin: 0;
            padding: 0;
        }
    </style>

@endsection
