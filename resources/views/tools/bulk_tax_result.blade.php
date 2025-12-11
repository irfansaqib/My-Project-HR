@extends('layouts.admin')
@section('title', 'Calculation Results')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tax Calculation Results</h3>
        <div class="card-tools">
            <button onclick="exportResults()" class="btn btn-success btn-sm">
                <i class="fas fa-file-download"></i> Download Excel
            </button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-bordered text-sm" id="resultTable">
            <thead class="bg-dark text-white">
                <tr>
                    <th>CNIC</th>
                    <th>Name</th>
                    <th class="text-right">Monthly Basic</th>
                    <th class="text-right">Bonus</th>
                    <th class="text-right bg-secondary">Annual Gross</th>
                    <th class="text-right">Annual Taxable</th>
                    <th class="text-right text-info">Paid YTD</th>
                    <th class="text-right font-weight-bold text-danger">New Monthly Tax</th>
                    <th class="text-right text-danger">Total Annual Tax</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $row['cnic'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right" data-val="{{ $row['basic'] }}">{{ number_format($row['basic']) }}</td>
                    <td class="text-right" data-val="{{ $row['bonus'] }}">{{ number_format($row['bonus']) }}</td>
                    <td class="text-right font-weight-bold" data-val="{{ $row['annual_gross'] }}">{{ number_format($row['annual_gross']) }}</td>
                    <td class="text-right" data-val="{{ $row['annual_taxable'] }}">{{ number_format($row['annual_taxable']) }}</td>
                    <td class="text-right text-info" data-val="{{ $row['tax_paid_so_far'] }}">{{ number_format($row['tax_paid_so_far']) }}</td>
                    <td class="text-right font-weight-bold text-danger" data-val="{{ $row['new_monthly_tax'] }}">{{ number_format($row['new_monthly_tax']) }}</td>
                    <td class="text-right" data-val="{{ $row['total_annual_tax'] }}">{{ number_format($row['total_annual_tax']) }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#scheduleModal-{{ $index }}">
                            <i class="fas fa-eye"></i> Schedule
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ✅ DETAILED MODALS --}}
@foreach($data as $index => $row)
<div class="modal fade" id="scheduleModal-{{ $index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-calculator mr-2"></i> Tax Breakdown: {{ $row['name'] }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body bg-light">
                
                {{-- Summary Section --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col-3 border-right">
                                <small class="text-muted text-uppercase">Monthly Basic</small>
                                <h5 class="font-weight-bold">{{ number_format($row['basic']) }}</h5>
                            </div>
                            <div class="col-3 border-right">
                                <small class="text-muted text-uppercase">Monthly Allowances</small>
                                <h5 class="font-weight-bold">{{ number_format($row['monthly_gross'] - $row['basic']) }}</h5>
                            </div>
                            <div class="col-3 border-right">
                                <small class="text-muted text-uppercase">One-Time Bonus</small>
                                <h5 class="text-success font-weight-bold">{{ number_format($row['bonus']) }}</h5>
                            </div>
                            <div class="col-3">
                                <small class="text-muted text-uppercase">Total Annual Tax</small>
                                <h5 class="text-danger font-weight-bold">{{ number_format($row['total_annual_tax']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Monthly Schedule --}}
                <div class="card shadow-sm mb-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="font-weight-bold">Monthly Schedule</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0 text-center">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="text-left pl-3">Month</th>
                                        <th>Status</th>
                                        <th class="text-right">Basic Salary</th>
                                        <th class="text-right">Allowances</th>
                                        <th class="text-right font-weight-bold">Gross Salary</th>
                                        <th class="text-right text-primary">Taxable Income</th>
                                        <th class="text-right text-danger font-weight-bold">Tax Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($row['schedule']))
                                        @foreach($row['schedule'] as $sch)
                                        {{-- Skip Bonus row in list --}}
                                        @if($sch['type'] == 'Bonus') @continue @endif
                                        <tr>
                                            <td class="text-left pl-3 font-weight-bold">{{ $sch['month'] }}</td>
                                            <td>
                                                <span class="badge badge-{{ $sch['type'] == 'History' ? 'secondary' : 'success' }}">{{ $sch['type'] }}</span>
                                            </td>
                                            
                                            {{-- ✅ SAFE FORMATTING: Checks if numeric before format --}}
                                            <td class="text-right">{{ is_numeric($sch['basic']) ? number_format($sch['basic']) : $sch['basic'] }}</td>
                                            <td class="text-right">{{ is_numeric($sch['allowances']) ? number_format($sch['allowances']) : $sch['allowances'] }}</td>
                                            <td class="text-right font-weight-bold">{{ is_numeric($sch['gross']) ? number_format($sch['gross']) : $sch['gross'] }}</td>
                                            <td class="text-right text-primary">{{ is_numeric($sch['taxable']) ? number_format($sch['taxable']) : $sch['taxable'] }}</td>
                                            <td class="text-right text-danger font-weight-bold">{{ is_numeric($sch['tax']) ? number_format($sch['tax']) : $sch['tax'] }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
function exportResults() {
    let csv = [];
    let headers = ["CNIC", "Name", "Monthly Basic", "Bonus", "Annual Gross", "Annual Taxable", "Paid YTD", "Monthly Tax", "Total Annual Tax"];
    csv.push(headers.join(","));

    let rows = document.querySelectorAll("#resultTable tbody tr");
    rows.forEach(row => {
        let cols = row.querySelectorAll("td");
        if(cols.length >= 9 && !row.closest('.modal')) {
            let rowData = [];
            rowData.push('"' + cols[0].innerText.trim() + '"');
            rowData.push('"' + cols[1].innerText.trim() + '"');
            
            for (let i = 2; i <= 8; i++) {
                let val = cols[i].getAttribute("data-val");
                if(!val) val = cols[i].innerText.replace(/,/g, "").trim();
                rowData.push(val);
            }
            csv.push(rowData.join(","));
        }
    });

    let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    let downloadLink = document.createElement("a");
    downloadLink.download = "Tax_Calculation_Results.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}
</script>
@endpush
@endsection