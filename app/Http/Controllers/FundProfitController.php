<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\FundContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FundProfitController extends Controller
{
    public function create()
    {
        $funds = Fund::where('business_id', Auth::user()->business_id)->get();
        return view('funds.distribute', compact('funds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fund_id' => 'required|exists:funds,id',
            'amount' => 'required|numeric|min:1',
            'distribution_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $fund = Fund::findOrFail($request->fund_id);
        $totalProfit = (float) $request->amount;
        $date = $request->distribution_date;

        DB::transaction(function () use ($fund, $totalProfit, $date, $request) {
            
            // 1. Calculate Balances AS AT the distribution date
            $contributions = FundContribution::where('fund_id', $fund->id)
                ->where('transaction_date', '<=', $date)
                ->get();

            $balances = [];
            $totalFundBalance = 0;

            foreach ($contributions->groupBy('employee_id') as $empId => $records) {
                // Calculate Net Balance (Credits - Debits)
                $credits = $records->whereIn('type', ['employee_share', 'employer_share', 'profit_credit'])->sum('amount');
                $debits = $records->where('type', 'withdrawal')->sum('amount');
                $net = $credits - $debits;

                if ($net > 0) {
                    $balances[$empId] = $net;
                    $totalFundBalance += $net;
                }
            }

            if ($totalFundBalance <= 0) {
                throw new \Exception("Total fund balance is zero. Cannot distribute profit.");
            }

            // 2. Distribute Profit
            foreach ($balances as $empId => $empBalance) {
                $ratio = $empBalance / $totalFundBalance;
                $share = round($totalProfit * $ratio, 2);

                if ($share > 0) {
                    FundContribution::create([
                        'fund_id' => $fund->id,
                        'employee_id' => $empId,
                        'type' => 'profit_credit',
                        'amount' => $share,
                        'transaction_date' => $date,
                        'description' => $request->description, // Auto-generated description from form
                    ]);
                }
            }
        });

        return redirect()->route('funds.index')->with('success', 'Profit distributed successfully.');
    }
}