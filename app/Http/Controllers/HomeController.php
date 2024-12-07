<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\RoomBooking;
use App\Models\Cost; 
use App\Models\Task;
use Carbon\Carbon;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
     public function index(Request $request)
     {
         // Get selected month or default to current month
         $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
         $today = Carbon::now();
         $currentDay = $today->day;
     
         // Calculate current period for service charge (10th to 10th)
         if ($currentDay > 10) {
             $scStartDate = Carbon::parse($selectedMonth)->setDay(10)->startOfDay();
             $scEndDate = Carbon::parse($selectedMonth)->addMonth()->setDay(10)->endOfDay();
         } else {
             $scStartDate = Carbon::parse($selectedMonth)->subMonth()->setDay(10)->startOfDay();
             $scEndDate = Carbon::parse($selectedMonth)->setDay(10)->endOfDay();
         }
     
         // Calculate previous period
         $prevScStartDate = $scStartDate->copy()->subMonth();
         $prevScEndDate = $scEndDate->copy()->subMonth();
     
         // Get current period sales
         $currentSales = Sale::whereBetween('updated_at', [$scStartDate, $scEndDate])
                         ->where('sale_status', 'paid');
         $serviceCharge = $currentSales->sum('total_recieved');
     
         // Get previous period sales
         $previousSales = Sale::whereBetween('updated_at', [$prevScStartDate, $prevScEndDate])
                         ->where('sale_status', 'paid');
         $previousServiceCharge = $previousSales->sum('total_recieved');
     
         // Calculate percentage change
         $percentageChange = 0;
         if ($previousServiceCharge > 0) {
             $percentageChange = (($serviceCharge - $previousServiceCharge) / $previousServiceCharge) * 100;
         }
     
         // Generate months for dropdown (last 12 months)
         $months = [];
         for ($i = 0; $i < 12; $i++) {
             $date = Carbon::now()->subMonths($i);
             $months[$date->format('Y-m')] = $date->format('F Y');
         }
     
         $selectedDate = $request->get('date', date('Y-m-d'));
     
         // Get booked rooms
         $bookedRooms = RoomBooking::with('room')
             ->whereDate('guest_in_time', '<=', $selectedDate)
             ->where(function($query) use ($selectedDate) {
                 $query->whereNull('guest_out_time')
                       ->orWhereDate('guest_out_time', '>=', $selectedDate);
             })
             ->get();
     
         // Get pending tasks
         $pendingTasks = Task::with('taskCategory')
             ->where('is_done', false)
             ->get();
     
         // Add period navigation for salary advances
         $selectedPeriod = $request->get('period', 0);
         
         // Calculate periods for salary advances
         $periods = [];
         for ($i = 0; $i < 3; $i++) {
             $basePeriod = Carbon::now()->subMonths($i);
             
             if ($basePeriod->day > 10) {
                 $periodStart = Carbon::create($basePeriod->year, $basePeriod->month, 10, 0, 0, 0);
                 $periodEnd = Carbon::create($basePeriod->year, $basePeriod->month + 1, 10, 23, 59, 59);
             } else {
                 $periodStart = Carbon::create($basePeriod->year, $basePeriod->month - 1, 10, 0, 0, 0);
                 $periodEnd = Carbon::create($basePeriod->year, $basePeriod->month, 10, 23, 59, 59);
             }
     
             $periods[] = [
                 'start' => $periodStart,
                 'end' => $periodEnd,
                 'label' => $periodStart->format('M d, Y') . ' - ' . $periodEnd->format('M d, Y')
             ];
         }
     
         // Get selected period dates
         $selectedPeriodDates = $periods[$selectedPeriod];
         $startDate = $selectedPeriodDates['start'];
         $endDate = $selectedPeriodDates['end'];
     
         // Fetch salary advances for the selected period
         $salaryAdvances = Cost::with(['person', 'user'])
             ->where('group_id', 1)
             ->whereBetween('cost_date', [$startDate, $endDate])
             ->orderBy('cost_date', 'desc')
             ->get();
     
         $totalAdvance = $salaryAdvances->sum('amount');
         $dateRangeText = $selectedPeriodDates['label'];
     
         // Service charge period labels
         $periodLabel = $scStartDate->format('M d, Y') . ' - ' . $scEndDate->format('M d, Y');
         $previousPeriodLabel = $prevScStartDate->format('M d, Y') . ' - ' . $prevScEndDate->format('M d, Y');
     
         return view('home', compact(
             'serviceCharge',
             'previousServiceCharge',
             'percentageChange',
             'periodLabel',
             'previousPeriodLabel',
             'selectedMonth',
             'months',
             'bookedRooms',
             'pendingTasks',
             'salaryAdvances',
             'totalAdvance',
             'dateRangeText',
             'selectedPeriod',
             'periods'
         ));
     }
}
