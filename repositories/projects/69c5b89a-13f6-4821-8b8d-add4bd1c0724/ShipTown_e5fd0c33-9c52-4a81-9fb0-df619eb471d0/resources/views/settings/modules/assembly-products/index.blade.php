@extends('layouts.app')

@section('title', __('Assembly Products - Settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <assembly-products-page></assembly-products-page>
            </div>
        </div>
    </div>
@endsection
