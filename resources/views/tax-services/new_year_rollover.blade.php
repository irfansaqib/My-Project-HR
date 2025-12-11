@extends('layouts.tax_client')

@section('tab-content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-left-warning shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-dark">
                    <span class="text-primary mr-2">{{ $client->name }}</span> | 
                    Start New Tax Year
                </h5>
            </div>
            
            <div class="card-body">
                {{-- STATUS DISPLAY --}}
                <div class="mb-4 text-center">
                    <span class="d-block text-muted small text-uppercase font-weight-bold">Current Status</span>
                    <h5 class="font-weight-bold text-dark mb-1">
                        Last Finalized Payroll: 
                        <span class="text-info">{{ $lastMonthName }}</span>
                    </h5>
                </div>

                @if(!$canRollover)
                    {{-- BLOCKER MESSAGE --}}
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-lock fa-2x mb-2 text-danger"></i>
                        <h5>Tax Year Not Concluded</h5>
                        <p class="mb-0">{{ $statusMessage }}</p>
                        <hr>
                        <a href="{{ route('tax-services.clients.salary', $client->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left mr-1"></i> Go Back to Payroll
                        </a>
                    </div>
                @else
                    {{-- ROLLOVER FORM --}}
                    <div class="alert alert-success text-center">
                        <h4 class="alert-heading font-weight-bold"><i class="fas fa-check-circle mr-2"></i>Ready to Rollover</h4>
                        <p class="mb-0">You are about to start the <strong>{{ $nextTaxYearLabel }}</strong> Tax Year.</p>
                    </div>
                    
                    <form action="{{ route('tax-services.clients.process-new-year', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will reset all YTD values and cannot be undone.');">
                        @csrf
                        
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body py-2 px-3">
                                <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-info-circle mr-1 text-warning"></i> What happens next?</h6>
                                <ul class="small text-muted pl-3 mb-0">
                                    <li><strong>Opening Taxable Income</strong> and <strong>Tax Paid</strong> will reset to 0.</li>
                                    <li>Current year data remains accessible in <strong>Reports & History</strong>.</li>
                                    <li>The system will prepare for <strong>July {{ explode('-', $nextTaxYearLabel)[0] }}</strong> input.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Salary Increment (Optional)</label>
                            <select name="increment_type" id="increment_type" class="form-control">
                                <option value="none">No Increment (Keep Salaries Same)</option>
                                <option value="percentage">Percentage Increase (%)</option>
                                <option value="fixed">Fixed Amount Increase (PKR)</option>
                            </select>
                        </div>

                        <div class="form-group" id="val_box" style="display:none;">
                            <label>Increment Value</label>
                            <input type="number" step="0.01" name="increment_value" class="form-control" placeholder="e.g. 10 for 10%, or 5000 for 5k">
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tax-services.clients.salary', $client->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning font-weight-bold shadow-sm">
                                <i class="fas fa-forward mr-1"></i> Confirm & Start New Year
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('increment_type').addEventListener('change', function() {
        document.getElementById('val_box').style.display = (this.value === 'none') ? 'none' : 'block';
    });
</script>
@endsection