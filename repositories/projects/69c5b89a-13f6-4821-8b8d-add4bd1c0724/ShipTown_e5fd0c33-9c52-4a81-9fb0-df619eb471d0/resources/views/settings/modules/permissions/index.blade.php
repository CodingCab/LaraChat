@extends('layouts.app')

@section('title', __('Permissions - Settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <permissions-configuration-page></permissions-configuration-page>
            </div>
        </div>
    </div>
@endsection
