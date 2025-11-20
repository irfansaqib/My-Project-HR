@php
    $isEdit = isset($revision); // Are we in 'edit' mode?

    // "Current" is always the last approved salary
    $currentBasic = $currentBasicSalary ?? 0;
    
    // "Updated" is the value from the revision we are editing, or fallback to current
    $updatedBasic = $revisionBasic ?? $currentBasic;

    // "Updated" component values (for edit mode)
    $updatedComponents = $revisionComponents ?? collect();
@endphp

{{-- ✅ ADDED: Custom styles for the improved table layout --}}
@push('styles')
<style>
    .salary-table th {
        width: 33.33%;
    }
    .salary-table th:first-child, .salary-table td:first-child {
        width: 34%;
        text-align: left;
    }
    .salary-table th:nth-child(2), .salary-table td:nth-child(2) {
        width: 33%;
        text-align: right;
    }
    .salary-table th:nth-child(3), .salary-table td:nth-child(3) {
        width: 33%;
        text-align: right;
    }
    .salary-table input.form-control {
        text-align: right;
    }
    .salary-table .current-component {
        vertical-align: middle;
    }
    .salary-table tfoot th, .salary-table tfoot td {
        vertical-align: middle;
        font-size: 1.1rem; /* Make totals slightly larger */
    }
    .table-header-group {
        background-color: #f8f9fa;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endpush

<form action="{{ $isEdit ? route('employees.revisions.update', [$employee->id, $revision->id]) : route('employees.revisions.store', $employee->id) }}" 
      method="POST" id="revision-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- HEADER --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1">{{ $isEdit ? 'Edit Salary Revision' : 'New Salary Revision' }}</h3>
                    <p class="mb-0 text-muted">
                        Employee: <strong>{{ $employee->name }}</strong><br>
                        {{-- ✅ *** N/A BUG FIX *** --}}
                        {{-- Access designation/department as simple string properties --}}
                        Designation / Department:
                        <strong>{{ $employee->designation ?? 'N/A' }} / {{ $employee->department ?? 'N/A' }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <label for="effective_date" class="mb-0"><strong>Effective Date</strong></label>
                    <input type="date" name="effective_date" id="effective_date"
                           class="form-control mt-1"
                           value="{{ \Carbon\Carbon::parse($isEdit ? $revision->effective_date : now())->format('Y-m-d') }}" required>
                </div>
            </div>
        </div>
    </div>

    {{-- SALARY TABLE --}}
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Salary Structure</h3>
            <a href="{{ route('tools.taxCalculator', $employee) }}" target="_blank" class="btn btn-outline-info btn-sm">
                <i class="fas fa-calculator mr-1"></i> Open Tax Calculator
            </a>
        </div>
        <div class="card-body table-responsive p-0">
            {{-- ✅ REFORMATTED TABLE --}}
            <table class="table table-bordered align-middle mb-0 salary-table">
                <thead class="thead-light">
                    <tr>
                        <th class="text-left">Component</th>
                        <th class="text-right">Current (Rs)</th>
                        <th class="text-right">Updated (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- BASIC SALARY --}}
                    <tr>
                        <td class="text-left"><strong>Basic Salary</strong></td>
                        <td id="current-basic" data-current-basic="{{ $currentBasic }}" class="text-muted text-right">
                            {{ number_format($currentBasic, 2) }}
                        </td>
                        <td>
                            <input type="number" name="new_basic_salary" id="basic_salary_input"
                                   class="form-control text-right salary-input"
                                   value="{{ $updatedBasic }}" step="0.01" required>
                        </td>
                    </tr>

                    {{-- ALLOWANCES --}}
                    <tr class="table-header-group"><td colspan="3" class="text-left p-2">Allowances</td></tr>
                    @foreach($components->where('type', 'allowance') as $component)
                        @php
                            $currentAmount = $component['amount'] ?? 0;
                            $updatedAmount = $updatedComponents->get($component['id'])['amount'] ?? $currentAmount;
                        @endphp
                        <tr>
                            <td class="text-left">{{ ucfirst($component['name']) }}</td>
                            <td class="current-component text-muted text-right" data-current-amount="{{ $currentAmount }}">
                                {{ number_format($currentAmount, 2) }}
                            </td>
                            <td>
                                <input type="number" step="0.01" name="components[{{ $component['id'] }}]"
                                       class="form-control text-right salary-input allowance" 
                                       data-component-type="allowance"
                                       value="{{ $updatedAmount }}">
                            </td>
                        </tr>
                    @endforeach

                    {{-- DEDUCTIONS --}}
                    <tr class="table-header-group"><td colspan="3" class="text-left p-2">Deductions</td></tr>
                    @foreach($components->where('type', 'deduction') as $component)
                        @php
                            $currentAmount = $component['amount'] ?? 0;
                            $updatedAmount = $updatedComponents->get($component['id'])['amount'] ?? $currentAmount;
                        @endphp
                        <tr>
                            <td class="text-left">{{ ucfirst($component['name']) }}</td>
                            <td class="current-component text-danger text-muted text-right" data-current-amount="{{ $currentAmount }}">
                                ({{ number_format($currentAmount, 2) }})
                            </td>
                            <td>
                                <input type="number" step="0.01" name="components[{{ $component['id'] }}]"
                                       class="form-control text-right salary-input deduction" 
                                       data-component-type="deduction"
                                       value="{{ $updatedAmount }}">
                            </td>
                        </tr>
                    @endforeach
                    
                </tbody>
                <tfoot>
                    {{-- ✅ REFORMATTED TOTALS FOOTER --}}
                    <tr class="bg-light font-weight-bold">
                        <th class="text-left p-3">Gross Salary</th>
                        <td id="current-gross-salary" class="text-success text-right align-middle">{{ number_format($currentGross ?? 0, 2) }}</td>
                        <td id="updated-gross-salary" class="text-success text-right align-middle">{{-- JS will fill this --}}</td>
                    </tr>
                    <tr class="bg-dark text-white font-weight-bold">
                        <th class="text-left p-3">Net Salary</th>
                        <td id="current-net-salary" class="text-right align-middle">{{ number_format($currentNet ?? 0, 2) }}</td>
                        <td id="updated-net-salary" class="text-right align-middle">{{-- JS will fill this --}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('employees.revisions.index', $employee->id) }}" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Update Revision' : 'Submit for Approval' }}
        </button>
    </div>
