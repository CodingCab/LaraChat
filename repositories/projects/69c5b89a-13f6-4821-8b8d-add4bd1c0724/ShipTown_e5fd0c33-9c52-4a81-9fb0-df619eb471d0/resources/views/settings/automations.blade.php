@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div class="container-fluid">
    @if (session('status'))
        <div class="row">
            <div class="col">
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            </div>
        </div>
    @endif
    <automations-page></automations-page>
</div>
@endsection

