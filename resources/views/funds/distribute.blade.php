@extends('layouts.admin')
@section('title', 'Distribute Fund Profit')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Distribute Interest / Profit</h3>
    </div>
    {{-- ✅ Added 'novalidate' to prevent browser validation blocks --}}
    <form action="{{ route('funds.profit.store') }}" method="POST" novalidate onsubmit="return confirm('This will credit the profit to all eligible employees based on their current balance. Continue?');">
        @csrf
        <div class="card-body">
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                This tool calculates the total accumulated balance of the selected fund and distributes the profit amount proportionally to each employee's share.
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Select Fund <span class="text-danger">*</span></label>
                    <select name="fund_id" class="form-control select2" required>
                        <option value="">-- Select Fund --</option>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Total Profit Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 500000" required value="{{ old('amount') }}">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Distribution Date <span class="text-danger">*</span></label>
                    <input type="date" name="distribution_date" class="form-control" value="{{ old('distribution_date', now()->format('Y-m-d')) }}" required>
                    <small class="text-muted">Balances will be calculated as of this date.</small>
                </div>
            </div>

            <hr>
            <h5 class="text-primary mb-3">Period Details (For Description)</h5>

            <div class="row">
                {{-- 1. Interest Period Type --}}
                <div class="col-md-4 form-group">
                    <label>Interest Period</label>
                    <select name="period" id="period" class="form-control">
                        <option value="Monthly" {{ old('period') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="Quarterly" {{ old('period') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="Bi-Annual" {{ old('period') == 'Bi-Annual' ? 'selected' : '' }}>Bi-Annual</option>
                        <option value="Annual" {{ old('period') == 'Annual' ? 'selected' : '' }}>Annual</option>
                    </select>
                </div>

                {{-- 2. Fiscal Year --}}
                <div class="col-md-4 form-group">
                    <label>Fiscal Year</label>
                    <select name="fiscal_year" id="fiscal_year" class="form-control">
                        @php
                            $currentYear = now()->year;
                            $startYear = (now()->month < 7) ? $currentYear - 1 : $currentYear;
                        @endphp
                        @foreach(range($startYear - 2, $startYear + 1) as $y)
                            <option value="{{ $y }}" {{ $y == $startYear ? 'selected' : '' }}>
                                {{ $y }}-{{ $y + 1 }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Dynamic Period Selector --}}
                <div class="col-md-4 form-group">
                    <label id="sub_period_label">Select Period</label>
                    
                    {{-- Monthly --}}
                    <div id="wrapper_month" class="period-wrapper" style="display: none;">
                        <select name="month" id="input_month" class="form-control">
                            @foreach(['July','August','September','October','November','December','January','February','March','April','May','June'] as $index => $m)
                                {{-- Value logic: Jul=7 ... Dec=12, Jan=1 ... Jun=6 --}}
                                @php 
                                    $val = $index + 7; 
                                    if ($val > 12) $val -= 12;
                                @endphp
                                <option value="{{ $val }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Quarterly --}}
                    <div id="wrapper_quarter" class="period-wrapper" style="display: none;">
                        <select name="quarter" id="input_quarter" class="form-control">
                            <option value="Q1">Q1 (Jul - Sep)</option>
                            <option value="Q2">Q2 (Oct - Dec)</option>
                            <option value="Q3">Q3 (Jan - Mar)</option>
                            <option value="Q4">Q4 (Apr - Jun)</option>
                        </select>
                    </div>

                    {{-- Bi-Annual --}}
                    <div id="wrapper_half" class="period-wrapper" style="display: none;">
                        <select name="half" id="input_half" class="form-control">
                            <option value="H1">H1 (Jul - Dec)</option>
                            <option value="H2">H2 (Jan - Jun)</option>
                        </select>
                    </div>

                    {{-- Annual --}}
                    <div id="wrapper_annual" class="period-wrapper" style="display: none;">
                        <input type="text" class="form-control" value="Full Fiscal Year" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Description / Notes <span class="text-danger">*</span></label>
                {{-- Removed readonly so you can edit if auto-fill fails --}}
                <input type="text" name="description" id="description" class="form-control" required>
                <small class="text-muted">Auto-generated based on period selection (Editable).</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"> <i class="fas fa-check mr-1"></i> Process Distribution</button>
            <a href="{{ route('funds.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Safe initialization of Select2
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4' });
        }

        function updateDistributionUI() {
            let period = $('#period').val();
            let yearStart = parseInt($('#fiscal_year').val());
            let yearEnd = yearStart + 1;
            let fiscalLabel = yearStart + '-' + yearEnd;
            let specificLabel = '';

            // Hide all wrappers first
            $('.period-wrapper').hide();
            // Disable inputs to keep submission clean (optional)
            $('#input_month, #input_quarter, #input_half').prop('disabled', true);

            if (period === 'Monthly') {
                $('#wrapper_month').show();
                $('#input_month').prop('disabled', false);
                
                let monthName = $('#input_month option:selected').text();
                let monthVal = parseInt($('#input_month').val());
                // Fiscal logic
                let monthYear = (monthVal >= 7) ? yearStart : yearEnd;
                specificLabel = monthName + ' ' + monthYear;
            } 
            else if (period === 'Quarterly') {
                $('#wrapper_quarter').show();
                $('#input_quarter').prop('disabled', false);
                specificLabel = $('#input_quarter option:selected').text();
            } 
            else if (period === 'Bi-Annual') {
                $('#wrapper_half').show();
                $('#input_half').prop('disabled', false);
                specificLabel = $('#input_half option:selected').text();
            } 
            else {
                $('#wrapper_annual').show();
                specificLabel = 'Full Year';
            }

            // Auto-fill Description
            let desc = `Profit Distribution (${period}: ${specificLabel}) - FY ${fiscalLabel}`;
            $('#description').val(desc);
        }

        // Bind change events to all relevant inputs
        $('#period, #fiscal_year, #input_month, #input_quarter, #input_half').on('change', updateDistributionUI);
        
        // Trigger once on load
        updateDistributionUI();
    });
</script>
@endpush