@extends('layouts.tax_client')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tax Projection Report ({{ $taxYear }}-{{ $taxYear + 1 }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            
            {{-- 1. Initialize Variables --}}
            @php
                // Column Totals (Vertical)
                $sumYtdGross = 0;
                $sumYtdTaxable = 0;
                $sumYtdTax = 0;

                $monthlySums = [];
                foreach($reportColumns as $key => $col) {
                    $monthlySums[$key] = ['gross' => 0, 'taxable' => 0, 'tax' => 0];
                }

                // Grand Totals (Bottom Right Corner)
                $finalTotalGross = 0;
                $finalTotalTaxable = 0;
                $finalTotalTax = 0;
            @endphp

            <table class="table table-bordered table-sm text-center" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        {{-- Sticky Employee Name --}}
                        <th rowspan="2" class="align-middle bg-dark text-white" style="position:sticky; left:0; z-index:2;">Employee</th>
                        
                        {{-- YTD Header --}}
                        <th colspan="3" class="bg-secondary text-white align-middle">
                            YTD History <br> <small>(Till Oct {{ $taxYear }})</small>
                        </th>

                        {{-- Monthly Headers --}}
                        @foreach($reportColumns as $col)
                            <th colspan="3" class="align-middle text-white 
                                @if($col['type'] == 'current') bg-success 
                                @elseif($col['type'] == 'future') bg-primary 
                                @else bg-warning text-dark @endif">
                                {{ $col['label'] }}
                            </th>
                        @endforeach

                        {{-- NEW: Total for Year Header --}}
                        <th colspan="3" class="bg-dark text-white align-middle border-left-thick">
                            Total For Year <br> <small>({{ $taxYear }}-{{ $taxYear+1 }})</small>
                        </th>
                    </tr>
                    <tr>
                        {{-- Sub-Headers YTD --}}
                        <th class="bg-gray-200" style="font-size: 0.75rem;">Gross</th>
                        <th class="bg-gray-200" style="font-size: 0.75rem;">Taxable</th>
                        <th class="bg-gray-200" style="font-size: 0.75rem;">Tax</th>
                        
                        {{-- Sub-Headers Months --}}
                        @foreach($reportColumns as $col)
                            @php
                                $subClass = 'bg-light';
                                if($col['type'] == 'past') $subClass = 'bg-warning-light';
                                if($col['type'] == 'current') $subClass = 'bg-success-light';
                                if($col['type'] == 'future') $subClass = 'bg-primary-light';
                            @endphp
                            <th class="{{ $subClass }}" style="font-size: 0.75rem;">Gross</th>
                            <th class="{{ $subClass }}" style="font-size: 0.75rem;">Taxable</th>
                            <th class="{{ $subClass }}" style="font-size: 0.75rem;">Tax</th>
                        @endforeach

                        {{-- NEW: Sub-Headers Total --}}
                        <th class="bg-gray-300 border-left-thick" style="font-size: 0.75rem;">Gross</th>
                        <th class="bg-gray-300" style="font-size: 0.75rem;">Taxable</th>
                        <th class="bg-gray-300" style="font-size: 0.75rem;">Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectionData as $row)
                        
                        {{-- 2. Calculate Row Totals (Employee Wise) --}}
                        @php
                            // Start with YTD values
                            $empTotalGross = $row['ytd']['gross'];
                            $empTotalTaxable = $row['ytd']['taxable'];
                            $empTotalTax = $row['ytd']['tax'];

                            // Add to Vertical YTD Sums
                            $sumYtdGross += $row['ytd']['gross'];
                            $sumYtdTaxable += $row['ytd']['taxable'];
                            $sumYtdTax += $row['ytd']['tax'];

                            // Loop through months to add to row total AND vertical column totals
                            foreach($row['months'] as $index => $month) {
                                // Add to Row Total
                                $empTotalGross += $month['gross'];
                                $empTotalTaxable += $month['taxable'];
                                $empTotalTax += $month['tax'];

                                // Add to Column Totals
                                $monthlySums[$index]['gross'] += $month['gross'];
                                $monthlySums[$index]['taxable'] += $month['taxable'];
                                $monthlySums[$index]['tax'] += $month['tax'];
                            }

                            // Add to Final Grand Totals (Bottom Right)
                            $finalTotalGross += $empTotalGross;
                            $finalTotalTaxable += $empTotalTaxable;
                            $finalTotalTax += $empTotalTax;
                        @endphp

                    <tr>
                        {{-- Employee Name --}}
                        <td class="text-left font-weight-bold" style="position:sticky; left:0; background: #fff; min-width: 150px; z-index:1;">
                            {{ $row['employee']->name }} <br>
                            <small class="text-muted">{{ $row['employee']->designation }}</small>
                        </td>

                        {{-- YTD Data --}}
                        <td class="bg-gray-200 font-weight-bold">{{ number_format($row['ytd']['gross']) }}</td>
                        <td class="bg-gray-200 font-weight-bold">{{ number_format($row['ytd']['taxable']) }}</td>
                        <td class="bg-gray-200 font-weight-bold">{{ number_format($row['ytd']['tax']) }}</td>

                        {{-- Monthly Data --}}
                        @foreach($row['months'] as $month)
                            @php
                                $cellClass = '';
                                if($month['type'] == 'current') $cellClass = 'bg-success-light font-weight-bold';
                                elseif($month['type'] == 'future') $cellClass = 'bg-primary-light text-muted';
                                else $cellClass = 'bg-warning-light';
                            @endphp
                            
                            <td class="{{ $cellClass }}">{{ number_format($month['gross']) }}</td>
                            <td class="{{ $cellClass }}">{{ number_format($month['taxable']) }}</td>
                            <td class="{{ $cellClass }}">{{ number_format($month['tax']) }}</td>
                        @endforeach

                        {{-- NEW: Employee Totals (Far Right) --}}
                        <td class="bg-gray-300 font-weight-bold border-left-thick">{{ number_format($empTotalGross) }}</td>
                        <td class="bg-gray-300 font-weight-bold">{{ number_format($empTotalTaxable) }}</td>
                        <td class="bg-gray-300 font-weight-bold">{{ number_format($empTotalTax) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                
                {{-- 3. Footer Row with All Totals --}}
                <tfoot class="bg-dark text-white font-weight-bold">
                    <tr>
                        <th class="align-middle" style="position:sticky; left:0; background: #343a40; z-index:2;">TOTALS</th>
                        
                        {{-- YTD Totals --}}
                        <th class="align-middle">{{ number_format($sumYtdGross) }}</th>
                        <th class="align-middle">{{ number_format($sumYtdTaxable) }}</th>
                        <th class="align-middle">{{ number_format($sumYtdTax) }}</th>

                        {{-- Monthly Totals --}}
                        @foreach($reportColumns as $index => $col)
                            <th class="align-middle">{{ number_format($monthlySums[$index]['gross']) }}</th>
                            <th class="align-middle">{{ number_format($monthlySums[$index]['taxable']) }}</th>
                            <th class="align-middle">{{ number_format($monthlySums[$index]['tax']) }}</th>
                        @endforeach

                        {{-- NEW: Grand Totals (Bottom Right) --}}
                        <th class="align-middle border-left-thick bg-secondary">{{ number_format($finalTotalGross) }}</th>
                        <th class="align-middle bg-secondary">{{ number_format($finalTotalTaxable) }}</th>
                        <th class="align-middle bg-secondary">{{ number_format($finalTotalTax) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    /* Formatting */
    .bg-gray-200 { background-color: #eaecf4; color: #5a5c69; }
    .bg-gray-300 { background-color: #dddfeb; color: #4e505c; }
    
    .bg-success-light { background-color: #e8f5e9; color: #1b5e20; }
    .bg-primary-light { background-color: #e3f2fd; color: #0d47a1; }
    .bg-warning-light { background-color: #fff8e1; color: #7f6000; }
    .bg-warning { background-color: #ffc107 !important; color: #000 !important; }

    /* Thick Border to separate Annual Totals */
    .border-left-thick { border-left: 3px solid #858796 !important; }
    
    /* Sticky Column Shadow */
    table td[style*="sticky"], table th[style*="sticky"] {
        box-shadow: 2px 0px 5px rgba(0,0,0,0.05);
    }
</style>
@endsection