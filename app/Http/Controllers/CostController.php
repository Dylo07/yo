<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use App\Models\Group;
use App\Models\Person; // Import the Person model
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index(Request $request)
{
    // Default to the current month if no filter is applied
    $month = $request->query('month', now()->format('Y-m'));

    // Default to today's date if no specific date is selected
    $selectedDate = $request->query('date', now()->format('Y-m-d'));

    // Fetch all costs filtered by the selected month
    $costs = Cost::with('group', 'person')
        ->whereYear('cost_date', substr($month, 0, 4))
        ->whereMonth('cost_date', substr($month, 5, 2))
        ->get();

    // Group costs by Group and Person for the month
    $monthlyGroupedCosts = $costs->groupBy('group.name')->map(function ($groupCosts) {
        return $groupCosts->groupBy('person.name')->map(function ($personCosts) {
            return [
                'costs' => $personCosts,
                'total' => $personCosts->sum('amount'),
            ];
        });
    });

    // Fetch costs for the selected date
    $dailyCosts = Cost::with('group', 'person')
        ->whereDate('cost_date', $selectedDate)
        ->get();

    // Group costs by Group and Person for the daily summary
    $dailyGroupedCosts = $dailyCosts->groupBy('group.name')->map(function ($groupCosts) {
        return $groupCosts->groupBy('person.name')->map(function ($personCosts) {
            return [
                'costs' => $personCosts,
                'total' => $personCosts->sum('amount'),
            ];
        });
    });

    // Calculate grand total for the entire month
    $grandTotal = $costs->sum('amount');

    return view('costs.index', compact('monthlyGroupedCosts', 'dailyGroupedCosts', 'month', 'selectedDate', 'grandTotal'));
}

    public function create()
    {
        $groups = Group::all();
        $persons = Person::all(); // Fetch all persons/shops
        return view('costs.create', compact('groups', 'persons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'person_id' => 'required|exists:persons,id',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
        ]);
    
        // Save the cost
        Cost::create([
            'group_id' => $request->group_id,
            'person_id' => $request->person_id,
            'amount' => $request->amount,
            'cost_date' => $request->cost_date,
        ]);
    
        return redirect()->route('costs.index')->with('success', 'Cost added successfully!');
    }

    public function update(Request $request, Cost $cost)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'person_or_shop' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
        ]);

        $cost->update($request->all());

        return redirect()->route('costs.index')->with('success', 'Cost updated successfully!');
    }

    public function destroy(Cost $cost)
    {
        $cost->delete();

        return redirect()->route('costs.index')->with('success', 'Cost deleted successfully!');
    }
}
