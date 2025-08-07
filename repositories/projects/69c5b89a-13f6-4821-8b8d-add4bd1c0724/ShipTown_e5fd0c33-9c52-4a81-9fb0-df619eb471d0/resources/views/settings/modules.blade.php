@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-12">
            <module-configuration></module-configuration>
        </div>
    </div>
</div>
@endsection

@section('css')
    <style>
        .setting-list{
            border-radius: 1px;
            padding: 0.5rem 1rem;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.125);
            width: 100%;
            color: #495057;
            display: flex;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .setting-list:hover, .setting-list:focus {
            color: #495057;
            text-decoration: none;
            background-color: #f8f9fa;
        }

        .setting-icon{
            padding: 1rem;
            margin-right: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }

        .setting-icon:hover{
            background-color: unset;
        }

        .setting-title{
            color: #3490dc;
            font-weight: bolder;
            /*font-size: 1rem;*/
            /*line-height: 1.2;*/
            margin-bottom: 2px;
        }

        .setting-desc{
            color: #6c757d;
            font-size: 10pt;
        }
    </style>
@endsection
