@extends('layouts.app')

@section('content')
    <div class="container">
        @if(!empty($screenshots))
            @foreach($screenshots as $folder => $files)
                <h2><a class="text-secondary" id="{{ $folder }}" href="#{{ $folder }}">{{ $folder }}</a></h2>
                <table style="table-layout: auto;">
                    <tr>
                    @foreach($files as $file)
                        <td>
                            <a target="_blank" href="{{ asset('img/screenshots/' . $file) }}">
                                <div class="text-secondary small">{{ $file }}</div>
                                <img style="display: block; max-width: 300px; height: auto;"
                                     src="{{ asset('img/screenshots/' . $file) }}"
                                     class="img-fluid" alt="{{ $file }}">
                            </a>
                        </td>
                    @endforeach
                    </tr>
                </table>
            @endforeach
        @else
            <p>No screenshots found.</p>
        @endif
    </div>

    <style>
        a.anchor {
            display: block;
            position: relative;
            top: -250px;
            visibility: hidden;
        }
    </style>
@endsection
