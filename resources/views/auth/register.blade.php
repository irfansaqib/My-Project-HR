<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | Registration Page</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}"><b>INH</b> HR & Tasks</a>
    </div>

    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Register a new membership</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Full name" :value="old('name')" required autofocus autocomplete="name">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" :value="old('email')" required autocomplete="username">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />

                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Retype password" required autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />

                <div class="row">
                    <div class="col-8">
                        </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                    </div>
            </form>

            <a href="{{ route('login') }}" class="text-center">I already have a membership</a>
        </div>
        </div></div>
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>