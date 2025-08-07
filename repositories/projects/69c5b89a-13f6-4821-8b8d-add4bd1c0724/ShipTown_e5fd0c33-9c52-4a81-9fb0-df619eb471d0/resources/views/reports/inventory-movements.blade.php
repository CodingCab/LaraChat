@extends('layouts.app')

@section('title',__('Inventory Report'))

@section('content')
    <div class="container dashboard-widgets">
        <inventory-movements-report-page></inventory-movements-report-page>
    </div>
@endsection
