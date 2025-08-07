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
        <h4 class="card-title text-center">Picks</h4>
        <table class="table-hover w-100">
            <thead>
                <tr>
                    <th>{{ __('Picked At') }}</th>
                    <th>{{ __('Picker') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th class="text-right">
                        <a href="{{ route('reports.picks', ['filter[quantity_picked_between]=1,99999']) }}">
                            {{ __('Picked') }}
                        </a>
                    </th>
                    <th class="text-right">
                        <a href="{{ route('reports.picks', ['filter[quantity_skipped_between]=1,99999']) }}">
                            {{ __('Skipped') }}
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($picks as $pick)
                    <tr class="table-hover">
                        <td class="pr-2">{{  \Carbon\Carbon::parse($pick['created_at'])->format('M d, H:i') }}</td>
                        <td class="pr-4">
                            <a href="{{ route('reports.picks', ['filter[user_id]' => $pick['user_id']], true) }}">
                                {{ data_get($pick, 'user.name', '') }}
                            </a>
                        </td>
                        <td class="pr-4">
                            <a href="{{ route('reports.picks', ['filter[sku_picked]' => $pick['sku_ordered']], true) }}">
                                {{ $pick['sku_ordered'] }}
                            </a>
                        </td>
                        <td class="pr-5">{{ $pick['name_ordered'] }}</td>
                        <td class="pl-5 text-right">{{ $pick['quantity_picked'] == 0 ? '' : round($pick['quantity_picked'])  }}</td>
                        <td class="pl-5 text-right">{{ $pick['quantity_skipped_picking'] == 0 ? '' : round($pick['quantity_skipped_picking'])  }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
