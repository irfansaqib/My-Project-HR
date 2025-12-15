<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - HR System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; border-radius: 10px; padding: 2rem; }
        .input-group-text { cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <h3 class="text-center mb-4">Client Portal Login</h3>

                {{-- 🔴 THIS ERROR BLOCK WAS MISSING --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                {{-- 🔴 END ERROR BLOCK --}}

                {{-- FORM STARTS HERE --}}
                {{-- Notice the route name: 'client.login.post' --}}
                <form action="{{ route('client.login.post') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text" onclick="togglePassword()">
                                <i id="toggleIcon" class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="{{ route('client.register') }}">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>