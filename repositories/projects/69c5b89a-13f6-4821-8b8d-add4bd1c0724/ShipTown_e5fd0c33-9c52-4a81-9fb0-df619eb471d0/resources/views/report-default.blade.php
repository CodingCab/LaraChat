@extends('layouts.app')

@section('title',__('Inventory Report'))

@section('content')
    <report download-button-text="{{ __('Download All') }}"></report>
@endsection
