<?php

namespace App\Http\Controllers;

use App\Models\WelfareFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WelfareFundController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $currentBalance = WelfareFund::getCurrentBalance();
        
        // Get selected month for summary (default to current month)
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $year = Carbon::parse($selectedMonth)->year;
        $month = Carbon::parse($selectedMonth)->month;
        
        // Monthly summary
        $monthlyAdded = WelfareFund::getMonthlyTotal($year, $month, 'add');
        $monthlyDeducted = WelfareFund::getMonthlyTotal($year, $month, 'deduct');
        $monthlyNet = $monthlyAdded - $monthlyDeducted;
        
        // Get logs with pagination
        $logs = WelfareFund::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Generate months for dropdown (last 12 months)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }
        
        return view('welfare-fund.index', compact(
            'currentBalance',
            'selectedMonth',
            'monthlyAdded',
            'monthlyDeducted',
            'monthlyNet',
            'logs',
            'months'
        ));
    }

    public function add(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        WelfareFund::create([
            'amount' => $request->amount,
            'type' => 'add',
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('welfare-fund.index')
            ->with('success', 'Amount added successfully!');
    }

    public function deduct(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $currentBalance = WelfareFund::getCurrentBalance();
        
        if ($request->amount > $currentBalance) {
            return redirect()->route('welfare-fund.index')
                ->with('error', 'Insufficient balance! Current balance is Rs ' . number_format($currentBalance, 2));
        }

        WelfareFund::create([
            'amount' => $request->amount,
            'type' => 'deduct',
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('welfare-fund.index')
            ->with('success', 'Amount deducted successfully!');
    }
}
