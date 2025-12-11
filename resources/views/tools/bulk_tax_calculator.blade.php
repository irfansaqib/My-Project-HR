@extends('layouts.admin')
@section('title', 'Bulk Tax Calculator')

@section('content')
<div class="container-fluid">
    
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-upload" data-toggle="pill" href="#content-upload" role="tab">
                        <i class="fas fa-file-excel mr-1"></i> Upload Excel/CSV
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-manual" data-toggle="pill" href="#content-manual" role="tab">
                        <i class="fas fa-keyboard mr-1"></i> Manual Entry
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            
            {{-- Shared Tax Year Selection --}}
            <div class="row mb-4 p-3 bg-light rounded">
                <div class="col-md-4">
                    <label class="mb-1">Select Tax Year <span class="text-danger">*</span></label>
                    <select name="tax_year" form="form-upload" class="form-control" required>
                        @foreach($taxYears as $year => $label)
                            <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <small class="text-muted">
                        <i class="fas fa-info-circle text-info"></i> 
                        The system will fetch Tax Slabs from your database applicable for the selected financial year.
                    </small>
                </div>
            </div>

            <div class="tab-content">
                
                {{-- TAB 1: Upload --}}
                <div class="tab-pane fade show active" id="content-upload" role="tabpanel">
                    <form action="{{ route('tools.bulk-tax.process') }}" method="POST" enctype="multipart/form-data" id="form-upload">
                        @csrf
                        
                        <div class="alert alert-info">
                            <h5>Instructions</h5>
                            <ul>
                                <li>Download the template below.</li>
                                <li><strong>"One-Time Bonus"</strong>: Enter any annual bonus here. This amount is added directly to annual income (Flat Taxable).</li>
                                <li><strong>"YTD Income" / "Tax Paid YTD"</strong>: Use these columns for mid-year joiners or reconciliation.</li>
                            </ul>
                            <a href="{{ route('tools.bulk-tax.template') }}" class="btn btn-outline-light text-dark border-dark mt-2">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>

                        <div class="form-group">
                            <label>Upload File (.csv)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file" required accept=".csv">
                                <label class="custom-file-label">Choose file</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Calculate Tax</button>
                    </form>
                </div>

                {{-- TAB 2: Manual Entry --}}
                <div class="tab-pane fade" id="content-manual" role="tabpanel">
                    <form action="{{ route('tools.bulk-tax.process') }}" method="POST" id="form-manual">
                        @csrf
                        <input type="hidden" name="tax_year" id="manual_tax_year">

                        <div id="manual-rows">
                            {{-- Template Row --}}
                            <div class="card mb-3 manual-row border-left-primary position-relative">
                                
                                {{-- ✅ DELETE BUTTON (Top Right) --}}
                                <button type="button" class="btn btn-xs btn-danger position-absolute" 
                                        style="top: 10px; right: 10px; z-index: 10;" 
                                        onclick="removeRow(this)" title="Remove Row">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <div class="card-body bg-light pt-4"> {{-- Added pt-4 to clear button space --}}
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label>Name</label>
                                            <input type="text" name="manual_data[0][name]" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>CNIC</label>
                                            <input type="text" name="manual_data[0][cnic]" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>Current Monthly Basic</label>
                                            <input type="number" name="manual_data[0][basic]" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>One-Time Bonus</label>
                                            <input type="number" name="manual_data[0][bonus]" class="form-control form-control-sm" value="0">
                                        </div>
                                        
                                        <div class="col-12"><hr class="my-2"> <small class="text-primary font-weight-bold">Reconciliation (Optional):</small></div>
                                        
                                        <div class="col-md-4 mb-2">
                                            <label class="small">YTD Taxable Income</label>
                                            <input type="number" name="manual_data[0][ytd_income]" class="form-control form-control-sm" value="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="small">Tax Paid YTD</label>
                                            <input type="number" name="manual_data[0][tax_paid_ytd]" class="form-control form-control-sm" value="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="small">Months Passed</label>
                                            <input type="number" name="manual_data[0][months_passed]" class="form-control form-control-sm" value="0" min="0" max="11">
                                        </div>
                                        
                                        <div class="col-12"><hr class="my-2"> <small class="text-muted font-weight-bold">Monthly Allowances:</small></div>
                                        @foreach($allowances as $comp)
                                            <div class="col-md-3">
                                                <label class="small">{{ $comp->name }}</label>
                                                <input type="number" name="manual_data[0][allowances][{{ $comp->id }}]" class="form-control form-control-sm" value="0">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm" onclick="addRow()"><i class="fas fa-plus"></i> Add Row</button>
                            <button type="submit" class="btn btn-primary btn-sm">Calculate All</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Sync Tax Year
    $('#form-manual').on('submit', function() {
        $('#manual_tax_year').val($('select[name="tax_year"]').val());
    });

    let rowIdx = 0;

    function addRow() {
        rowIdx++;
        // Clone the FIRST row template
        let template = $('.manual-row').first().clone();
        
        // Reset inputs and update names
        template.find('input').each(function() {
            let name = $(this).attr('name');
            if(name) {
                // Replace [0] with [newIndex]
                let newName = name.replace(/manual_data\[\d+\]/, 'manual_data[' + rowIdx + ']');
                $(this).attr('name', newName);
                
                // Reset values (Default 0 for numbers, empty for text)
                $(this).val($(this).attr('type') == 'number' ? 0 : '');
            }
        });
        
        $('#manual-rows').append(template);
    }

    // ✅ Remove Row Function
    function removeRow(button) {
        // Prevent deleting the last remaining row
        if ($('.manual-row').length > 1) {
            $(button).closest('.manual-row').remove();
        } else {
            alert("You must have at least one row.");
        }
    }
    
    $('.custom-file-input').on('change', function() { 
        let fileName = $(this).val().split('\\').pop(); 
        $(this).next('.custom-file-label').addClass("selected").html(fileName); 
    });
</script>
@endpush
@endsection