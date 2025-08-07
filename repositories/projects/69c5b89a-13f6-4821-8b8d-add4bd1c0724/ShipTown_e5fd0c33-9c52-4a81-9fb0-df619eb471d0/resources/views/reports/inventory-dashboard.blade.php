@extends('layouts.app')

@section('title',__('Inventory Dashboard'))

@section('content')
    <div class="container dashboard-widgets">
        <div class="row">
            <div class="col">
                {{--            <div class="widget-tools-container">--}}
                {{--                <a class="btn btn-primary btn-sm mt-2 fa-arrow-alt-circle-down"  href="{{ request()->fullUrlWithQuery(['filename' =>  __($report_name).'.csv']) }}">{{ __('Download') }}</a>--}}
                {{--            </div>--}}

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <div class="row col d-block font-weight-bold pt-2 pb-2 text-uppercase small text-secondary align-content-center text-center">
                                    {{ t('Inventory Dashboard') }}
                                </div>
                                @foreach($reports as $report)
                                    @if(data_get($report, 'warehouse_code') === auth()->user()->warehouse_code)
                                        <table class="table table-borderless">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href='{{ url()->route('tools.restocking', [
                                                            'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                            'filter[quantity_required_greater_than]' => 0,
                                                            'filter[warehouse_has_stock]' => true,
                                                            'sort' => '-quantity_required,-quantity_incoming,-fc_quantity_available',
                                                            'per_page' => '999',
                                                        ]) }}'>
                                                        {{ __('Products Required') }}
                                                    </a>
                                                </td>
                                                <td class="text-right">{{ data_get($report, 'wh_products_required') === 0 ? '-' : data_get($report, 'wh_products_required')}}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href='{{ url()->route('tools.restocking', [
                                                            'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                            'filter[quantity_incoming_greater_than]' => 0,
                                                            'filter[warehouse_has_stock]' => true,
                                                            'sort' => '-quantity_incoming,-quantity_required,-fc_quantity_available',
                                                            'per_page' => '999',
                                                        ]) }}'>
                                                        {{ __('Products Incoming') }}
                                                    </a>
                                                </td>
                                                <td class="text-right">{{ data_get($report, 'wh_products_incoming') === 0 ? '-' : data_get($report, 'wh_products_incoming')}}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href='{{ url()->route('tools.restocking', [
                                                            'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                            'filter[restock_level]' => 0,
                                                            'filter[warehouse_has_stock]' => true,
                                                            'sort' => 'restock_level,-quantity_incoming,quantity_available,-fc_quantity_available',
                                                            'per_page' => '999',
                                                        ]) }}'>
                                                        {{ __('Missing Restock Levels') }}
                                                    </a>
                                                </td>
                                                <td class="text-right">{{ data_get($report, 'missing_restock_levels') === 0 ? '-' : data_get($report, 'missing_restock_levels')}}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href='{{ url()->route('tools.restocking', [
                                                            'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                            'filter[quantity_available_lower_than]' => 1,
                                                            'filter[warehouse_has_stock]' => true,
                                                            'sort' => 'quantity_available,-quantity_incoming,-fc_quantity_available',
                                                            'per_page' => '999',
                                                        ]) }}'>
                                                        {{ __('Out Of Stock') }}
                                                    </a>
                                                </td>
                                                <td class="text-right">{{ data_get($report, 'wh_products_out_of_stock') === 0 ? '-' : data_get($report, 'wh_products_out_of_stock')}}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href='{{ url()->route('tools.restocking', [
                                                            'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                            'filter[warehouse_has_stock]' => true,
                                                            'sort' => '-quantity_required,-quantity_incoming,quantity_available,-fc_quantity_available,',
                                                            'per_page' => '999',
                                                        ]) }}'>
                                                        {{ __('Restockable') }}
                                                    </a>
                                                </td>
                                                <td class="text-right">{{ data_get($report, '`wh_products_available`') === 0 ? '-' : data_get($report, 'wh_products_available')}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                @endforeach

                                <div class="row">
                                    <div class="col">
                                        @asyncWidget('ReportWidget', [
                                            'report' => 'App\Modules\StocktakeSuggestions\src\Reports\StocktakeSuggestionsTotalsReport',
                                            'view' => 'reports.StocktakeSuggestionsReport',
                                            'sort' => 'warehouse_code',
                                            'per_page' => 999,
                                        ])
                                    </div>
                                </div>

                                <table class="table table-borderless">
                                    <thead>
                                    <tr>
                                        <th scope="col">Products Required</th>
                                        <th scope="col" class="text-right"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>
                                                <a href='{{ url()->route('reports.restocking.index', [
                                                        'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                        'filter[quantity_required_greater_than]' => 0,
                                                        'filter[warehouse_has_stock]' => true,
                                                        'sort' => '-quantity_required,-quantity_incoming,-fc_quantity_available',
                                                        'per_page' => '999',
                                                    ]) }}'>
                                                    {{ data_get($report, 'warehouse_code') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ data_get($report, 'wh_products_required') === 0 ? '-' : data_get($report, 'wh_products_required')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <table class="table table-borderless">
                                    <thead>
                                    <tr>
                                        <th scope="col">Products Incoming</th>
                                        <th scope="col" class="text-right"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>
                                                <a href='{{ url()->route('reports.restocking.index', [
                                                        'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                        'filter[quantity_incoming_greater_than]' => 0,
                                                        'sort' => '-quantity_incoming,-quantity_required,-fc_quantity_available',
                                                        'per_page' => '999',
                                                    ]) }}'>
                                                    {{ data_get($report, 'warehouse_code') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ data_get($report, 'wh_products_incoming') === 0 ? '-' : data_get($report, 'wh_products_incoming')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <table class="table table-borderless">
                                    <thead>
                                    <tr>
                                        <th scope="col">Missing Restock Levels</th>
                                        <th scope="col" class="text-right"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td><a href='{{ url()->route('reports.restocking.index', [
                                                        'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                        'filter[restock_level]' => 0,
                                                        'filter[warehouse_has_stock]' => true,
                                                        'sort' => '-quantity_incoming,-quantity_available,-fc_quantity_available',
                                                        'per_page' => '999',
                                                    ]) }}'>
                                                    {{ data_get($report, 'warehouse_code') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ data_get($report, 'missing_restock_levels') === 0 ? '-' : data_get($report, 'missing_restock_levels')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <table class="table table-borderless">
                                    <thead>
                                    <tr>
                                        <th scope="col">Products Out Of Stock</th>
                                        <th scope="col" class="text-right"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td><a href='{{ url()->route('reports.restocking.index', [
                                                        'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                        'filter[warehouse_has_stock]' => true,
                                                        'filter[quantity_available_lower_than]' => 1,
                                                        'filter[restock_level_between]' => '0.01,999999',
                                                        'sort' => '-restock_level',
                                                        'per_page' => '999',
                                                    ]) }}'>
                                                    {{ data_get($report, 'warehouse_code') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ data_get($report, 'wh_products_out_of_stock') === 0 ? '-' : data_get($report, 'wh_products_out_of_stock')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <table class="table table-borderless">
                                    <thead>
                                    <tr>
                                        <th scope="col">Products Restockable</th>
                                        <th scope="col" class="text-right"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>
                                                <a href='{{ url()->route('reports.restocking.index', [
                                                        'filter[warehouse_code]' => data_get($report, 'warehouse_code'),
                                                        'filter[warehouse_has_stock]' => true,
                                                        'sort' => '-fc_quantity_available,-quantity_required,-quantity_incoming,quantity_available',
                                                        'per_page' => '999',
                                                    ]) }}'>
                                                    {{ data_get($report, 'warehouse_code') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ data_get($report, '`wh_products_available`') === 0 ? '-' : data_get($report, 'wh_products_available')}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
