<div class="card-body">
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
            <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name', $businessBankAccount->bank_name ?? '') }}" required>
        </div>
        <div class="col-md-6 form-group">
            <label for="account_title">Account Title <span class="text-danger">*</span></label>
            <input type="text" name="account_title" id="account_title" class="form-control" value="{{ old('account_title', $businessBankAccount->account_title ?? '') }}" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="account_number">Account Number <span class="text-danger">*</span></label>
            <input type="text" name="account_number" id="account_number" class="form-control" value="{{ old('account_number', $businessBankAccount->account_number ?? '') }}" required>
        </div>
        <div class="col-md-6 form-group">
            <label for="branch_code">Branch Code</label>
            <input type="text" name="branch_code" id="branch_code" class="form-control" value="{{ old('branch_code', $businessBankAccount->branch_code ?? '') }}">
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input class="custom-control-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $businessBankAccount->is_default ?? false) ? 'checked' : '' }}>
            <label for="is_default" class="custom-control-label">Set as default account for payments</label>
        </div>
    </div>
</div>