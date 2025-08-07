<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Products Shipped</th>
        <th scope="col" class="text-right">{{ $data->sum('quantity_shipped') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $record)
        <tr>
            <td>{{ data_get($record, 'user.name', 'AutoPilot') }}</td>
            <td class="text-right">{{ $record['quantity_shipped'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
