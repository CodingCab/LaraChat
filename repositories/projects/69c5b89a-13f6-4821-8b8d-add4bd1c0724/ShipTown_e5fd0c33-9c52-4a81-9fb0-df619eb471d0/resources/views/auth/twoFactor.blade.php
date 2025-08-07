@extends('layouts.auth')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ __('Two Factor Authentication') }}</div>

                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-info" role="alert">
                            {{ session()->get('message') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verify.store') }}">
                        @csrf

                        <h6 class="text-muted">
                            We emailed you the security code
                        </h6>
                        <div class="form-group row">
                            <div class="col">
                                <input autocomplete="off" id="two_factor_code" type="number" class="form-control @error('two_factor_code') is-invalid @enderror" name="two_factor_code" value="{{ old('two_factor_code') }}" required autofocus>

                                @error('two_factor_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col text-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    {{ __('Verify') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <a class="col btn btn-link" href="https://gmail.com">Gmail</a> |
                <a class="col btn btn-link" href="https://outlook.live.com">Outlook</a> |
                <a class="col btn btn-link" href="https://mail.proton.me">ProtonMail</a>
            </div>
        </div>
    </div>
</div>
@endsection
