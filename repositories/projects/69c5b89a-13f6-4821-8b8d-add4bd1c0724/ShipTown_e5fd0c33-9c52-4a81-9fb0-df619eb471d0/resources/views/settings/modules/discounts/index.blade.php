@extends('layouts.app')

@section('title', __('Discounts - Settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <data-collector-discounts-configuration-page></data-collector-discounts-configuration-page>
            </div>
        </div>
    </div>
@endsection
