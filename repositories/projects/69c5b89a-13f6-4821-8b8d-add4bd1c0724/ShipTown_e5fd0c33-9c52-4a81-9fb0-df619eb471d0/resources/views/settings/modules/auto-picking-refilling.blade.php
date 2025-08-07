@extends('layouts.app')

@section('title', __('Automation - Auto "picking" refilling - Settings'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col">
            <auto-picking-refilling-configuration-page></auto-picking-refilling-configuration-page>
        </div>
    </div>
</div>
@endsection
