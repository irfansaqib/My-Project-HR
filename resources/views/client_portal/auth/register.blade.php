<!DOCTYPE html>
<html lang="en">
<head>
    <title>Client Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join our portal to manage your services</p>
                    </div>

                    <form action="{{ route('client.register.submit') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Business Name</label>
                            <input type="text" name="business_name" class="form-control" required value="{{ old('business_name') }}">
                        </div>

                        <div class="row mb-3">
                            <div class="col-4">
                                <label class="form-label fw-bold">ID Type</label>
                                <select name="id_type" id="id_type" class="form-select" onchange="toggleIdType()">
                                    <option value="NTN">NTN</option>
                                    <option value="CNIC">CNIC</option>
                                </select>
                            </div>
                            <div class="col-8">
                                <label class="form-label fw-bold" id="lbl_id">NTN Number</label>
                                <input type="text" name="ntn_cnic" id="inp_id" class="form-control" required 
                                       placeholder="A123456-8" oninput="formatIdInput(this)" value="{{ old('ntn_cnic') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" required value="{{ old('contact_person') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="{{ old('email', request('email')) }}">
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign Up</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('client.login') }}">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleIdType() {
        let type = document.getElementById('id_type').value;
        let lbl = document.getElementById('lbl_id');
        let inp = document.getElementById('inp_id');
        
        if(type === 'CNIC') {
            lbl.innerText = 'CNIC (13 Digits)';
            inp.placeholder = '3120225252641';
            inp.maxLength = 13;
        } else {
            lbl.innerText = 'NTN Number';
            inp.placeholder = 'A123456-8';
            inp.maxLength = 9;
        }
    }
    
    function formatIdInput(el) {
        let type = document.getElementById('id_type').value;
        let val = el.value.toUpperCase();
        if (type === 'CNIC') {
            el.value = val.replace(/[^0-9]/g, '').slice(0, 13);
        } else {
            let clean = val.replace(/[^A-Z0-9]/g, '');
            if (clean.length > 7) el.value = clean.slice(0, 7) + '-' + clean.slice(7, 8);
            else el.value = clean;
        }
    }
</script>
</body>
</html>