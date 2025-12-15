<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration - Client Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f4f6; }
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .form-label { font-weight: 500; font-size: 0.9rem; }
        .password-toggle { cursor: pointer; color: #6c757d; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Create Client Account</h3>
                        <p class="text-muted">Enter your business details to get started</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('client.register.post') }}">
                        @csrf
                        
                        <input type="hidden" name="business_code" value="{{ request('code') }}">

                        <div class="mb-3">
                            <label class="form-label">Full Name / Business Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. John Doe or ABC Trading">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="name@company.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Type <span class="text-danger">*</span></label>
                            <select name="business_type" id="business_type" class="form-select" required onchange="toggleFields()">
                                <option value="" selected disabled>Select Entity Type...</option>
                                <option value="Individual" {{ old('business_type') == 'Individual' ? 'selected' : '' }}>Individual / Sole Proprietor</option>
                                <option value="Partnership" {{ old('business_type') == 'Partnership' ? 'selected' : '' }}>Partnership Firm</option>
                                <option value="Company" {{ old('business_type') == 'Company' ? 'selected' : '' }}>Company (Pvt Ltd / Ltd)</option>
                            </select>
                        </div>

                        <div class="p-3 bg-light rounded mb-3 border">
                            
                            <div class="mb-3" id="cnic_field" style="display:none;">
                                <label class="form-label">CNIC Number <span class="text-danger">*</span></label>
                                <input type="text" name="cnic" id="cnic_input" class="form-control" 
                                       value="{{ old('cnic') }}" 
                                       placeholder="42101-1234567-1" 
                                       maxlength="15">
                                <div class="form-text">Format: 12345-1234567-1 (Auto-formatted)</div>
                            </div>

                            <div class="mb-3" id="reg_no_field" style="display:none;">
                                <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" name="registration_number" id="reg_input" class="form-control" 
                                       value="{{ old('registration_number') }}" 
                                       placeholder="e.g. SECP Registration">
                            </div>

                            <div class="mb-0" id="ntn_field" style="display:none;">
                                <label class="form-label">
                                    NTN Number 
                                    <span id="ntn_required_star" class="text-danger" style="display:none;">*</span>
                                    <span id="ntn_optional_badge" class="badge bg-secondary ms-1" style="display:none;">Optional</span>
                                </label>
                                <input type="text" name="ntn" id="ntn_input" class="form-control" 
                                       value="{{ old('ntn') }}" 
                                       placeholder="1234567-8"
                                       maxlength="9">
                                <div class="form-text" id="ntn_hint">Format: 7 digits, dash, 1 check digit</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" required placeholder="Min 8 chars">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('password', 'togglePasswordIcon')">
                                        <i class="far fa-eye" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Repeat password">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('password_confirmation', 'toggleConfirmPasswordIcon')">
                                        <i class="far fa-eye" id="toggleConfirmPasswordIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg">Register Account</button>
                        </div>
                        <div class="text-center mt-3">
                            <small>Already have an account? <a href="{{ route('client.login') }}" class="text-decoration-none">Login here</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. Toggle Fields Visibility ---
        function toggleFields() {
            var type = document.getElementById("business_type").value;
            
            var cnicField = document.getElementById("cnic_field");
            var regField = document.getElementById("reg_no_field");
            var ntnField = document.getElementById("ntn_field");
            
            var ntnStar = document.getElementById("ntn_required_star");
            var ntnBadge = document.getElementById("ntn_optional_badge");
            
            var cnicInput = document.getElementById("cnic_input");
            var regInput = document.getElementById("reg_input");

            // Reset
            cnicField.style.display = "none";
            regField.style.display = "none";
            ntnField.style.display = "block"; 

            if (type === "Individual") {
                cnicField.style.display = "block";
                cnicInput.required = true;
                regInput.required = false;

                ntnStar.style.display = "none";
                ntnBadge.style.display = "inline-block";
            } else {
                regField.style.display = "block";
                regInput.required = true;
                cnicInput.required = false;

                ntnStar.style.display = "inline";
                ntnBadge.style.display = "none";
            }
        }

        // --- 2. Input Masking (Auto Dash) ---
        
        // CNIC MASK: #####-#######-#
        document.getElementById('cnic_input').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,5})(\d{0,7})(\d{0,1})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        // NTN MASK: #######-#
        document.getElementById('ntn_input').addEventListener('input', function (e) {
            // Remove dashes first to get pure alphanumeric
            let val = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
            
            // If length > 7, insert dash before the last character
            if (val.length > 7) {
                // Take first 7 + dash + 8th char
                e.target.value = val.substring(0, 7) + '-' + val.substring(7, 8);
            } else {
                e.target.value = val;
            }
        });

        // --- 3. Password Toggle ---
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            if(document.getElementById("business_type").value) { toggleFields(); }
        });
    </script>

</body>
</html>