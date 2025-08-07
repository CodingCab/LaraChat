@extends('pdf.template')
@section('content')
<div>
    <span class="bold">Transaction Receipt</span>
</div>

<style>
    @page {
        margin: 0;
    }

    *,
    *::after,
    *::before {
        box-sizing: border-box;
    }

    /* .body {
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: none;
        padding: 5mm;
        width: 80mm;
        height: 100%;
        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        overflow: hidden;
        word-break: break-word;
        page-break-inside: avoid;
    } */
</style>
@endsection