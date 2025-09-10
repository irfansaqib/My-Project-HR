<?php

namespace App\Http\Controllers;

use App\Models\BusinessBankAccount;
use App\Models\Payroll;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollController extends Controller
{
    public function index()
    {
        $businessId = Auth::user()->business_id;

        $sheets = SalarySheet::where('business_id', $businessId)
            ->where('status', '!=', 'paid')
            ->with(['items' => function ($query) {
                $query->where('status', 'pending')->with('employee.payingBankAccount');
            }])
            ->orderBy('month', 'desc')
            ->get();
            
        $pendingSheets = $sheets->filter(function ($sheet) {
            return $sheet->items->isNotEmpty();
        });

        $groupedItems = [];
        foreach ($pendingSheets as $sheet) {
            $grouped = $sheet->items->groupBy('employee.business_bank_account_id');
            $groupedItems[$sheet->month->format('Y-m-d')] = $grouped;
        }

        return view('payrolls.index', compact('pendingSheets', 'groupedItems'));
    }

    public function history()
    {
        $payrolls = Payroll::where('business_id', Auth::user()->business_id)
            ->with('salarySheet')
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('payrolls.history', compact('payrolls'));
    }
    
    public function runByBank(Request $request)
    {
        $request->validate([
            'salary_sheet_id' => 'required|exists:salary_sheets,id',
            'business_bank_account_id' => ['nullable', Rule::exists('business_bank_accounts', 'id')->where(function ($query) { $query->where('business_id', Auth::user()->business_id); }), ],
        ]);

        $sheet = SalarySheet::findOrFail($request->salary_sheet_id);

        if ($sheet->business_id !== Auth::user()->business_id) { abort(403, 'Unauthorized action.'); }

        try {
            DB::transaction(function () use ($sheet, $request) {
                $itemsToPay = SalarySheetItem::where('salary_sheet_id', $sheet->id)
                    ->where('status', 'pending')
                    ->whereHas('employee', function ($query) use ($request) {
                        $query->where('business_bank_account_id', $request->business_bank_account_id);
                    })->get();

                if ($itemsToPay->isEmpty()) { throw new \Exception("No pending payments found for this group."); }

                $totalAmount = $itemsToPay->sum('net_salary');
                $bank = $request->business_bank_account_id ? BusinessBankAccount::find($request->business_bank_account_id) : null;
                $bankName = $bank ? $bank->bank_name : 'Unassigned';

                $payroll = Payroll::create([
                    'business_id' => $sheet->business_id, 'salary_sheet_id' => $sheet->id,
                    'business_bank_account_id' => $bank ? $bank->id : null, 'payment_date' => now(),
                    'total_amount' => $totalAmount, 'status' => 'Completed',
                    'notes' => 'Payroll processed for ' . $bankName . ' for ' . Carbon::parse($sheet->month)->format('F, Y'),
                ]);

                $payroll->items()->attach($itemsToPay->pluck('id'));

                SalarySheetItem::whereIn('id', $itemsToPay->pluck('id'))->update(['status' => 'paid']);

                $remainingPending = $sheet->items()->where('status', 'pending')->count();
                if ($remainingPending === 0) {
                    $sheet->update(['status' => 'paid']);
                } else {
                    $sheet->update(['status' => 'partially_paid']);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payroll for the selected group has been processed successfully!');
    }
    
    public function downloadBankFile(Payroll $payroll)
    {
        if ($payroll->business_id !== Auth::user()->business_id) { abort(403); }
        
        $itemsToInclude = $payroll->items()->with('employee')->get();
        $sheet = $payroll->salarySheet()->with('business')->first();

        $bankAccount = $payroll->business_bank_account_id
            ? BusinessBankAccount::find($payroll->business_bank_account_id)
            : null;

        return $this->streamBankFile($sheet, $itemsToInclude, $bankAccount);
    }

    private function streamBankFile(SalarySheet $sheet, $items, $bankAccount)
    {
        $fileName = 'BankPaymentFile-' . Carbon::parse($sheet->month)->format('F-Y') . '.csv';
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];
        $columns = ['Employee Name', 'Bank Account Number', 'Net Salary Amount'];
        $business = $sheet->business;

        $callback = function() use($items, $columns, $business, $sheet, $bankAccount) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [$business->legal_name ?? $business->name]);
            
            // ** CHANGE 1: Rephrased this line **
            fputcsv($file, ['Salary payment for the month of ' . Carbon::parse($sheet->month)->format('F, Y')]);
            
            // ** CHANGE 2: Removed "Pay From:" text **
            if ($bankAccount) {
                fputcsv($file, [$bankAccount->bank_name . ' (' . $bankAccount->account_number . ')']);
            } else {
                fputcsv($file, ['Unassigned Bank Account']);
            }

            fputcsv($file, []); // Blank row for spacing
            fputcsv($file, $columns);

            foreach ($items as $item) {
                $row = [ $item->employee->name, $item->employee->bank_account_number, $item->net_salary ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}