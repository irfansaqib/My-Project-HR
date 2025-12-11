@extends('layouts.admin')
@section('title', 'Salary Sheet')

@push('styles')
<style>
    .table-salary th { vertical-align: middle !important; text-align: center; white-space: nowrap; }
    .table-salary td { vertical-align: middle; white-space: nowrap; }
    .table-salary .sticky-col { position: sticky; left: 0; background-color: #fff; z-index: 10; border-right: 2px solid #dee2e6; }
    .table-salary thead .sticky-col { background-color: #343a40; color: #fff; z-index: 11; }
    .input-clean { border: 1px solid #ced4da; border-radius: 4px; padding: 4px; width: 100%; text-align: right; font-weight: bold; }
    .input-clean:focus { border-color: #80bdff; outline: 0; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title font-weight-bold text-dark">
                    {{ $monthName }}
                </h3>
                @if($salarySheet->status == 'finalized')
                    <span class="badge badge-success ml-2"><i class="fas fa-lock"></i> Finalized</span>
                @else
                    <span class="badge badge-warning ml-2">Draft Mode - Review & Save</span>
                @endif
            </div>
            
           <div>
                <a href="{{ route('salaries.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                
                {{-- Global Actions only visible if Finalized --}}
                @if($salarySheet->status == 'finalized')
                    <a href="{{ route('salaries.print', $salarySheet->id) }}" target="_blank" class="btn btn-outline-dark btn-sm"><i class="fas fa-print"></i> Print Sheet</a>
                    <a href="{{ route('salaries.payslips.print-all', $salarySheet->id) }}" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-file-invoice"></i> Print All Payslips</a>
                    
                    
                  
                    {{-- ✅ NEW: Paying Bank Export Dropdown --}}
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-file-excel"></i> Bank Transfer Files
                        </button>
                        <div class="dropdown-menu">
                            {{-- Loop through Paying Banks found in this sheet --}}
                            @if($payingBanks->isNotEmpty())
                                @foreach($payingBanks as $bank)
                                    <a class="dropdown-item" href="{{ route('salaries.export-bank', ['salarySheet' => $salarySheet->id, 'account_id' => $bank->id]) }}">
                                        {{ $bank->bank_name }} ({{ $bank->account_number }})
                                    </a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                            @endif
                            
                            {{-- Option to download Unassigned/Cash --}}
                            <a class="dropdown-item" href="{{ route('salaries.export-bank', ['salarySheet' => $salarySheet->id]) }}">
                                Download All Combined
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('salaries.payslips.send-all', $salarySheet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to email payslips to all employees?');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope"></i> Email All Payslips
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($salarySheet->status != 'finalized')
    <form action="{{ route('salaries.finalize', $salarySheet->id) }}" method="POST" onsubmit="return confirm('Confirm Finalization? This will lock the sheet.');">
        @csrf
    @endif

    <div class="card-body table-responsive p-0" style="max-height: 75vh;">
        <table class="table table-bordered table-head-fixed table-hover table-salary text-sm">
            <thead>
                <tr class="bg-dark text-white">
                    <th rowspan="2" class="sticky-col" style="min-width: 180px; z-index: 12;">Employee Details</th>
                    @php $earningsColspan = 1 + count($activeAllowances) + ($hasBonus ? 1 : 0) + ($hasEncashment ? 1 : 0); @endphp
                    <th colspan="{{ $earningsColspan }}" class="bg-success">Earnings</th>
                    <th rowspan="2" class="bg-light text-dark font-weight-bold" style="min-width: 100px;">Gross Salary</th>
                    @php $deductionsColspan = count($activeDeductions) + 1; @endphp
                    <th colspan="{{ $deductionsColspan }}" class="bg-danger">Deductions</th>
                    <th rowspan="2" class="bg-light text-dark font-weight-bold">Net Salary</th>
                    @if($hasArrears) <th rowspan="2" class="text-danger">Arrears</th> @endif
                    <th rowspan="2" class="bg-primary font-weight-bold" style="min-width: 100px;">Payable</th>
                    <th colspan="2" class="bg-secondary">Payment Processing</th>
                </tr>
                <tr class="bg-secondary text-white">
                    <th class="text-right">Basic Salary</th>
                    @foreach($activeAllowances as $header) <th class="text-right">{{ $header }}</th> @endforeach
                    @if($hasBonus) <th class="text-right">Bonus</th> @endif
                    @if($hasEncashment) <th class="text-right">Leave Encash.</th> @endif
                    @foreach($activeDeductions as $header) <th class="text-right">{{ $header }}</th> @endforeach
                    <th class="text-right" style="min-width: 100px;">Income Tax <i class="fas fa-pen ml-1 small text-warning"></i></th>
                    <th style="min-width: 110px;">Payment</th>
                    <th style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salarySheet->items as $item)
                @php
                    $isFinal = $salarySheet->status == 'finalized';
                    $isHeld = $item->payment_status == 'held';
                @endphp
                <tr class="{{ $isHeld ? 'table-warning' : '' }}">
                    <td class="sticky-col">
                        <div class="d-flex flex-column align-items-start">
                            <span class="font-weight-bold text-dark">{{ $item->employee->name }}</span>
                            <small class="text-muted">{{ $item->employee->designationRelation->title ?? '-' }}</small>
                        </div>
                    </td>

                    <td class="text-right">{{ number_format($item->employee->basic_salary) }}</td>
                    @foreach($activeAllowances as $header)
                        <td class="text-right text-success">{{ number_format($item->allowances_breakdown[$header] ?? 0) }}</td>
                    @endforeach
                    @if($hasBonus) <td class="text-right">{{ number_format($item->bonus) }}</td> @endif
                    @if($hasEncashment) <td class="text-right">{{ number_format($item->leave_encashment_amount) }}</td> @endif

                    <td class="text-right font-weight-bold bg-light">{{ number_format($item->gross_salary) }}</td>

                    @foreach($activeDeductions as $header)
                        <td class="text-right text-danger">{{ number_format($item->deductions_breakdown[$header] ?? 0) }}</td>
                    @endforeach

                    <td class="p-1">
                        @if($isFinal)
                            <div class="text-right px-2">{{ number_format($item->income_tax) }}</div>
                        @else
                            <input type="number" name="items[{{ $item->id }}][income_tax]" 
                                   class="input-clean text-danger" 
                                   value="{{ round($item->income_tax) }}" 
                                   onchange="updateTax({{ $item->id }}, this.value)">
                        @endif
                    </td>

                    <td class="text-right font-weight-bold bg-light" id="net-{{ $item->id }}">{{ number_format($item->net_salary) }}</td>
                    @if($hasArrears) <td class="text-right text-danger">{{ number_format($item->arrears_adjustment) }}</td> @endif
                    <td class="text-right font-weight-bold bg-light text-primary" style="font-size: 1.1em;" id="payable-{{ $item->id }}">{{ number_format($item->payable_amount) }}</td>

                    <td class="p-1">
                        @if($isFinal)
                            <div class="text-right px-2 font-weight-bold text-success">
                                {{ number_format($item->paid_amount) }}
                            </div>
                        @else
                            <input type="number" name="items[{{ $item->id }}][paid_amount]" 
                                   class="input-clean text-success" 
                                   value="{{ round($item->payable_amount) }}" 
                                   id="pay-input-{{ $item->id }}"
                                   {{ $isHeld ? 'disabled' : '' }}>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            {{-- 1. Hold Toggle (Only in Draft) --}}
                            @if(!$isFinal)
                                <div class="custom-control custom-switch mr-2" title="Hold Salary">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="hold_{{ $item->id }}" 
                                           name="items[{{ $item->id }}][is_held]" value="1"
                                           {{ $isHeld ? 'checked' : '' }}
                                           onchange="toggleHoldUI({{ $item->id }}, this.checked)">
                                    <label class="custom-control-label" for="hold_{{ $item->id }}"></label>
                                </div>
                            
                            {{-- 2. Payslip Button (Visible Always) --}}
                            @else
                                <a href="{{ route('salaries.payslip', $item->id) }}" target="_blank" class="btn btn-xs btn-info" title="View Payslip">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(!$isFinal)
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success btn-lg px-5 font-weight-bold shadow-sm">
                <i class="fas fa-save mr-2"></i> Finalize & Save Sheet
            </button>
        </div>
    </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    function updateTax(itemId, newTax) {
        $.post(`/salary-items/${itemId}/update`, {
            _token: '{{ csrf_token() }}',
            income_tax: newTax
        }).done(function(res) {
            $(`#net-${itemId}`).text(new Intl.NumberFormat().format(res.item.net_salary));
            $(`#payable-${itemId}`).text(new Intl.NumberFormat().format(res.item.payable_amount));
            if(!$(`#hold_${itemId}`).is(':checked')) {
                $(`#pay-input-${itemId}`).val(res.item.payable_amount);
            }
        });
    }

    function toggleHoldUI(itemId, isChecked) {
        if(isChecked) {
            $(`#pay-input-${itemId}`).prop('disabled', true).val(0);
        } else {
            let payableText = $(`#payable-${itemId}`).text().replace(/,/g, '');
            $(`#pay-input-${itemId}`).prop('disabled', false).val(payableText);
        }
    }
</script>
@endpush
@endsection