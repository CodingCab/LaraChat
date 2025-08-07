@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <{{ \Illuminate\Support\Facades\Route::current()->uri }}-page></{{ \Illuminate\Support\Facades\Route::current()->uri }}-page>
            </div>
        </div>
    </div>
@endsection
