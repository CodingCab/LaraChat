@extends('layouts.app')

@section('title',__('Restocking'))

@section('content')
    <restocking-page :initial_data="{{ json_encode($cached_restocking_report ?? null) }}"></restocking-page>
@endsection
