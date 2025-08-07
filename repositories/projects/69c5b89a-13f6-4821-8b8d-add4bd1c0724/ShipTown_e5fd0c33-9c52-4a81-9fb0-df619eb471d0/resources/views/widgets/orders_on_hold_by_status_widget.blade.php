@if($total_count > 0)
    <table class="table table-borderless">
        <thead>
        <tr>
            <th scope="col">{{ t('Orders - On Hold') }}</th>
            <th scope="col" class="text-right">{{ $total_count }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($order_status_counts as $order_status)
            <tr>
                <td>
                    <a href="{{ route('orders', [
                    'status' => $order_status->status_code,
                    'sort' => 'order_placed_at',
                    'created_between' => ''
                ]) }}">{{$order_status->status_code }}</a>
                </td>
                <td class="text-right">{{ $order_status->order_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
