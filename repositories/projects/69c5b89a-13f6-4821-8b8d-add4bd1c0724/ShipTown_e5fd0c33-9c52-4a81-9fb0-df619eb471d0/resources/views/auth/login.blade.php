@extends('layouts.auth')

@section('content')
    <div class="container d-flex justify-content-center align-items-center min-vh-100 login-page">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                @if(config('app.demo_mode'))
                    <div class="alert alert-warning text-center ">
                        DEMO MODE
                    </div>
                @endif

                <div class="card shadow-lg border-0 rounded-lg p-0 login-card">
                    <div class="card-body p-4">
                        @error('two_factor_code')
                        <div class="alert alert-danger" role="alert">
                            {{ $message }}
                        </div>
                        @enderror

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="text-muted">{{ __('E-Mail Address') }}</label>
                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       name="email"
                                       value="{{ config('app.demo_mode') ? 'demo-admin@ship.town' : old('email') }}"
                                       required autocomplete="email" autofocus>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password" class="text-muted">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password"
                                       required autocomplete="current-password"
                                       value="{{ config('app.demo_mode') ? 'secret1144' : '' }}">
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="form-group d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="remember"
                                           id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>

                                @if (Route::has('password.request'))
                                    <a class="text-primary small" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>

                            <!-- Submit -->
                            <button type="submit" id="login-button" class="btn btn-primary btn-block font-weight-semibold shadow-sm">
                                {{ __('Login') }}
                            </button>

                            <!-- Register -->
                            @if (Route::has('register'))
                                <div class="text-center mt-3">
                                    <a class="text-muted small" href="{{ route('register') }}">
                                        {{ __('Don\'t have an account? Register') }}
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
