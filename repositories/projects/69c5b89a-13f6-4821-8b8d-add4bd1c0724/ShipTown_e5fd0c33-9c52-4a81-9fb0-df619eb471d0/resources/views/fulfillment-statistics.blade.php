@extends('layouts.app')

@section('title',__('Fulfillment Statistics'))

@section('content')
<div class="container dashboard-widgets">

    <div class="row">
        <div class="col card">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@widget('DateSelectorWidget', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@asyncWidget('apt', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@widget('PackersAverage', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@widget('OrderPackedCountsByUser', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@asyncWidget('ProductsShippedByUserWidget', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@asyncWidget('ProductsPickedCountsWidget', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="">@asyncWidget('CompletedStatusOrderCount', ['between_dates' => Request::get('between_dates') ])</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

