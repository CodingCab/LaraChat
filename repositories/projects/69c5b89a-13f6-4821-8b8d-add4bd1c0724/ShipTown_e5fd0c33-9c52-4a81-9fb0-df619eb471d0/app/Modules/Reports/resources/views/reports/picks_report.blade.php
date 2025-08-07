@extends('layouts.app')

@section('title',__('Dashboard'))

@section('content')
    <div class="container dashboard-widgets">
        <div class="row">
            <div class="col">@widget('PicksReport')</div>
        </div>
    </div>
@endsection

