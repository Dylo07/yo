<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\RoomBooking;
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
        $dateStart = Carbon::parse($selectedMonth)->startOfMonth();
        $dateEnd = Carbon::parse($selectedMonth)->endOfMonth();

        // If it's current month, set end date to now
        if ($selectedMonth === Carbon::now()->format('Y-m')) {
            $dateEnd = Carbon::now();
        }

        // Get sales for selected month
        $sales = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])
                    ->where('sale_status', 'paid');

        $serviceCharge = $sales->sum('total_recieved');
        
        // Generate months for dropdown (last 12 months)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }

        $selectedDate = $request->get('date', date('Y-m-d'));
    
        $bookedRooms = RoomBooking::with('room')
            ->whereDate('guest_in_time', '<=', $selectedDate)
            ->where(function($query) use ($selectedDate) {
                $query->whereNull('guest_out_time')
                      ->orWhereDate('guest_out_time', '>=', $selectedDate);
            })
            ->get();
    
        return view('home', compact('serviceCharge', 'months', 'selectedMonth', 'bookedRooms'));
    }

}
