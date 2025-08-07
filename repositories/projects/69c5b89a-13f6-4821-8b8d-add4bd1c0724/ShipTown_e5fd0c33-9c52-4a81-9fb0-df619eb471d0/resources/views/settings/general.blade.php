@extends('layouts.app')

@section('title', t('Settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                @if (auth()->user()->hasRole('admin'))
                    <configuration-section></configuration-section>
                    <maintenance-section></maintenance-section>
                @endif
            </div>
        </div>
    </div>
@endsection
