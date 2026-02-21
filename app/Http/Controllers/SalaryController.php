<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Person;
use App\Models\ManualAttendance;
use App\Models\Cost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index()
    {
        // SOLUTION 1: Use Eloquent instead of Query Builder (RECOMMENDED)
        $staff = Person::whereHas('staffCode', function($query) {
            $query->where('is_active', 1);
        })->orderBy('name')->get();

        /* 
        // SOLUTION 2: If you must use Query Builder, use DISTINCT
        $staff = DB::table('persons')
            ->join('staff_codes', 'persons.id', '=', 'staff_codes.person_id')
            ->where('staff_codes.is_active', 1)
            ->select('persons.*')
            ->distinct()
            ->orderBy('persons.name')
            ->get();
        */

        /* 
        // SOLUTION 3: Add full_name to GROUP BY if it exists (WORKING SOLUTION)
        $staff = DB::table('persons')
            ->join('staff_codes', 'persons.id', '=', 'staff_codes.person_id')
            ->where('staff_codes.is_active', 1)
            ->select('persons.*')
            ->groupBy(
                'persons.id', 
                'persons.name', 
                'persons.full_name',  // ADD THIS LINE
                'persons.created_at', 
                'persons.updated_at', 
                'persons.type', 
                'persons.basic_salary',
                'persons.id_card_number',
                'persons.address',
                'persons.phone_number',
                'persons.emergency_contact',
                'persons.emergency_phone',
                'persons.date_of_birth',
                'persons.gender',
                'persons.position',
                'persons.hire_date',
                'persons.blood_group',
                'persons.email',
                'persons.notes'
            )
            ->orderBy('persons.name')
            ->get();
        */

        /* 
        // SOLUTION 4: Use subquery approach
        $staffIds = DB::table('staff_codes')
            ->where('is_active', 1)
            ->pluck('person_id');
            
        $staff = DB::table('persons')
            ->whereIn('id', $staffIds)
            ->orderBy('name')
            ->get();
        */

        // Auto-advance to next month after the 10th
        // After Feb 10, show March salary (since Feb salary was already paid)
        $defaultDate = Carbon::now();
        if ($defaultDate->day > 10) {
            $defaultDate->addMonth(); // Move to next month
        }
        
        $month = request('month', $defaultDate->format('m'));
        $year = request('year', $defaultDate->format('Y'));
        
        // Calculate salary advance period for selected month
        // Example: For February salary (paid 10th Feb) → Period is Jan 10 to Feb 10
        $periodStart = Carbon::create($year, $month, 10)->subMonth()->startOfDay(); // Previous month 10th
        $periodEnd = Carbon::create($year, $month, 10)->endOfDay(); // Current month 10th

        // Fetch salary advances (use cost_date, not created_at)
        $salaryAdvances = Cost::with(['person', 'user'])
            ->where('group_id', 1)
            ->whereBetween('cost_date', [$periodStart, $periodEnd])
            ->orderBy('cost_date', 'desc')
            ->get();

        $totalAdvance = $salaryAdvances->sum('amount');

        // Get attendance data for work month (previous month)
        // March salary = February work, so show February attendance
        $workMonth = Carbon::create($year, $month)->subMonth();
        $attendanceData = [];
        foreach ($staff as $employee) {
            $attendance = ManualAttendance::where('person_id', $employee->id)
                ->whereYear('attendance_date', $workMonth->year)
                ->whereMonth('attendance_date', $workMonth->month)
                ->get();

            if ($attendance->count() > 0) {
                $attendanceData[$employee->id] = [
                    'present' => $attendance->where('status', 'present')->count(),
                    'half' => $attendance->where('status', 'half')->count(),
                    'absent' => $attendance->where('status', 'absent')->count()
                ];
            }
        }

        // Get processed salaries
        $salaries = Salary::with('person')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        // Calculate periods for dropdown
        $periods = [];
        for ($i = 0; $i < 3; $i++) {
            $basePeriod = Carbon::create($year, $month)->subMonths($i);
            $pStart = Carbon::create($basePeriod->year, $basePeriod->month, 10, 0, 0, 0);
            $pEnd = $basePeriod->copy()->addMonth()->setDay(10)->setTime(23, 59, 59);

            $periods[] = [
                'start' => $pStart,
                'end' => $pEnd,
                'label' => $pStart->format('M d, Y') . ' - ' . $pEnd->format('M d, Y')
            ];
        }

        $selectedPeriod = request('period', 0);
        
        return view('salary.index', compact(
            'staff', 'salaries', 'month', 'year',
            'periods', 'selectedPeriod', 'salaryAdvances', 
            'totalAdvance', 'attendanceData'
        ));
    }

    public function updateBasicSalary(Request $request)
    {
        $person = Person::findOrFail($request->person_id);
        $person->basic_salary = $request->basic_salary;
        $person->save();

        return response()->json(['success' => true]);
    }

    public function calculate(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        $personId = $request->person_id;
        
        $person = Person::findOrFail($personId);
        $basicSalary = $person->basic_salary;

        if (!$basicSalary) {
            return response()->json([
                'success' => false,
                'message' => 'Please assign basic salary first'
            ], 400);
        }

        // Calculate advance period - Previous month 10th to current month 10th
        // Example: For February salary (paid 10th Feb) → Period is Jan 10 to Feb 10
        $periodStart = Carbon::create($year, $month, 10)->subMonth()->startOfDay();
        $periodEnd = Carbon::create($year, $month, 10)->endOfDay();

        // Get attendance data for work month (previous month)
        // March salary = February work, so show February attendance
        $workMonth = Carbon::create($year, $month)->subMonth();
        $attendance = ManualAttendance::where('person_id', $personId)
            ->whereYear('attendance_date', $workMonth->year)
            ->whereMonth('attendance_date', $workMonth->month)
            ->get();

        // Get salary advance (use cost_date for accurate period matching)
        $salaryAdvance = Cost::where('group_id', 1)
            ->where('person_id', $personId)
            ->whereBetween('cost_date', [$periodStart, $periodEnd])
            ->sum('amount') ?? 0;

        $presentDays = $attendance->where('status', 'present')->count();
        $halfDays = $attendance->where('status', 'half')->count();
        $absentDays = $attendance->where('status', 'absent')->count();
        $presentDays += $halfDays * 0.5;

        // Calculate leave days (total days off including half days)
        $leaveDays = $absentDays + ($halfDays * 0.5);

        // Formula with 5 days leave allowance:
        // If Leave <= 5: Bonus = Basic/30 × (5 - Leave)
        // If Leave > 5: Deduction = Basic/25 × (Leave - 5)
        if ($leaveDays == 5) {
            $finalSalary = $basicSalary - $salaryAdvance;
            $remarks = "Leave: $leaveDays days (standard), No adjustment";
        } elseif ($leaveDays < 5) {
            $extraDays = 5 - $leaveDays;
            $bonus = ($basicSalary / 30) * $extraDays;
            $finalSalary = $basicSalary - $salaryAdvance + $bonus;
            $remarks = "Leave: $leaveDays days, Bonus: Rs. " . number_format($bonus, 2) . " for $extraDays extra days";
        } else {
            $excessLeave = $leaveDays - 5;
            $deduction = ($basicSalary / 25) * $excessLeave;
            $finalSalary = $basicSalary - $salaryAdvance - $deduction;
            $remarks = "Leave: $leaveDays days, Deduction: Rs. " . number_format($deduction, 2) . " for $excessLeave excess days";
        }

        return $this->processSalary(
            $personId, $month, $year, $basicSalary, $salaryAdvance, 
            $absentDays, $presentDays, $finalSalary,
            $remarks
        );
    }

    private function processSalary($personId, $month, $year, $basicSalary, $salaryAdvance, 
                                 $absentDays, $presentDays, $finalSalary, $remarks)
    {
        $salary = new Salary([
            'person_id' => $personId,
            'month' => $month,
            'year' => $year,
            'basic_salary' => $basicSalary,
            'salary_advance' => $salaryAdvance,
            'days_off' => $absentDays,
            'present_days' => $presentDays,
            'final_salary' => $finalSalary,
            'remarks' => $remarks
        ]);

        $salary->save();

        return response()->json([
            'success' => true,
            'salary' => $salary
        ]);
    }

    public function generatePayslip(Request $request)
    {
        $person = Person::findOrFail($request->person_id);
        $month = request('month', Carbon::now()->format('m'));
        $year = request('year', Carbon::now()->format('Y'));

        // Get attendance data for work month (previous month)
        // March salary = February work, so show February attendance
        $workMonth = Carbon::create($year, $month)->subMonth();
        $attendance = ManualAttendance::where('person_id', $person->id)
            ->whereYear('attendance_date', $workMonth->year)
            ->whereMonth('attendance_date', $workMonth->month)
            ->get();

        // Calculate salary advance period - Previous month 10th to current month 10th
        // Example: For February salary (paid 10th Feb) → Period is Jan 10 to Feb 10
        $periodStart = Carbon::create($year, $month, 10)->subMonth()->startOfDay();
        $periodEnd = Carbon::create($year, $month, 10)->endOfDay();

        // Get salary advance (use cost_date for accurate period matching)
        $salaryAdvance = Cost::where('group_id', 1)
            ->where('person_id', $person->id)
            ->whereBetween('cost_date', [$periodStart, $periodEnd])
            ->sum('amount') ?? 0;

        $presentDays = $attendance->where('status', 'present')->count();
        $halfDays = $attendance->where('status', 'half')->count();
        $absentDays = $attendance->where('status', 'absent')->count();
        $presentDays += $halfDays * 0.5;

        // Calculate leave days (total days off including half days)
        $leaveDays = $absentDays + ($halfDays * 0.5);

        // Formula with 5 days leave allowance:
        // If Leave <= 5: Bonus = Basic/30 × (5 - Leave)
        // If Leave > 5: Deduction = Basic/25 × (Leave - 5)
        if ($leaveDays == 5) {
            $finalSalary = $person->basic_salary - $salaryAdvance;
            $remarks = "Leave: $leaveDays days (standard), No adjustment";
        } elseif ($leaveDays < 5) {
            $extraDays = 5 - $leaveDays;
            $bonus = ($person->basic_salary / 30) * $extraDays;
            $finalSalary = $person->basic_salary - $salaryAdvance + $bonus;
            $remarks = "Leave: $leaveDays days, Bonus: Rs. " . number_format($bonus, 2) . " for $extraDays extra days";
        } else {
            $excessLeave = $leaveDays - 5;
            $deduction = ($person->basic_salary / 25) * $excessLeave;
            $finalSalary = $person->basic_salary - $salaryAdvance - $deduction;
            $remarks = "Leave: $leaveDays days, Deduction: Rs. " . number_format($deduction, 2) . " for $excessLeave excess days";
        }

        // Create a temporary salary object (not saved to database)
        $salary = new Salary([
            'person_id' => $person->id,
            'month' => $month,
            'year' => $year,
            'basic_salary' => $person->basic_salary,
            'salary_advance' => $salaryAdvance,
            'days_off' => $absentDays,
            'present_days' => $presentDays,
            'final_salary' => $finalSalary,
            'remarks' => $remarks
        ]);

        $salary->person = $person;  // Set the relationship manually

        return view('salary.payslip', compact('salary'));
    }
    
    public function basicSalary()
    {
        // Use the same approach as in index method
        $staff = Person::whereHas('staffCode', function($query) {
            $query->where('is_active', 1);
        })->get();

        return view('salary.basic', compact('staff'));
    }

    public function updateBasic(Request $request)
    {
        $person = Person::findOrFail($request->person_id);
        $person->basic_salary = $request->basic_salary;
        $person->save();

        return response()->json(['success' => true]);
    }
    
    public function payslip($id)
    {
        $salary = Salary::with('person')->findOrFail($id);
        return view('salary.payslip', compact('salary'));
    }
}