</form>

{{-- SCRIPT --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fmt = n => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
    const parseNum = v => parseFloat(String(v).replace(/[(),]/g, '')) || 0;

    const basicInput = document.getElementById('basic_salary_input');
    const currentBasic = parseNum(document.getElementById('current-basic').dataset.currentBasic);
    
    // ✅ Get all total cells
    const currentGrossEl = document.getElementById('current-gross-salary');
    const currentNetEl = document.getElementById('current-net-salary');
    const updatedGrossEl = document.getElementById('updated-gross-salary');
    const updatedNetEl = document.getElementById('updated-net-salary');

    function recalcTotals() {
        // 1. Get Updated Basic
        const updatedBasic = parseNum(basicInput.value);
        
        // 2. Initialize all totals
        let currentAllowances = 0, currentDeductions = 0;
        let updatedAllowances = 0, updatedDeductions = 0;

        // 3. Loop through all component inputs
        document.querySelectorAll('.salary-input').forEach(el => {
            // Find the 'current-component' cell in the same row
            const currentRow = el.closest('tr');
            const currentComponentCell = currentRow.querySelector('.current-component');
            
            if (currentComponentCell) {
                const currentAmount = parseNum(currentComponentCell.dataset.currentAmount);
                const updatedAmount = parseNum(el.value);
                const type = el.dataset.componentType;

                if (type === 'allowance') {
                    currentAllowances += currentAmount;
                    updatedAllowances += updatedAmount;
                } else if (type === 'deduction') {
                    currentDeductions += currentAmount;
                    updatedDeductions += updatedAmount;
                }
            }
        });

        // 4. Calculate all 4 totals
        const currentGross = currentBasic + currentAllowances;
        const updatedGross = updatedBasic + updatedAllowances;
        const currentNet = currentGross - currentDeductions;
        const updatedNet = updatedGross - updatedDeductions;

        // 5. Update the 4 cells in the footer
        currentGrossEl.textContent = fmt(currentGross);
        currentNetEl.textContent = fmt(currentNet);
        updatedGrossEl.textContent = fmt(updatedGross);
        updatedNetEl.textContent = fmt(updatedNet);
    }

    // Recalculate when any salary input changes
    document.querySelectorAll('.salary-input').forEach(el => el.addEventListener('input', recalcTotals));

    // Initial calculation on page load
    recalcTotals();
});
</script>
@endpush