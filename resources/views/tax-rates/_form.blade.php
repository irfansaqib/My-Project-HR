@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Tax Period</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="tax_year">Tax Year</label>
                <input type="number" name="tax_year" class="form-control" value="{{ old('tax_year', $taxRate->tax_year ?? now()->year + 1) }}" placeholder="e.g., 2025" required>
                <small class="form-text text-muted">Enter the year the tax period **ends**.</small>
            </div>
            <div class="col-md-4 form-group">
                <label for="effective_from_date">Effective From Date</label>
                <input type="date" name="effective_from_date" class="form-control" value="{{ old('effective_from_date', isset($taxRate) ? $taxRate->effective_from_date->format('Y-m-d') : '') }}" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="effective_to_date">Effective To Date</label>
                <input type="date" name="effective_to_date" class="form-control" value="{{ old('effective_to_date', isset($taxRate) ? ($taxRate->effective_to_date ? $taxRate->effective_to_date->format('Y-m-d') : '') : '') }}">
            </div>
        </div>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Tax Slabs</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Income From (PKR)</th>
                    <th>Income To (PKR)</th>
                    <th>Fixed Tax (PKR)</th>
                    <th>Tax Rate (%)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="slabs-container">
                @if(old('slabs', isset($taxRate) ? $taxRate->slabs : []))
                    @foreach(old('slabs', isset($taxRate) ? $taxRate->slabs : []) as $i => $slab)
                        <tr>
                            <td><input type="number" name="slabs[{{$i}}][income_from]" class="form-control" value="{{ $slab['income_from'] }}" required></td>
                            <td>
                                <input type="number" name="slabs[{{$i}}][income_to]" class="form-control" value="{{ $slab['income_to'] ?? '' }}">
                                @if ($loop->last)
                                <small class="form-text text-muted">Keep this field blank for the last slab.</small>
                                @endif
                            </td>
                            <td><input type="number" name="slabs[{{$i}}][fixed_tax_amount]" class="form-control" value="{{ $slab['fixed_tax_amount'] }}" required></td>
                            <td><input type="number" name="slabs[{{$i}}][tax_rate_percentage]" class="form-control" value="{{ $slab['tax_rate_percentage'] }}" required></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-slab-row">Remove</button></td>
                        </tr>
                    @endforeach
                @else
                 {{-- Default first row for create form --}}
                 <tr>
                    <td><input type="number" name="slabs[0][income_from]" class="form-control" value="0" required></td>
                    <td>
                        <input type="number" name="slabs[0][income_to]" class="form-control" value="600000">
                    </td>
                    <td><input type="number" name="slabs[0][fixed_tax_amount]" class="form-control" value="0" required></td>
                    <td><input type="number" name="slabs[0][tax_rate_percentage]" class="form-control" value="0" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-slab-row">Remove</button></td>
                </tr>
                @endif
            </tbody>
        </table>
        <button type="button" id="add-slab-row" class="btn btn-success mt-2">Add New Slab</button>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Additional Surcharge (For High Earners)</h3></div>
    <div class="card-body">
         <div class="row">
            <div class="col-md-6 form-group">
                <label for="surcharge_threshold">Surcharge Threshold (PKR)</label>
                <input type="number" name="surcharge_threshold" class="form-control" value="{{ old('surcharge_threshold', $taxRate->surcharge_threshold ?? '') }}" >
                <small class="form-text text-muted">Annual taxable income level above which surcharge applies.</small>
            </div>
            <div class="col-md-6 form-group">
                <label for="surcharge_rate_percentage">Surcharge Rate (%)</label>
                <input type="number" step="0.01" name="surcharge_rate_percentage" class="form-control" value="{{ old('surcharge_rate_percentage', $taxRate->surcharge_rate_percentage ?? 0) }}">
                <small class="form-text text-muted">The percentage of tax to be added as a surcharge.</small>
            </div>
        </div>
    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">Save Tax Rates</button>
    {{-- THIS BUTTON HAS BEEN ADDED --}}
    <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Cancel</a>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let slabIndex = {{ count(old('slabs', isset($taxRate) ? $taxRate->slabs : [1])) }};

    function updateLastRowNote() {
        const container = document.getElementById('slabs-container');
        container.querySelectorAll('.form-text').forEach(note => note.remove());
        const lastRow = container.querySelector('tr:last-child');
        if (lastRow) {
            const incomeToCell = lastRow.querySelector('input[name*="[income_to]"]').parentElement;
            const note = document.createElement('small');
            note.className = 'form-text text-muted';
            note.textContent = 'Keep this field blank for the last slab.';
            incomeToCell.appendChild(note);
        }
    }

    document.getElementById('add-slab-row').addEventListener('click', function() {
        const container = document.getElementById('slabs-container');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="number" name="slabs[${slabIndex}][income_from]" class="form-control" required></td>
            <td><input type="number" name="slabs[${slabIndex}][income_to]" class="form-control"></td>
            <td><input type="number" name="slabs[${slabIndex}][fixed_tax_amount]" class="form-control" required></td>
            <td><input type="number" name="slabs[${slabIndex}][tax_rate_percentage]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-slab-row">Remove</button></td>
        `;
        container.appendChild(newRow);
        slabIndex++;
        updateLastRowNote();
    });

    document.getElementById('slabs-container').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-slab-row')) {
            e.target.closest('tr').remove();
            updateLastRowNote();
        }
    });

    updateLastRowNote();
});
</script>
@endpush