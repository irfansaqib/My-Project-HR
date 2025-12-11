@extends('layouts.tax_client')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tax Projection Report ({{ $taxYear }}-{{ $taxYear + 1 }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle bg-dark text-white" style="position:sticky; left:0; z-index:1;">Employee</th>
                        
                        {{-- YTD Header --}}
                        <th colspan="3" class="bg-secondary text-white">
                            YTD History <br> <small>(Till Oct {{ $taxYear }})</small>
                        </th>

                        {{-- Monthly Headers --}}
                        @foreach($reportColumns as $col)
                            <th colspan="3" class="@if($col['type'] == 'current') bg-success text-white @elseif($col['type'] == 'future') bg-primary text-white @else bg-warning text-dark @endif">
                                {{ $col['label'] }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        {{-- Sub-Headers --}}
                        <th class="bg-light">Gross</th>
                        <th class="bg-light">Taxable</th>
                        <th class="bg-light">Tax</th>
                        
                        @foreach($reportColumns as $col)
                            <th style="font-size: 0.75rem;">Gross</th>
                            <th style="font-size: 0.75rem;">Taxable</th>
                            <th style="font-size: 0.75rem;">Tax</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectionData as $row)
                    <tr>
                        {{-- Employee Name --}}
                        <td class="text-left font-weight-bold" style="position:sticky; left:0; background: #fff;">
                            {{ $row['employee']->name }} <br>
                            <small class="text-muted">{{ $row['employee']->designation }}</small>
                        </td>

                        {{-- YTD Data --}}
                        <td class="bg-light font-weight-bold">{{ number_format($row['ytd']['gross']) }}</td>
                        <td class="bg-light font-weight-bold">{{ number_format($row['ytd']['taxable']) }}</td>
                        <td class="bg-light font-weight-bold">{{ number_format($row['ytd']['tax']) }}</td>

                        {{-- Monthly Data (Looping through pre-calculated array) --}}
                        @foreach($row['months'] as $month)
                            @php
                                $cellClass = '';
                                if($month['type'] == 'current') $cellClass = 'bg-success-light';
                                if($month['type'] == 'future') $cellClass = 'bg-primary-light';
                            @endphp
                            
                            <td class="{{ $cellClass }}">{{ number_format($month['gross']) }}</td>
                            <td class="{{ $cellClass }}">{{ number_format($month['taxable']) }}</td>
                            <td class="{{ $cellClass }}">{{ number_format($month['tax']) }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-success-light { background-color: #e8f5e9; color: #1b5e20; }
    .bg-primary-light { background-color: #e3f2fd; color: #0d47a1; }
    .bg-secondary { background-color: #757575 !important; }
</style>
@endsection