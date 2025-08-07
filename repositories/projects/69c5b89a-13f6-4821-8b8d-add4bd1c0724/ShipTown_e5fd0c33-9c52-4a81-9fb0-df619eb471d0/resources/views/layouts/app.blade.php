@extends('layouts.body')

@section('app-content')
    @auth
        @if (request()->input('hide_nav_bar', false) === false)
            <heartbeats></heartbeats>
            <navigation-bar></navigation-bar>
        @endif

        <subpages>
            @yield('content')
        </subpages>

        <vue-snotify></vue-snotify>
        <recent-inventory-movements-modal></recent-inventory-movements-modal>
        <product-details-modal></product-details-modal>
        <new-product-modal></new-product-modal>
        <find-product-modal></find-product-modal>
        <new-quantity-discount-modal></new-quantity-discount-modal>
        <module-data-collector-payments-new-payment-type-modal></module-data-collector-payments-new-payment-type-modal>
        <module-data-collector-discounts-new-discount-modal></module-data-collector-discounts-new-discount-modal>
        <shelf-location-command-modal></shelf-location-command-modal>
        <edit-shelf-label-modal></edit-shelf-label-modal>
        <create-mail-template-modal></create-mail-template-modal>
        <module-fakturowo-new-configuration-modal></module-fakturowo-new-configuration-modal>
        <inventory-reservations-modal></inventory-reservations-modal>
        <settings-modal></settings-modal>
    @endauth
@endsection
