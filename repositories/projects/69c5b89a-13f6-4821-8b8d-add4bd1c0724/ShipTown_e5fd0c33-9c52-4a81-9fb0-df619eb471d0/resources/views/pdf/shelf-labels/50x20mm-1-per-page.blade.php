@extends('pdf.template')
@section('content')

    @foreach($labels as $label)
        @php
            $barcodeText = 'shelf:'.$label;
            $fontSize = strlen($label) > 4 ? '18px' : '35px';
        @endphp
        <div class="row label_box nowrap">
            <table>
                <tbody>
                    <tr>
                        <td class="qr" style="width: 15mm">
                            <img style="padding-top: 2mm; padding-left: 1mm; width: 13mm; height: 13mm" src="data:image/svg,{{ DNS2D::getBarcodeSVG($barcodeText, 'QRCODE') }}" alt="barcode" />
                        </td>
                        <td class="label" style="width: 30mm; text-align: center; vertical-align: middle; padding: 0.5mm;">
                            <h1 style="font-size: {{ $fontSize }}; word-wrap: anywhere; line-height: 90%">{{ $label }}</h1>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <style>
        @page {
            size: 50mm 20mm;
            margin: 1mm;
        }

        h1, img, table, tbody, tr, td {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            text-align: left;
            vertical-align: middle;
        }

        td {
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>

@endsection
