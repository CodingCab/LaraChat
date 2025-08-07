@extends('layouts.app')

@section('title', __('DPD Poland Configuration'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <shippy-pro-dpd-poland-configuration></shippy-pro-dpd-poland-configuration>
            </div>
        </div>
@endsection
