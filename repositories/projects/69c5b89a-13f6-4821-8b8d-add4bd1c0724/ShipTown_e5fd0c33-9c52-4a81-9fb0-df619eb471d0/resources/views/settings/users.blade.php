@extends('layouts.app')

@section('title', __('Users'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <user-table></user-table>
            </div>
        </div>
    </div>
@endsection
