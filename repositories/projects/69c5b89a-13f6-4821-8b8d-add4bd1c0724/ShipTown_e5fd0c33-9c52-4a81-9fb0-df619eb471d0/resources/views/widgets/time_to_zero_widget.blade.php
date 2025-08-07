<table class="table table-borderless">
    <thead>
    <tr>
        <th scope="col">Performance Dashboard</th>
        <th scope="col" class="text-right"></th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>Orders Placed</td>
            <td class="text-right">{{ $data['orders_placed_count'] }}</td>
        </tr>
        <tr>
            <td>Orders Completed</td>
            <td class="text-right">{{ $data['orders_closed_count'] }}</td>
        </tr>
        <tr class="font-weight-bold">
            <td>Period Balance</td>
            <td class="text-right">{{ $data['balance'] }}</td>
        </tr>
        <tr>
            <td>Active Orders Count</td>
            <td class="text-right">{{ $data['active_orders_count'] }}</td>
        </tr>
        <tr>
            <td>Average Orders Completed Per Staff Per Day</td>
            <td class="text-right">{{ $data['avg_per_staff_per_day'] }}</td>
        </tr>
        <tr>
            <td>Daily Staff Required To Keep Up</td>
            <td class="text-right">{{ $data['staff_days_required_for_balance_0'] }}</td>
        </tr>
        <tr>
            <td>Extra Staff Required To Clear in 5 days</td>
            <td class="text-right">{{ $data['staff_required_to_clear_in_5days'] }}</td>
        </tr>
{{--        <tr>--}}
{{--            <td>Average Order Total</td>--}}
{{--            <td class="text-right">€ {{ round($data['average_order_total'],1) }}</td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td>Staff Working Day Value</td>--}}
{{--            <td class="text-right">€ {{ $data['staff_working_day_value'] }}</td>--}}
{{--        </tr>--}}
    </tbody>
</table>
