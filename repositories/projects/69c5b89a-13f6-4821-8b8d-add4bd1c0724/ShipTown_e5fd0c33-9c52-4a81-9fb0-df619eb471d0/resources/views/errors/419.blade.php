@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message')
    {{ __('Page Expired') }}<br>
    {{ t('Redirecting to dashboard...') }}
    <script>window.location.replace('/dashboard');</script>
@endsection
