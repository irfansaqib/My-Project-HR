<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | HR and Tasks Management Portal</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
  
  <style>
      /* Custom Professional Background */
      .content-wrapper {
          background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
          min-height: calc(100vh - 100px) !important; 
      }
      .register-card-body {
          background: rgba(255, 255, 255, 0.98);
          border-top: 3px solid #007bff;
          border-radius: 0 0 5px 5px;
      }
      
      /* NAVBAR STRUCTURAL FIXES */
      .navbar-light {
          background-color: #ffffff;
          box-shadow: 0 4px 6px rgba(0,0,0,.05);
          padding-top: 10px;
          padding-bottom: 10px;
          min-height: 90px; 
      }
      
      /* FORCE LOGO SIZE */
      .main-header .navbar-brand .brand-image {
          height: 75px !important;    
          max-height: 75px !important; 
          width: auto !important;     
          margin-right: 0px; /* Gap handled by text margin now */
          float: none !important;     
          opacity: 1 !important;
          margin-top: 0 !important;   
      }
      
      /* Flex container for Logo + Text */
      .navbar-brand {
          display: flex !important;
          align-items: center;
          padding: 0;
      }

      /* ✅ UPDATED PROJECT TEXT STYLING */
      .brand-text {
          color: #2b2d30ff; /* Changed to Blue to match Logo */
          font-weight: 700; 
          font-size: 1.25rem; /* Reduced Size (was 1.5rem) */
          line-height: 1.2;
          margin-left: 20px; /* Increased Gap between Logo and Title */
      }

      /* NAVIGATION TABS STYLING */
      .nav-link {
          font-weight: 600;
          color: #555 !important;
          margin: 0 10px;
          font-size: 1rem;
          padding: 8px 15px !important; 
          border-radius: 5px;
          transition: all 0.3s;
      }
      .nav-link:hover {
          color: #007bff !important;
          background-color: rgba(0, 123, 255, 0.1); 
      }
      
      .btn-login {
          border-radius: 25px;
          padding: 10px 35px;
          font-size: 1rem;
          font-weight: 600;
      }
  </style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid px-md-5">
      
      <a href="/" class="navbar-brand">
        <img src="{{ asset('storage/INH_HR_LOGO.png') }}" alt="Logo" class="brand-image">
        
        <span class="brand-text">HR and Tasks<br>Management Portal</span>
      </a>

      <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <ul class="navbar-nav ml-auto align-items-center">
          <li class="nav-item">
            <a href="/" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="#services" class="nav-link">Our Services</a>
          </li>
          <li class="nav-item">
            <a href="#pricing" class="nav-link">Pricing</a>
          </li>
          <li class="nav-item">
            <a href="#contact" class="nav-link">Contact Us</a>
          </li>
        </ul>
        
        <ul class="navbar-nav ml-3 align-items-center">
            <li class="nav-item">
                <a href="{{ route('login') }}" class="btn btn-primary btn-login shadow-sm">
                    <i class="fas fa-sign-in-alt mr-2"></i> Log In
                </a>
            </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="content-wrapper d-flex align-items-center justify-content-center">
    
    <div class="register-box" style="width: 500px; margin-top: 40px; margin-bottom: 40px;">
        
        <div class="text-center mb-4">
            <h2 class="font-weight-bold text-dark">Register Your Business</h2>
            <p class="text-muted">Join hundreds of businesses managing HR effectively.</p>
        </div>

        <div class="card card-outline card-primary shadow-lg border-0">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Register a new membership</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Full Name" value="{{ old('name') }}" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror

                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    @error('email')<span class="text-danger small">{{ $message }}</span>@enderror

                    <div class="input-group mb-3">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    @error('password')<span class="text-danger small">{{ $message }}</span>@enderror

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Retype password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center my-3">
                                <div class="border-bottom w-100 mr-2"></div>
                                <span class="text-muted small text-uppercase font-weight-bold text-nowrap">Business Details</span>
                                <div class="border-bottom w-100 ml-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="legal_name" class="form-control" placeholder="Business Legal Name" value="{{ old('legal_name') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-building"></span>
                            </div>
                        </div>
                    </div>
                    @error('legal_name')<span class="text-danger small">{{ $message }}</span>@enderror

                    <div class="input-group mb-3">
                        <input type="text" name="registration_number" class="form-control" placeholder="Registration / CNIC No." value="{{ old('registration_number') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-id-card"></span>
                            </div>
                        </div>
                    </div>
                    @error('registration_number')<span class="text-danger small">{{ $message }}</span>@enderror

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Register Business</button>
                        </div>
                    </div>
                </form>
                
                <div class="social-auth-links text-center mt-3">
                    <a href="{{ route('login') }}" class="text-center">I already have a membership</a>
                </div>
            </div>
            </div></div></div>
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      Enterprise HR Solutions
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="#">HR and Tasks Management Portal</a>.</strong> All rights reserved.
  </footer>
</div>
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>