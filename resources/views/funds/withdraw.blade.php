@extends('layouts.admin')
@section('title', 'Record Fund Withdrawal')

@section('content')
<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">Record Withdrawal / Loan against Fund</h3>
    </div>
    <form action="{{ route('funds.withdraw.store') }}" method="POST">
        @csrf
        <div class="card-body">
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-control select2" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Select Fund <span class="text-danger">*</span></label>
                    <select name="fund_id" class="form-control" required>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}" {{ old('fund_id') == $fund->id ? 'selected' : '' }}>{{ $fund->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <hr>

            <div class="form-group">
                <label>Withdrawal Type <span class="text-danger">*</span></label>
                <div class="mt-2">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="type_perm" name="type" value="permanent" class="custom-control-input" {{ old('type', 'permanent') == 'permanent' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="type_perm">Permanent Withdrawal (Leaving/Partial)</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="type_refund" name="type" value="refundable" class="custom-control-input" {{ old('type') == 'refundable' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="type_refund">Refundable Loan (Repay via Salary)</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" required min="1" value="{{ old('amount') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                </div>
            </div>
            
            {{-- Refundable Options --}}
            <div id="refundable_options" style="display: none;" class="bg-light p-3 rounded mb-3 border">
                <h6 class="text-primary font-weight-bold">Repayment Terms</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Number of Installments <span class="text-danger">*</span></label>
                        <input type="number" name="installments" id="installments" class="form-control" min="1" value="{{ old('installments', 12) }}">
                        <small id="monthly_deduct" class="text-muted"></small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Repayment Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="repayment_start_date" id="repayment_start_date" class="form-control" value="{{ old('repayment_start_date') }}">
                        <small class="text-muted">Salary deduction will start from this date.</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" placeholder="Reason for withdrawal" value="{{ old('description') }}">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-danger">Confirm Withdrawal</button>
            <a href="{{ route('funds.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Safe Select2 Init
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4' });
        }

        function toggleType() {
            // Robust check: get value of the radio button that is currently checked
            var type = $('input[name="type"]:checked').val();
            
            if (type === 'refundable') {
                $('#refundable_options').slideDown();
                $('#installments, #repayment_start_date').prop('required', true);
            } else {
                $('#refundable_options').slideUp();
                $('#installments, #repayment_start_date').prop('required', false);
            }
        }
        
        function calcMonthly() {
            let amt = parseFloat($('#amount').val()) || 0;
            let inst = parseInt($('#installments').val()) || 1;
            if(amt > 0 && inst > 0) {
                $('#monthly_deduct').text('Monthly Deduction: ' + (amt/inst).toFixed(2));
            } else {
                $('#monthly_deduct').text('');
            }
        }

        // Bind to change event on all radio inputs with name="type"
        $('input[name="type"]').on('change', toggleType);
        
        // Bind calculation events
        $('#amount, #installments').on('input change', calcMonthly);
        
        // Run on load to apply correct state immediately (e.g. after validation error)
        toggleType();
        calcMonthly();
    });
</script>
@endpush