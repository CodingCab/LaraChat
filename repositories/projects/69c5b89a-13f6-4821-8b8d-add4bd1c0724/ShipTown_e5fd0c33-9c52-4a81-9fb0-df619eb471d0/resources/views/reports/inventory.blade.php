@extends('layouts.app')

@section('title',__('Inventory Report'))

@section('content')
    <div class="container dashboard-widgets">
    <div class="row">
        <div class="col">
            <div class="widget-tools-container">
                <a class="btn btn-primary btn-sm mt-2 fa-arrow-alt-circle-down"  href="{{ request()->fullUrlWithQuery(['filename' =>  __($report_name).'.csv']) }}">{{ __('Download All') }}</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center">{{ __($report_name) }}</h4>
                    <div class="row text-right d-block flex-nowrap">
                        <div class="col text-right">
                            <div class="">@widget('DateSelectorWidget', ['url_param_name' => 'filter[date_between]'])</div>
                        </div>
                    </div>
                    <table class="table-hover w-100">
                        <thead>
                        <tr>
                            @foreach($fields as $field)
                                <th>{{ __($field) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $record)
                            <tr class="table-hover">
                                @foreach($fields as $field)
                                    <td>{{ data_get($record, $field) }}</td>
                                @endforeach
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
