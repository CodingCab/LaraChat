@extends('layouts.app')

@section('title',__('Dashboard'))

@section('content')
    <div class="container dashboard-widgets">
    <div class="row">
        <div class="col">
            <div class="widget-tools-container">
                <font-awesome-icon icon="question-circle" name="user-pick-counts-help-icon"></font-awesome-icon>
                <template>
                    <tippy to="user-pick-counts-help-icon" arrow>
                        <p>
                            {{--                //Total number of picks in last 7 days per user<br>--}}
                        </p>
                    </tippy>
                </template>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center">Shipments</h4>
                    <table class="table-hover w-100">
                        <thead>
                        <tr>
                            <th>{{ __('Shipped At') }}</th>
                            <th>{{ __('Shipper') }}</th>
                            <th>{{ __('Shipping Number') }}</th>
                            <th>{{ __('Order') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($shipments as $shipment)
                            <tr class="table-hover">
                                <td class="pr-2">{{  \Carbon\Carbon::parse($shipment['created_at'])->format('M d, H:i') }}</td>
                                <td class="pr-4">
                                    <a href="{{ route('reports.shipments', ['filter[user_id]' => $shipment['user_id']], true) }}">
                                        {{ data_get($shipment,'user.name', 'AutoPilot') }}
                                    </a>
                                </td>
                                <td class="pr-4">
                                    <a href="{{ $shipment['tracking_url'] }}" target="_blank">
                                        {{ $shipment['shipping_number'] }}
                                    </a>
                                </td>
                                <td class="pr-5">
                                    <a href="{{ route('orders', ['search' => $shipment['order']['order_number'] ]) }}">
                                        #{{ $shipment['order']['order_number'] }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
