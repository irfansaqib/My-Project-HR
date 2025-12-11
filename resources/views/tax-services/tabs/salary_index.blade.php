@extends('layouts.tax_client')

@section('tab-content')

<style>
    /* STRICT LAYOUT FOR EQUAL WIDTHS */
    .table-fixed-layout { 
        table-layout: fixed; 
        width: 100%; 
        min-width: 1400px; /* Forces scroll on small screens */ 
    }
    
    .table-fixed-layout th, .table-fixed-layout td {
        vertical-align: middle !important;
        text-align: center !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        width: 10% !important; /* Force equal width */
        font-size: 0.9rem;
        padding: 5px !important;
    }
    
    /* Specific Column Widths */
    .col-emp { width: 15% !important; }
    .col-money { width: 10% !important; }
    .col-total { width: 11% !important; }
    .col-tax { width: 11% !important; }
    .col-action { width: 12% !important; }

    .table-input input { text-align: right; }
    
    /* Read Only Styling */
    input:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        border: 1px solid #dee2e6;
        cursor: not-allowed;
    }
</style>

{{-- 1. HEADER --}}
<div class="row">
    <div class="col-12">
        <div class="card bg-light border-0 mb-4 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark">Payroll & Tax History</h5>
                    <p class="mb-0 small text-muted">Record monthly salary inputs.</p>
                </div>
                <div>
                    @if($client->is_onboarded)
                        {{-- NEW YEAR ROLLOVER BUTTON --}}
                        <a href="{{ route('tax-services.clients.new-year', $client->id) }}" class="btn btn-outline-danger shadow-sm font-weight-bold mr-2">
                            <i class="fas fa-calendar-alt mr-1"></i> Start New Tax Year
                        </a>

                        @if($hasPendingDraft)
                             <button class="btn btn-warning shadow-sm font-weight-bold" onclick="openInputModal('{{ $nextPayrollMonth->format('Y-m') }}', true, false)">
                                <i class="fas fa-edit mr-1"></i> Resume Draft ({{ $nextPayrollMonth->format('M Y') }})
                            </button>
                        @else
                             <button class="btn btn-primary shadow-sm font-weight-bold" onclick="openInputModal('{{ $nextPayrollMonth ? $nextPayrollMonth->format('Y-m') : '' }}', false, false)">
                                <i class="fas fa-plus-circle mr-1"></i> Start Month ({{ $nextPayrollMonth ? $nextPayrollMonth->format('M Y') : 'N/A' }})
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. LIST --}}
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Month</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Total Gross</th>
                            <th class="text-right">Tax Recorded</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->salarySheets as $sheet)
                        <tr>
                            <td class="font-weight-bold text-center">{{ $sheet->month->format('F, Y') }}</td>
                            <td class="text-center">
                                @if($sheet->status == 'finalized') <span class="badge badge-success">Finalized</span>
                                @else <span class="badge badge-warning">Draft</span> @endif
                            </td>
                            <td class="text-right">{{ number_format($sheet->items->sum('gross_salary')) }}</td>
                            <td class="text-right">{{ number_format($sheet->items->sum('income_tax')) }}</td>
                            <td class="text-center">
                                @if($sheet->status == 'draft')
                                    <button class="btn btn-sm btn-primary" onclick="openInputModal('{{ $sheet->month->format('Y-m') }}', true, false)">
                                        <i class="fas fa-pen"></i> Edit / Finalize
                                    </button>
                                @else
                                    {{-- VIEW BUTTON --}}
                                    <button class="btn btn-sm btn-secondary" onclick="viewInput('{{ $sheet->month->format('Y-m') }}')">
                                        <i class="fas fa-eye"></i> View Input
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 3. MODAL --}}
<div class="modal fade" id="monthlyInputModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 98%;">
        <form id="monthlyInputForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <span class="font-weight-bold mr-2">{{ $client->name }}</span> | 
                        Salary Input: <span id="modalMonthLabel" class="text-warning ml-1"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body bg-light">
                    <input type="hidden" name="context_month" id="modalMonthInput">
                    
                    {{-- INPUT VIEW --}}
                    <div id="inputView">
                        <div class="table-responsive bg-white p-0" style="max-height: 65vh; overflow-y: auto; overflow-x: auto;">
                            <table class="table table-bordered table-sm mb-0 table-fixed-layout table-input">
                                <thead class="thead-dark sticky-top">
                                    <tr>
                                        <th class="col-emp">Employee / CNIC</th>
                                        <th class="col-money">Basic Salary</th>
                                        @php $allowanceNames = $client->components->where('type', 'allowance')->pluck('name')->toArray(); @endphp
                                        @foreach($allowanceNames as $name) <th class="col-money">{{ $name }}</th> @endforeach
                                        <th class="col-money">Bonus</th>
                                        <th class="col-total bg-info text-white">Total Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->employees->where('status', 'active') as $emp)
                                    <tr class="emp-row" data-id="{{ $emp->id }}">
                                        <td class="font-weight-bold text-left">
                                            {{ $emp->name }} <br> <small class="text-muted">{{ $emp->cnic }}</small>
                                        </td>
                                        
                                        {{-- BASIC SALARY (Added calc-input) --}}
                                        <td><input type="number" step="0.01" class="form-control form-control-sm text-right calc-input" name="employees[{{ $emp->id }}][current_basic_salary]" value="{{ $emp->current_basic_salary }}"></td>
                                        
                                        {{-- ALLOWANCES (Added calc-input) --}}
                                        @foreach($allowanceNames as $name)
                                        @php 
                                            $savedAllowances = is_array($emp->current_allowances) ? $emp->current_allowances : json_decode($emp->current_allowances ?? '[]', true);
                                        @endphp
                                        <td><input type="number" step="0.01" class="form-control form-control-sm text-right calc-input" name="employees[{{ $emp->id }}][allowances][{{ $name }}]" value="{{ $savedAllowances[$name] ?? 0 }}"></td>
                                        @endforeach
                                        
                                        {{-- BONUS (Added calc-input) --}}
                                        <td><input type="number" step="0.01" class="form-control form-control-sm text-right calc-input" name="employees[{{ $emp->id }}][current_bonus]" value="{{ $emp->current_bonus ?? 0 }}"></td>
                                        
                                        {{-- TOTAL SALARY (Calculated) --}}
                                        <td><input type="text" class="form-control form-control-sm text-right font-weight-bold bg-light total-salary-display" readonly value="0"></td>
                                        
                                        <input type="hidden" class="manual-tax-hidden" name="employees[{{ $emp->id }}][manual_tax_deduction]">
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PREVIEW VIEW --}}
                    <div id="previewView" style="display:none;">
                        <h5 class="text-center text-primary mb-3">Review & Confirm Tax</h5>
                        <div class="table-responsive bg-white p-0" style="max-height: 65vh; overflow-y: auto; overflow-x: auto;">
                            <table class="table table-bordered table-sm mb-0 table-fixed-layout" id="previewTable">
                                <thead class="thead-light sticky-top"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" id="btnBack" style="display:none;"><i class="fas fa-arrow-left mr-1"></i> Back</button>
                    <button type="button" class="btn btn-light border" data-dismiss="modal" id="btnClose">Close</button>
                    
                    <button type="button" class="btn btn-info shadow-sm font-weight-bold" id="btnPreview"><i class="fas fa-calculator mr-1"></i> Preview Calculation</button>
                    <button type="button" class="btn btn-success shadow-sm font-weight-bold" id="btnSaveDraft"><i class="fas fa-save mr-1"></i> Save Draft</button>
                    <button type="button" class="btn btn-dark shadow-sm font-weight-bold" id="btnFinalize" style="display:none;"><i class="fas fa-check-double mr-1"></i> Finalize & Lock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="allowance-names-json" style="display:none;">{{ json_encode($allowanceNames) }}</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function formatNum(num) {
        return parseFloat(num || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }

    // --- TOTAL SALARY CALCULATION ---
    function recalculateRowTotal(row) {
        let total = 0;
        // Sum any input with class 'calc-input' in this row
        row.find('.calc-input').each(function() {
            let val = parseFloat($(this).val());
            if(!isNaN(val)) total += val;
        });
        row.find('.total-salary-display').val(formatNum(total));
    }

    $(document).on('keyup change input', '.calc-input', function() {
        recalculateRowTotal($(this).closest('tr'));
    });

    // --- RENDER TABLE ---
    function renderPreviewTable(data) {
        let allowanceNames = JSON.parse($('#allowance-names-json').text());
        
        let theadHtml = `<tr>
            <th class="col-emp">Employee / CNIC</th>
            <th class="col-money">Basic</th>`;
        
        allowanceNames.forEach(name => { theadHtml += `<th class="col-money">${name}</th>`; });

        theadHtml += `
            <th class="col-money">Bonus</th>
            <th class="col-total bg-info text-white">Total Salary</th>
            <th class="col-tax bg-primary text-white">Est. Annual Tax</th>
            <th class="col-tax bg-secondary text-white">Tax Paid YTD</th>
            <th class="col-tax bg-success text-white">Tax Chargeable</th>
            <th class="col-action bg-warning text-dark border-warning">Actual Tax (Edit)</th>
        </tr>`;
        
        $('#previewTable thead').html(theadHtml);

        let tbody = $('#previewTable tbody');
        tbody.empty();
        
        $.each(data, function(id, row){
            // Use saved manual tax if available, otherwise system calculation
            let actualTax = (row.manual_tax_deduction !== null && row.manual_tax_deduction !== undefined) 
                            ? row.manual_tax_deduction 
                            : row.system_monthly_tax;
            
            let tr = `<tr>
                <td class="font-weight-bold text-left">${row.name}<br><small class="text-muted">${row.cnic}</small></td>
                <td class="text-right">${formatNum(row.current_basic)}</td>`;
            
            allowanceNames.forEach(name => {
                let val = (row.current_allowances && row.current_allowances[name]) ? row.current_allowances[name] : 0;
                tr += `<td class="text-right text-muted">${formatNum(val)}</td>`;
            });

            tr += `
                <td class="text-right text-success">${formatNum(row.current_bonus)}</td>
                <td class="text-right font-weight-bold bg-light">${formatNum(row.current_gross)}</td>
                <td class="text-right text-primary font-weight-bold">${formatNum(row.est_annual_tax)}</td>
                <td class="text-right font-weight-bold">${formatNum(row.tax_paid_ytd)}</td>
                <td class="text-right bg-success text-white font-weight-bold">${formatNum(row.system_monthly_tax)}</td>
                <td class="p-1">
                    <input type="number" step="0.01" class="form-control form-control-sm text-center font-weight-bold text-danger manual-tax-input"
                        data-id="${id}" value="${parseFloat(actualTax).toFixed(0)}" ${$('#monthlyInputForm input').prop('disabled') ? 'disabled' : ''}>
                </td>
            </tr>`;
            
            tbody.append(tr);
        });
    }

    // --- OPEN MODAL (EDIT MODE) ---
    window.openInputModal = function(month, isEdit, isReadOnly) {
        $('#modalMonthInput').val(month);
        let dateObj = new Date(month + "-01");
        let dateStr = dateObj.toLocaleString('default', { month: 'long', year: 'numeric' });
        $('#modalMonthLabel').text(dateStr);
        
        // Reset Views
        $('#inputView').show(); $('#previewView').hide();
        $('#btnBack').hide(); $('#btnClose').show();
        
        // Enable Controls for Edit
        $('#monthlyInputForm input').prop('disabled', false); 
        $('.total-salary-display').prop('readonly', true);
        $('#btnPreview, #btnSaveDraft').show(); 
        $('#btnFinalize').hide(); 

        $('#monthlyInputForm')[0].reset();
        $('.emp-row').each(function(){ recalculateRowTotal($(this)); });

        if(isEdit) {
            $.get("{{ route('tax-services.clients.get-month-data', $client->id) }}?month=" + month, function(res){
                if(res.status === 'found') {
                    $.each(res.data, function(empId, values){
                        let row = $('.emp-row[data-id="'+empId+'"]');
                        row.find('input[name*="[current_basic_salary]"]').val(values.basic);
                        row.find('input[name*="[current_bonus]"]').val(values.bonus);
                        
                        if(values.manual_tax !== null) {
                            row.find('.manual-tax-hidden').val(values.manual_tax); 
                        }

                        $.each(values.allowances, function(name, val){
                            row.find('input[name*="[allowances]['+name+']"]').val(val);
                        });
                        recalculateRowTotal(row);
                    });
                }
            });
        }
        $('#monthlyInputModal').modal('show');
    };

    // --- VIEW INPUT (READ ONLY PREVIEW) ---
    window.viewInput = function(month) {
        $('#modalMonthInput').val(month);
        let dateObj = new Date(month + "-01");
        $('#modalMonthLabel').text(dateObj.toLocaleString('default', { month: 'long', year: 'numeric' }) + " (Finalized)");

        // 1. Fetch saved data
        $.get("{{ route('tax-services.clients.get-month-data', $client->id) }}?month=" + month, function(res){
            if(res.status === 'found') {
                // 2. Populate form invisibly for calculation
                $('#monthlyInputForm')[0].reset();
                $.each(res.data, function(empId, values){
                    let row = $('.emp-row[data-id="'+empId+'"]');
                    row.find('input[name*="[current_basic_salary]"]').val(values.basic);
                    row.find('input[name*="[current_bonus]"]').val(values.bonus);
                    
                    // CRITICAL: Set the hidden tax input to the SAVED value
                    if(values.manual_tax !== null) {
                        row.find('.manual-tax-hidden').val(values.manual_tax); 
                    }

                    $.each(values.allowances, function(name, val){
                        row.find('input[name*="[allowances]['+name+']"]').val(val);
                    });
                });

                // 3. Trigger Calculation Immediately
                $.post("{{ route('tax-services.clients.preview-calculation', $client->id) }}", $('#monthlyInputForm').serialize())
                .done(function(resCalc){
                    renderPreviewTable(resCalc.calculations);
                    
                    // 4. Lock UI
                    $('#inputView').hide();
                    $('#previewView').show();
                    $('#btnPreview, #btnSaveDraft, #btnFinalize, #btnBack').hide();
                    $('#btnClose').show();
                    $('#monthlyInputForm input').prop('disabled', true); 
                    
                    $('#monthlyInputModal').modal('show');
                });
            }
        });
    };

    // --- ACTIONS ---
    $(document).on('click', '#btnPreview', function(e){
        e.preventDefault();
        let btn = $(this);
        let originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Calculating...');
        
        $.post("{{ route('tax-services.clients.preview-calculation', $client->id) }}", $('#monthlyInputForm').serialize())
        .done(function(res){
            renderPreviewTable(res.calculations);
            $('#inputView').hide(); 
            $('#previewView').show();
            
            // Show buttons for Edit mode
            if(!$('#monthlyInputForm input').first().prop('disabled')) {
                $('#btnPreview').hide(); 
                $('#btnSaveDraft').show(); 
                $('#btnFinalize').show(); 
                $('#btnBack').show();
            } else {
                $('#btnBack').show(); // Allow back even in read-only
            }
            btn.html(originalText);
        })
        .fail(function(){ 
            alert('Calculation Failed.'); 
            btn.html(originalText); 
        });
    });

    $(document).on('input', '.manual-tax-input', function(){
        let id = $(this).data('id');
        let val = $(this).val();
        $('.emp-row[data-id="'+id+'"]').find('.manual-tax-hidden').val(val);
    });

    $(document).on('click', '#btnBack', function(){
        $('#previewView').hide(); $('#inputView').show();
        if(!$('#monthlyInputForm input').first().prop('disabled')) {
            $('#btnBack').hide(); $('#btnFinalize').hide(); $('#btnPreview').show();
        } else {
            $('#btnBack').hide(); // In read-only, back button just hides itself, no other buttons show
        }
    });

    $(document).on('click', '#btnSaveDraft', function(e){
        e.preventDefault();
        let btn = $(this); let orig = btn.html(); btn.html('Saving...');
        $.post("{{ route('tax-services.clients.save-salary-draft', $client->id) }}", $('#monthlyInputForm').serialize())
        .done(function(res){ alert(res.message); location.reload(); })
        .fail(function(){ alert('Error Saving'); btn.html(orig); });
    });

    $(document).on('click', '#btnFinalize', function(e){
        e.preventDefault();
        if(!confirm("Are you sure? Once finalized, this month cannot be changed.")) return;
        let btn = $(this); let orig = btn.html(); btn.html('Finalizing...');
        $.post("{{ route('tax-services.clients.finalize-salary-input', $client->id) }}", $('#monthlyInputForm').serialize())
        .done(function(res){ alert(res.message); location.reload(); })
        .fail(function(){ alert('Error Finalizing'); btn.html(orig); });
    });
</script>
@endsection