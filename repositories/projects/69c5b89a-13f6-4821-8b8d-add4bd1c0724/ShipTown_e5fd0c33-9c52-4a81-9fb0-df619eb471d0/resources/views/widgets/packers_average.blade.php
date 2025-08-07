<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Daily Averages</th>
        <th scope="col" class="text-right">{{ round($total_count,0) }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($count_per_user as $count)
        <tr>
            <td>
                <a href="{{ route('orders', [
                    'sort' => 'packed_at',
                    'packed_between' => $config['starting_date']->toDateTimeString() . ',' .$config['ending_date']->toDateTimeString(),
                    'packer_user_id' =>  $count['packer_user_id'],
                    ]) }}" target="_blank">
                    {{ $count['name'] }}
                </a>
            </td>
            <td class="text-right">{{ round($count['daily_average'],0) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
