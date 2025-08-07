<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Orders Completed</th>
        <th scope="col" class="text-right">{{ $total_count }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($status_order_counts as $status)
            <tr>
                <td><a href="/orders?sort=-order_closed_at&status={{$status->status_code }}">{{$status->status_code }}</a></td>
                <td class="text-right">{{ $status->order_count }}</td>
            </tr>
    @endforeach
    </tbody>
</table>
