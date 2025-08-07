@extends('layouts.app')

@section('title', t('ChatGPT'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">
                <chat-gpt-page></chat-gpt-page>
            </div>
        </div>
    </div>
@endsection
