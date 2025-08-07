@extends('layouts.app')

@section('title', __('Data Collector'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <shelf-label-printing-page></shelf-label-printing-page>
            </div>
        </div>
    </div>
@endsection
