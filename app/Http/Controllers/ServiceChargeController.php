<?php

namespace App\Http\Controllers;

use App\Models\ServiceCharge;
use App\Models\ServiceChargePoint;
use App\Models\Person;
use App\Models\Sale;
use App\Models\DamageItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceChargeController extends Controller
{
    public function index()
    {
        // Get active staff - Modified query to handle MySQL strict mode
        $staff = DB::table('persons')
            ->join('staff_codes', 'persons.id', '=', 'staff_codes.person_id')
            ->where('staff_codes.is_active', 1)
            ->select('persons.id', 'persons.name', 'persons.created_at', 'persons.updated_at', 'persons.type')
            ->groupBy('persons.id', 'persons.name', 'persons.created_at', 'persons.updated_at', 'persons.type')
            ->orderBy('persons.name')
            ->get();

        // Handle month parameter properly
        $monthParam = request('month', Carbon::now()->format('Y-m'));
        
        // Parse the month parameter to get year and month
        if (strpos($monthParam, '-') !== false) {
            [$year, $month] = explode('-', $monthParam);
        } else {
            $month = $monthParam;
            $year = Carbon::now()->format('Y');
        }

        // Ensure month and year are properly formatted
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $year = (int) $year;

        // Get previous month's service charge
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        
        // Fix: Use proper date filtering for sales
        $prevMonthSales = Sale::whereYear('updated_at', $prevMonth->year)
            ->whereMonth('updated_at', $prevMonth->month)
            ->where('sale_status', 'paid')
            ->sum('total_recieved');

        // Get current month's damage items
        $currentMonthDamages = DamageItem::whereYear('reported_date', $year)
            ->whereMonth('reported_date', $month)
            ->sum('total_cost');

        // Calculate total giving SC
        $totalGivingSC = max(0, $prevMonthSales - $currentMonthDamages);

        // Get points for each person
        $points = ServiceChargePoint::with('person')->get();
        $totalPoints = $points->sum('points');

        // Get processed service charges for current month
        $serviceCharges = ServiceCharge::with('person')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        // Generate months for dropdown (last 12 months)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }

        return view('service-charge.index', compact(
            'staff', 'points', 'totalPoints', 'totalGivingSC',
            'prevMonthSales', 'currentMonthDamages', 'serviceCharges',
            'month', 'year', 'months', 'monthParam'
        ));
    }

    public function updatePoints(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
            'points' => 'required|integer|min:0'
        ]);

        ServiceChargePoint::updateOrCreate(
            ['person_id' => $request->person_id],
            ['points' => $request->points]
        );

        return response()->json(['success' => true]);
    }

    public function generateServiceCharge(Request $request)
    {
        try {
            $person = Person::findOrFail($request->person_id);
            
            // Handle month parameter properly
            $monthParam = $request->month ?? Carbon::now()->format('Y-m');
            
            // Parse the month parameter to get year and month
            if (strpos($monthParam, '-') !== false) {
                [$year, $month] = explode('-', $monthParam);
            } else {
                $month = $monthParam;
                $year = Carbon::now()->format('Y');
            }

            // Ensure month and year are properly formatted
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $year = (int) $year;

            // Check if service charge already exists for this person and month
            $existingServiceCharge = ServiceCharge::where('person_id', $person->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->first();

            if ($existingServiceCharge) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service charge already generated for this month'
                ], 400);
            }

            // Get previous month's service charge
            $prevMonth = Carbon::create($year, $month, 1)->subMonth();
            
            $prevMonthSales = Sale::whereYear('updated_at', $prevMonth->year)
                ->whereMonth('updated_at', $prevMonth->month)
                ->where('sale_status', 'paid')
                ->sum('total_recieved');

            // Get current month's damage items
            $currentMonthDamages = DamageItem::whereYear('reported_date', $year)
                ->whereMonth('reported_date', $month)
                ->sum('total_cost');

            // Calculate total giving SC
            $totalGivingSC = max(0, $prevMonthSales - $currentMonthDamages);

            // Get all points
            $totalPoints = ServiceChargePoint::sum('points');
            $personPoints = ServiceChargePoint::where('person_id', $person->id)->first();

            if (!$personPoints || $totalPoints == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Points not assigned or total points is zero'
                ], 400);
            }

            // Calculate final amount
            $pointsRatio = $personPoints->points / $totalPoints;
            $finalAmount = $totalGivingSC * $pointsRatio;

            // Create service charge record
            $serviceCharge = ServiceCharge::create([
                'person_id' => $person->id,
                'month' => $month,
                'year' => $year,
                'total_sc' => $totalGivingSC,
                'points_ratio' => $pointsRatio,
                'final_amount' => $finalAmount,
                'remarks' => "Service Charge for " . Carbon::create()->month($month)->format('F') . " $year"
            ]);

            return response()->json([
                'success' => true,
                'serviceCharge' => $serviceCharge
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating service charge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printServiceCharge($id)
    {
        $serviceCharge = ServiceCharge::with('person')->findOrFail($id);
        return view('service-charge.print', compact('serviceCharge'));
    }

    public function managePoints()
    {
        $staff = DB::table('persons')
            ->join('staff_codes', 'persons.id', '=', 'staff_codes.person_id')
            ->where('staff_codes.is_active', 1)
            ->select('persons.id', 'persons.name', 'persons.created_at', 'persons.updated_at', 'persons.type')
            ->groupBy('persons.id', 'persons.name', 'persons.created_at', 'persons.updated_at', 'persons.type')
            ->orderBy('persons.name')
            ->get();

        $points = ServiceChargePoint::all()->keyBy('person_id');
        
        return view('service-charge.points', compact('staff', 'points'));
    }

    public function updatePointsBulk(Request $request)
    {
        try {
            $points = $request->validate([
                'points' => 'required|array',
                'points.*' => 'required|integer|min:0'
            ]);

            foreach ($points['points'] as $personId => $pointValue) {
                ServiceChargePoint::updateOrCreate(
                    ['person_id' => $personId],
                    ['points' => $pointValue]
                );
            }

            return redirect()->back()->with('success', 'Points updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating points: ' . $e->getMessage());
        }
    }
}