<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Orders - Placed In Last 7 Days</th>
        <th scope="col" class="text-right">{{ $total_count }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($new_order_counts as $status)
        <tr>
            <td>{{ $status->date_placed_at }}</td>
            <td class="text-right">{{ $status->order_count }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
