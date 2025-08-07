@extends('layouts.app')

@section('title', __('Quick Connect'))

@section('content')
    <div class="col-md-3 mx-auto mt-5 text-center">
        <a href="quick-connect/magento" class="btn btn-block d-flex align-items-center justify-content-center mt-5">
            <img src="{{ asset('img/logos/magento.svg') }}" alt="Magento Logo" style="width: 145%">
        </a>

        <a href="quick-connect/shopify" class="btn btn-block d-flex align-items-center justify-content-center mt-5">
            <img src="{{ asset('img/logos/shopify.svg') }}" alt="Shopify Logo">
        </a>

        <a href="/" class="btn btn-primary btn-block mt-5">Skip</a>
    </div>
@endsection
