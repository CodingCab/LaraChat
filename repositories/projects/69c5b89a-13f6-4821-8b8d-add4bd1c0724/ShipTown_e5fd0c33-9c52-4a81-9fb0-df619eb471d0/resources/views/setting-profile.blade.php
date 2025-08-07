@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">

            <user-courier-integration-select></user-courier-integration-select>

            <printer-configuration></printer-configuration>

        </div>
    </div>
</div>
@endsection
