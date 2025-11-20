@extends('layouts.admin')
@section('title', 'Salary Revision Approval')

@section('content')
<div class="w-full max-w-6xl mx-auto px-6 text-[16px] leading-relaxed">

    {{-- HEADER --}}
    <div class="bg-white shadow-md rounded-xl mb-6 p-6 border border-gray-300">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
            Salary Revision Review — {{ $employee->name }}
        </h2>
        <p class="text-gray-700 mb-1">
            <strong>Designation / Department:</strong>
            {{ $employee->designationRelation->name ?? 'N/A' }} / {{ $employee->departmentRelation->name ?? 'N/A' }}
        </p>

        <p class="text-gray-700 mb-1">
            <strong>Effective Date:</strong>
            {{ \Carbon\Carbon::parse($structure->effective_date)->format('d M, Y') }}
        </p>
        <p class="text-gray-700 mb-1">
            <strong>Status:</strong>
            <span style="background-color:#fef3c7;color:#92400e;padding:2px 10px;border-radius:8px;font-weight:600;">
                {{ ucfirst($structure->status) }}
            </span>
        </p>
    </div>

    {{-- SALARY COMPARISON TABLE --}}
    <div class="bg-white shadow-md rounded-xl p-6 border border-gray-300">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Salary Structure Comparison</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-[16px] border border-gray-300">
                <thead style="background-color:#f3f4f6;">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold w-1/4">Component</th>
                        <th class="px-4 py-3 text-center font-semibold w-1/4">Current (Rs)</th>
                        <th class="px-4 py-3 text-center font-semibold w-1/4">Change</th>
                        <th class="px-4 py-3 text-center font-semibold w-1/4">Revised (Rs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">

                    {{-- BASIC SALARY --}}
                    <tr>
                        <td class="px-4 py-2 font-medium">Basic Salary</td>
                        <td class="px-4 py-2 text-center">{{ number_format($currentBasic, 2) }}</td>
                        @php $basicDiff = $pendingBasic - $currentBasic; @endphp
                        <td class="px-4 py-2 text-center" style="color:{{ $basicDiff >= 0 ? '#15803d' : '#b91c1c' }};">
                            {{ $basicDiff >= 0 ? '+' : '' }}{{ number_format($basicDiff, 2) }}
                        </td>
                        <td class="px-4 py-2 text-center">{{ number_format($pendingBasic, 2) }}</td>
                    </tr>

                    {{-- ALLOWANCES --}}
                    @if($pendingComponents->where('type', 'allowance')->isNotEmpty())
                        <tr style="background-color:#f9fafb;">
                            <td colspan="4" class="px-4 py-2 font-semibold text-gray-800">Allowances</td>
                        </tr>
                        @foreach($pendingComponents->where('type', 'allowance') as $comp)
                            @php
                                // Convert to readable label
                                $label = ucwords(str_replace(['-', '_'], ' ', $comp['name']));

                                // Case-insensitive, punctuation-insensitive match for previous value
                                $prev = optional(
                                    $previousComponents->first(function ($p) use ($comp) {
                                        return strtolower(preg_replace('/[^a-z0-9]/i','', $p['name'])) === strtolower(preg_replace('/[^a-z0-9]/i','', $comp['name']));
                                    })
                                )['amount'] ?? 0;

                                $diff = $comp['amount'] - $prev;
                            @endphp
                            <tr>
                                <td class="px-4 py-2">{{ $label }}</td>
                                <td class="px-4 py-2 text-center">{{ number_format($prev, 2) }}</td>
                                <td class="px-4 py-2 text-center" style="color:{{ $diff >= 0 ? '#15803d' : '#b91c1c' }};">
                                    {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                </td>
                                <td class="px-4 py-2 text-center">{{ number_format($comp['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- DEDUCTIONS --}}
                    @if($pendingComponents->where('type', 'deduction')->isNotEmpty())
                        <tr style="background-color:#f9fafb;">
                            <td colspan="4" class="px-4 py-2 font-semibold text-gray-800">Deductions</td>
                        </tr>
                        @foreach($pendingComponents->where('type', 'deduction') as $comp)
                            @php
                                $label = ucwords(str_replace(['-', '_'], ' ', $comp['name']));

                                $prev = optional(
                                    $previousComponents->first(function ($p) use ($comp) {
                                        return strtolower(preg_replace('/[^a-z0-9]/i','', $p['name'])) === strtolower(preg_replace('/[^a-z0-9]/i','', $comp['name']));
                                    })
                                )['amount'] ?? 0;

                                $diff = $comp['amount'] - $prev;
                            @endphp
                            <tr>
                                <td class="px-4 py-2">{{ $label }}</td>
                                <td class="px-4 py-2 text-center">({{ number_format($prev, 2) }})</td>
                                <td class="px-4 py-2 text-center" style="color:{{ $diff >= 0 ? '#15803d' : '#b91c1c' }};">
                                    {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                </td>
                                <td class="px-4 py-2 text-center">({{ number_format($comp['amount'], 2) }})</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ✅ FIX: Removed the explicit "Income Tax (Estimated)" block entirely --}}

                    {{-- TOTALS --}}
                    <tr style="background-color:#f3f4f6;font-weight:600;">
                        <td class="px-4 py-2">Gross Salary</td>
                        <td class="px-4 py-2 text-center">{{ number_format($currentGross, 2) }}</td>
                        <td class="px-4 py-2 text-center" style="color:{{ $pendingGross - $currentGross >= 0 ? '#15803d' : '#b91c1c' }};">
                            {{ $pendingGross - $currentGross >= 0 ? '+' : '' }}{{ number_format($pendingGross - $currentGross, 2) }}
                        </td>
                        <td class="px-4 py-2 text-center">{{ number_format($pendingGross, 2) }}</td>
                    </tr>
                    <tr style="background-color:#f9fafb;font-weight:600;">
                        <td class="px-4 py-2">Net Salary</td>
                        <td class="px-4 py-2 text-center">{{ number_format($currentNet, 2) }}</td>
                        <td class="px-4 py-2 text-center" style="color:{{ $pendingNet - $currentNet >= 0 ? '#15803d' : '#b91c1c' }};">
                            {{ $pendingNet - $currentNet >= 0 ? '+' : '' }}{{ number_format($pendingNet - $currentNet, 2) }}
                        </td>
                        <td class="px-4 py-2 text-center">{{ number_format($pendingNet, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="mt-8 flex flex-wrap justify-start gap-4" style="margin-top:30px;">
            <a href="{{ route('approvals.salary.index') }}"
               style="display:inline-block;background-color:#374151;color:#fff;padding:10px 28px;font-weight:600;
                      border-radius:8px;text-align:center;box-shadow:0 2px 4px rgba(0,0,0,0.2);text-decoration:none;">
                Back
            </a>

            <form action="{{ route('approvals.salary.approve', $structure->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit"
                    style="background-color:#16a34a;color:#fff;padding:10px 28px;font-weight:600;
                           border:none;border-radius:8px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.2);">
                    Approve
                </button>
            </form>

            <form action="{{ route('approvals.salary.reject', $structure->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit"
                    style="background-color:#dc2626;color:#fff;padding:10px 28px;font-weight:600;
                           border:none;border-radius:8px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.2);">
                    Reject
                </button>
            </form>
        </div>
    </div>
</div>
@endsection