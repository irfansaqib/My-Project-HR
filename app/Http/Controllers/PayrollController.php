<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Payroll;
use App\Models\SalarySheet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
{
    $businessId = Auth::user()->business_id;

    // Get pending sheets and eager load necessary data
    $pendingSheets = SalarySheet::where('business_id', $businessId)
        ->where('status', 'generated')
        ->with('items.employee.payingBankAccount') // Load the new relationship
        ->orderBy('month', 'desc')
        ->get();

    // Group items by the business bank account
    $groupedItems = [];
    foreach($pendingSheets as $sheet) {
        $grouped = $sheet->items->groupBy('employee.payingBankAccount.bank_name');
        $groupedItems[$sheet->month] = $grouped;
    }

    return view('payrolls.index', compact('pendingSheets', 'groupedItems'));
}

    public function store(Request $request)
    {
        $request->validate(['salary_sheet_id' => 'required|exists:salary_sheets,id']);
        $sheet = SalarySheet::with('items.employee', 'business')->findOrFail($request->salary_sheet_id);

        if ($sheet->business_id !== Auth::user()->business_id) { abort(403, 'Unauthorized action.'); }

        DB::transaction(function () use ($sheet) {
            $totalAmount = $sheet->items->sum('net_salary');
            Payroll::create([
                'business_id' => $sheet->business_id, 'salary_sheet_id' => $sheet->id,
                'payment_date' => now(), 'total_amount' => $totalAmount, 'status' => 'Completed',
                'notes' => 'Payroll processed for ' . Carbon::parse($sheet->month)->format('F, Y'),
            ]);
            $sheet->update(['status' => 'paid']);
        });

        return $this->streamBankFile($sheet);
    }

    public function downloadBankFile(Payroll $payroll)
    {
        if ($payroll->business_id !== Auth::user()->business_id) { abort(403); }
        $sheet = SalarySheet::with('items.employee', 'business')->findOrFail($payroll->salary_sheet_id);
        return $this->streamBankFile($sheet);
    }

    private function streamBankFile(SalarySheet $sheet)
    {
        // --- AMENDMENT 3: DYNAMIC FILE NAME ---
        $fileName = 'BankPaymentFile-' . Carbon::parse($sheet->month)->format('F-Y') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache", "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Employee Name', 'Bank Account Number', 'Net Salary Amount'];
        $business = $sheet->business;

        $callback = function() use($sheet, $columns, $business) {
            $file = fopen('php://output', 'w');
            
            // --- AMENDMENT 2: ADD TITLES TO FILE ---
            fputcsv($file, [$business->legal_name ?? $business->name]);
            fputcsv($file, ['Bank Payment Details for ' . Carbon::parse($sheet->month)->format('F, Y')]);
            fputcsv($file, []); // Blank row for spacing
            
            fputcsv($file, $columns);

            foreach ($sheet->items as $item) {
                $row = [
                    $item->employee->name,
                    $item->employee->bank_account_number,
                    $item->net_salary
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}