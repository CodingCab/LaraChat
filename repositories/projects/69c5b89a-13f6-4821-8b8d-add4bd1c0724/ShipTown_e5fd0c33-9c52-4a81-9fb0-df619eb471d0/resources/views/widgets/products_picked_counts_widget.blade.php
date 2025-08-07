<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Products Picked</th>
        <th scope="col" class="text-right">{{ $total_count }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($count_per_user as $count)
        <tr>
            <td>{{ $count['name'] }}</td>
            <td class="text-right">{{ $count['total'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
