@extends('layouts.admin')
@section('title', 'Email Configuration')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">SMTP Email Settings</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <p class="text-muted">Configure your SMTP settings to send payslips and other notifications to your employees. You can get these details from your email provider (e.g., Gmail, Outlook, or your web hosting service).</p>
        <form action="{{ route('email-configuration.update') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="host">SMTP Host <span class="text-danger">*</span></label>
                    <input type="text" name="host" id="host" class="form-control" value="{{ old('host', $config->host ?? '') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="port">SMTP Port <span class="text-danger">*</span></label>
                    <input type="number" name="port" id="port" class="form-control" value="{{ old('port', $config->port ?? '587') }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $config->username ?? '') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" value="{{ old('password', $config->password ?? '') }}" required>
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 form-group">
                    <label for="encryption">Encryption <span class="text-danger">*</span></label>
                    <select name="encryption" id="encryption" class="form-control" required>
                        <option value="tls" @if(old('encryption', $config->encryption ?? '') == 'tls') selected @endif>TLS</option>
                        <option value="ssl" @if(old('encryption', $config->encryption ?? '') == 'ssl') selected @endif>SSL</option>
                        <option value="starttls" @if(old('encryption', $config->encryption ?? '') == 'starttls') selected @endif>STARTTLS</option>
                    </select>
                </div>
            </div>
            <hr>
            <div class="row">
                 <div class="col-md-6 form-group">
                    <label for="from_address">From Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="from_address" id="from_address" class="form-control" value="{{ old('from_address', $config->from_address ?? '') }}" required placeholder="e.g., no-reply@yourcompany.com">
                </div>
                <div class="col-md-6 form-group">
                    <label for="from_name">From Name <span class="text-danger">*</span></label>
                    <input type="text" name="from_name" id="from_name" class="form-control" value="{{ old('from_name', $config->from_name ?? '') }}" required placeholder="e.g., Your Company Name">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Configuration</button>
            {{-- ** THIS IS THE NEW TEST BUTTON ** --}}
            <a href="{{ route('email-configuration.test') }}" class="btn btn-info" target="_blank">Send Test Email</a>
        </form>
    </div>
</div>
@endsection