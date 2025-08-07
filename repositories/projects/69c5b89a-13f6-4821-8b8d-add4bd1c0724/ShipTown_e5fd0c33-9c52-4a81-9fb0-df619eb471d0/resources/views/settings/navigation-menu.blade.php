@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
    <div class="container">
        @if (session('status'))
            <div class="row">
                <div class="col">
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                </div>
            </div>
        @endif
        <navigation-menu-page></navigation-menu-page>
    </div>
@endsection
