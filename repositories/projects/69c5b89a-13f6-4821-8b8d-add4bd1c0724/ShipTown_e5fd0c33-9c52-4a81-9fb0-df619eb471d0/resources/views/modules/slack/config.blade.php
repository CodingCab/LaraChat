@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8 col-md-12">
                <admin-modules-slack-config-page></admin-modules-slack-config-page>
            </div>
        </div>
    </div>
@endsection
