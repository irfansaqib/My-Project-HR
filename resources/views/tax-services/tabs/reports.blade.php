@extends('layouts.tax_client')

@section('tab-content')

<div class="row">
    
    {{-- 1. TAX DEDUCTION REPORT --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 border-left-primary">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice-dollar mr-2"></i> Tax Deduction Report</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Generate tax deduction details (Section 149).</p>
                
                {{-- MAIN FORM --}}
                <form method="GET">
                    <div class="form-group">
                        <label class="small font-weight-bold">Date Range</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="start_date" class="form-control" required>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        {{-- VIEW BUTTON: Opens New Tab --}}
                        <button type="submit" 
                                formaction="{{ route('tax-services.reports.tax-deduction-view', $client->id) }}" 
                                formtarget="_blank"
                                class="btn btn-info btn-sm font-weight-bold">
                            <i class="fas fa-eye mr-1"></i> View Report
                        </button>

                        {{-- DOWNLOAD BUTTON: Direct Download --}}
                        <button type="submit" 
                                formaction="{{ route('tax-services.reports.tax-deduction-csv', $client->id) }}" 
                                formtarget="_self"
                                class="btn btn-success btn-sm font-weight-bold">
                            <i class="fas fa-download mr-1"></i> Download CSV
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- 2. SALARY SHEET REPORT --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 border-left-success">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-table mr-2"></i> Monthly Salary Sheet</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Download detailed payroll sheet for a specific month.</p>
                <div class="form-group">
                    <label class="small font-weight-bold">Select Month</label>
                    <select id="salarySheetSelect" class="form-control form-control-sm">
                        <option value="">-- Select Month --</option>
                        @foreach($salarySheets as $sheet)
                            <option value="{{ route('tax-services.sheet.export', $sheet->id) }}">
                                {{ $sheet->month->format('F, Y') }} ({{ ucfirst($sheet->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-success btn-block btn-sm font-weight-bold" onclick="downloadSalarySheet()">
                    <i class="fas fa-download mr-1"></i> Download Sheet
                </button>
            </div>
        </div>
    </div>

    {{-- 3. ANNUAL PROJECTION --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 border-left-info">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-chart-line mr-2"></i> Annual Projection</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">View projected annual tax liability.</p>
                <form action="{{ route('tax-services.clients.projection', $client->id) }}" method="GET" target="_blank">
                    <div class="form-group">
                        <label class="small font-weight-bold">Tax Year</label>
                        <select name="tax_year" class="form-control form-control-sm">
                            <option value="2025">2025 - 2026</option>
                            <option value="2026">2026 - 2027</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-sm font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> View Projection
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- HISTORY TABLE BELOW --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2"></i> Monthly Payroll History</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Month</th>
                            <th>Status</th>
                            <th class="text-right">Total Salary</th>
                            <th class="text-right">Total Tax</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->salarySheets as $sheet)
                        <tr>
                            <td class="font-weight-bold">{{ $sheet->month->format('F, Y') }}</td>
                            <td><span class="badge badge-success">Finalized</span></td>
                            <td class="text-right">{{ number_format($sheet->items->sum('gross_salary')) }}</td>
                            <td class="text-right text-danger">{{ number_format($sheet->items->sum('income_tax')) }}</td>
                            <td class="text-center">
                                <a href="{{ route('tax-services.sheet.show', $sheet->id) }}" class="btn btn-sm btn-info" title="View Sheet"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('tax-services.sheet.export', $sheet->id) }}" class="btn btn-sm btn-light border" title="Download CSV"><i class="fas fa-file-csv text-success"></i> CSV</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No finalized sheets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function downloadSalarySheet() {
        let url = $('#salarySheetSelect').val();
        if(url) window.location.href = url;
        else alert('Please select a month first.');
    }
</script>
@endsection