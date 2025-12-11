@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $client->company_name }}</h1>
            <p class="mb-0 text-muted">
                TRN: {{ $client->trn_number }} | 
                Status: <span class="badge badge-{{ $client->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($client->status) }}</span>
            </p>
        </div>
        <a href="{{ route('tax-services.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="clientTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                        <i class="fas fa-tachometer-alt mr-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="employees-tab" data-toggle="tab" href="#employees" role="tab" aria-controls="employees" aria-selected="false">
                        <i class="fas fa-users mr-1"></i> Employees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="processing-tab" data-toggle="tab" href="#processing" role="tab" aria-controls="processing" aria-selected="false">
                        <i class="fas fa-calculator mr-1"></i> Salary & History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab" aria-controls="reports" aria-selected="false">
                        <i class="fas fa-chart-bar mr-1"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab" aria-controls="settings" aria-selected="false">
                        <i class="fas fa-cogs mr-1"></i> Settings
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="clientTabsContent">
                
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-primary">Client Summary</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th class="bg-light w-25">Company</th>
                                    <td>{{ $client->company_name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">TRN</th>
                                    <td>{{ $client->trn_number }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Status</th>
                                    <td>{{ ucfirst($client->status) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-success">Tax Year Information</h5>
                            <div class="alert alert-success">
                                <h4 class="alert-heading">Current Fiscal Year: {{ $client->current_tax_year }}</h4>
                                <p>Start Date: {{ $client->tax_year_start ?? 'Not Set' }}</p>
                                <p class="mb-0">End Date: {{ $client->tax_year_end ?? 'Not Set' }}</p>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('tax-services.clients.new-year', $client->id) }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-calendar-alt mr-1"></i> Start New Tax Year
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="employees" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-gray-800">Active Employees</h5>
                        <div>
                            <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addEmployeeModal">
                                <i class="fas fa-plus fa-sm"></i> Add New
                            </button>
                            <a href="{{ route('tax-services.clients.export-employees', $client->id) }}" class="btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-download fa-sm"></i> Export
                            </a>
                            <button class="btn btn-sm btn-info shadow-sm" data-toggle="modal" data-target="#importEmployeeModal">
                                <i class="fas fa-file-upload fa-sm"></i> Import
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped" width="100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Tax Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_ref_id }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->designation }}</td>
                                    <td>
                                        <span class="badge badge-{{ $employee->tax_status == 'taxable' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($employee->tax_status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('tax-services.employees.delete', [$client->id, $employee->id]) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-circle"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="processing" role="tabpanel">
                    
                    <div class="mb-4 p-3 border rounded bg-light">
                        <h6 class="font-weight-bold text-success border-bottom pb-2">
                            <i class="fas fa-file-excel mr-1"></i> Step 1: Monthly Data Input
                        </h6>
                        <form id="salaryInputForm" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <div class="form-row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">Select Month</label>
                                    <input type="month" name="month" id="salaryMonth" class="form-control" value="{{ date('Y-m') }}" required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="font-weight-bold small">Upload Salary CSV</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="salary_file" name="salary_file" accept=".csv, .xlsx">
                                        <label class="custom-file-label" for="salary_file">Choose file...</label>
                                    </div>
                                    <small class="text-muted">
                                        <a href="{{ route('tax-services.clients.export-salary', $client->id) }}">Download Input Template</a>
                                    </small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <button type="button" id="btnUploadSalary" class="btn btn-primary btn-block">
                                        <i class="fas fa-upload mr-1"></i> Upload & Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-3 border rounded bg-white">
                        <h6 class="font-weight-bold text-info border-bottom pb-2">
                            <i class="fas fa-calculator mr-1"></i> Step 2: Calculation & Confirmation
                        </h6>
                        <p class="mt-2 text-muted small">
                            After uploading data, click below to preview calculations. You can verify and manually adjust the "Actual Deduction" before saving the month.
                        </p>
                        
                        <div class="mt-3">
                            <button type="button" id="btnPreviewCalculation" class="btn btn-info btn-lg shadow-sm">
                                <i class="fas fa-eye mr-2"></i> Preview Calculation
                            </button>
                            
                            <a href="{{ route('tax-services.clients.generate-sheet', $client->id) }}" class="btn btn-secondary btn-lg shadow-sm ml-2">
                                <i class="fas fa-history mr-2"></i> View History
                            </a>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="reports" role="tabpanel">
                    <h5 class="font-weight-bold text-gray-800 mb-3">Available Reports</h5>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Projection</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Tax Projection Report</div>
                                            <a href="{{ route('tax-services.clients.projection', $client->id) }}" class="btn btn-primary btn-sm mt-2">View Report</a>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Downloads</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Monthly Deduction CSV</div>
                                            <a href="{{ route('tax-services.reports.tax-deduction-csv', $client->id) }}" class="btn btn-success btn-sm mt-2">Download CSV</a>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-file-csv fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="settings" role="tabpanel">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-gray-800 border-bottom pb-2">General Configuration</h5>
                            <form action="{{ route('tax-services.clients.settings', $client->id) }}" method="POST">
                                @csrf
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <label>Company Name</label>
                                        <input type="text" name="company_name" class="form-control" value="{{ $client->company_name }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>TRN</label>
                                        <input type="text" name="trn_number" class="form-control" value="{{ $client->trn_number }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tax Year Start</label>
                                        <input type="date" name="tax_year_start" class="form-control" value="{{ $client->tax_year_start }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tax Year End</label>
                                        <input type="date" name="tax_year_end" class="form-control" value="{{ $client->tax_year_end }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                                <a href="{{ route('tax-services.clients.unlock-onboarding', $client->id) }}" class="btn btn-warning ml-2">Unlock Onboarding</a>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="font-weight-bold text-gray-800 border-bottom pb-2">Salary Components</h5>
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr><th>Name</th><th>Type</th><th>Taxable</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($client->components as $c)
                                    <tr>
                                        <td>{{ $c->name }}</td>
                                        <td>{{ ucfirst($c->type) }}</td>
                                        <td>{{ $c->is_taxable ? 'Yes' : 'No' }}</td>
                                        <td>
                                            <form action="{{ route('tax-services.components.destroy', [$client->id, $c->id]) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm py-0">x</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <form action="{{ route('tax-services.components.store', $client->id) }}" method="POST" class="form-inline">
                                @csrf
                                <input type="text" name="name" class="form-control form-control-sm mr-2" placeholder="Component Name" required>
                                <select name="type" class="form-control form-control-sm mr-2">
                                    <option value="allowance">Allowance</option>
                                    <option value="deduction">Deduction</option>
                                </select>
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input" id="isTaxable2" name="is_taxable" value="1" checked>
                                    <label class="custom-control-label" for="isTaxable2">Taxable</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Add Component</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div> </div>
    </div>

</div>

<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('tax-services.employees.store', $client->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Ref ID</label><input type="text" name="employee_ref_id" class="form-control" required></div>
                    <div class="form-group"><label>Designation</label><input type="text" name="designation" class="form-control"></div>
                    <div class="form-group"><label>Status</label><select name="tax_status" class="form-control"><option value="taxable">Taxable</option><option value="exempt">Exempt</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="importEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('tax-services.employees.import', $client->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Import</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><input type="file" name="file" class="form-control-file" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Import</button></div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="taxPreviewModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;"> 
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Tax Preview & Confirmation</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Back to Editing</button>
                <button type="button" class="btn btn-success" id="btnConfirmSave">
                    <i class="fas fa-check-circle mr-1"></i> Confirm & Save All
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    
    // File Input Label Fix
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    // 1. Upload Salary
    $('#btnUploadSalary').click(function() {
        let formData = new FormData($('#salaryInputForm')[0]);
        if(!$('#salary_file').val()) { alert('Please select a file.'); return; }

        $.ajax({
            url: "{{ route('tax-services.clients.bulk-update-salary', $client->id) }}",
            type: "POST", data: formData, processData: false, contentType: false,
            beforeSend: function() { $('#btnUploadSalary').html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true); },
            success: function() { 
                alert('Uploaded successfully!'); 
                $('#btnUploadSalary').html('<i class="fas fa-upload mr-1"></i> Upload & Update').prop('disabled', false); 
                $('#salary_file').val('');
            },
            error: function(xhr) { 
                alert('Error: ' + (xhr.responseJSON.message || 'Unknown')); 
                $('#btnUploadSalary').html('<i class="fas fa-upload mr-1"></i> Upload & Update').prop('disabled', false);
            }
        });
    });

    // 2. Preview Calculation
    $('#btnPreviewCalculation').click(function() {
        let formData = new FormData($('#salaryInputForm')[0]);
        $.ajax({
            url: "{{ route('tax-services.clients.preview-calculation', $client->id) }}",
            type: "POST", data: formData, processData: false, contentType: false,
            beforeSend: function() { 
                $('#previewModalBody').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Calculating...</p></div>'); 
                $('#taxPreviewModal').modal('show'); 
            },
            success: function(response) { renderPreviewTable(response); },
            error: function() { alert('Error calculating.'); $('#taxPreviewModal').modal('hide'); }
        });
    });

    // 3. Render Table (With Editable Column & Defaults)
    function renderPreviewTable(data) {
        let employees = data.employees;
        let inputColumns = data.input_columns || ['Gross Salary'];

        let thead = `
            <thead>
                <tr>
                    <th rowspan="2" class="align-middle bg-secondary text-white">Employee</th>
                    <th colspan="${inputColumns.length}" class="text-center bg-light font-weight-bold text-dark border-bottom-0">Monthly Input</th>
                    <th colspan="3" class="text-center bg-dark text-white">Annual Estimates</th>
                    <th rowspan="2" class="align-middle bg-secondary text-white">YTD Tax Paid</th>
                    <th colspan="2" class="text-center text-white" style="background-color: #007bff;">Current Month Tax</th>
                </tr>
                <tr>
                    ${inputColumns.map(col => `<th class="bg-light text-muted small border-top-0" style="min-width:80px;">${col}</th>`).join('')}
                    <th class="bg-dark text-white small">Total Salary</th>
                    <th class="bg-dark text-white small">Taxable</th>
                    <th class="bg-dark text-white small">Total Tax</th>
                    <th class="text-white" style="background-color: #17a2b8;">Chargeable</th> 
                    <th class="text-dark" style="background-color: #ffc107;">Actual Deduction</th>
                </tr>
            </thead>
        `;

        let tbody = '<tbody>';
        employees.forEach(function(emp) {
            let inputCells = '';
            if(emp.inputs && typeof emp.inputs === 'object') {
                Object.values(emp.inputs).forEach(val => { inputCells += `<td>${parseFloat(val).toLocaleString()}</td>`; });
            } else {
                inputCells = `<td>${parseFloat(emp.current_gross || 0).toLocaleString()}</td>`;
            }

            let monthlyTaxVal = parseFloat(emp.monthly_tax || 0);

            tbody += `
                <tr>
                    <td class="font-weight-bold text-nowrap">${emp.name}</td>
                    ${inputCells}
                    <td class="text-right">${parseFloat(emp.annual_salary).toLocaleString()}</td>
                    <td class="text-right">${parseFloat(emp.annual_taxable).toLocaleString()}</td>
                    <td class="text-right">${parseFloat(emp.annual_tax).toLocaleString()}</td>
                    <td class="font-weight-bold text-right">${parseFloat(emp.tax_paid_ytd).toLocaleString()}</td>
                    
                    <td class="font-weight-bold text-right text-white" style="background-color: #17a2b8;">
                        ${monthlyTaxVal.toLocaleString(undefined, {minimumFractionDigits: 2})}
                    </td>
                    
                    <td class="p-1" style="background-color: #fff3cd;">
                        <input type="number" step="0.01" 
                               class="form-control form-control-sm font-weight-bold text-right actual-tax-input" 
                               data-employee-id="${emp.id}"
                               value="${monthlyTaxVal.toFixed(2)}" 
                               style="min-width: 100px;">
                    </td>
                </tr>
            `;
        });
        tbody += '</tbody>';

        $('#previewModalBody').html(`<div class="table-responsive" style="max-height: 60vh; overflow-y: auto;"><table class="table table-bordered table-sm table-striped mb-0">${thead}${tbody}</table></div>`);
    }

    // 4. Confirm & Save
    $('#btnConfirmSave').click(function() {
        let taxOverrides = {};
        $('.actual-tax-input').each(function() {
            taxOverrides[$(this).data('employee-id')] = $(this).val();
        });

        $.ajax({
            url: "{{ route('tax-services.sheet.finalize', ['sheet' => 'NEW']) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                client_id: "{{ $client->id }}",
                month: $('#salaryMonth').val(),
                overrides: taxOverrides
            },
            beforeSend: function() { $('#btnConfirmSave').html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true); },
            success: function(res) { 
                $('#taxPreviewModal').modal('hide'); 
                if(res.redirect_url) window.location.href = res.redirect_url; else location.reload();
            },
            error: function() { alert('Error saving.'); $('#btnConfirmSave').html('Confirm & Save All').prop('disabled', false); }
        });
    });

});
</script>
@endsection