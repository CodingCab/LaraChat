@extends('layouts.app')

@section('title', __('Scheduled Reports'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-12">
            <scheduled-reports-page></scheduled-reports-page>
        </div>
    </div>
</div>
@endsection
