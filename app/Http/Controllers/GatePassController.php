<?php
// app/Http/Controllers/GatePassController.php

namespace App\Http\Controllers;

use App\Models\GatePass;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GatePassController extends Controller
{
    
// Update the index method to handle the new status logic
public function index(Request $request)
{
    $query = GatePass::with(['person', 'requestedBy', 'approvedBy']);

    // Filter by status if provided
    if ($request->has('status') && $request->status !== 'all') {
        if ($request->status === 'active') {
            // Show all non-returned passes as "active"
            $query->whereNotIn('status', ['returned']);
        } else {
            $query->where('status', $request->status);
        }
    }

    // Filter by date
    if ($request->has('date') && $request->date) {
        $query->whereDate('exit_time', $request->date);
    } else {
        // Default to today's passes
        $query->whereDate('exit_time', Carbon::today());
    }

    // Filter by staff member if provided
    if ($request->has('person_id') && $request->person_id) {
        $query->where('person_id', $request->person_id);
    }

    $gatePasses = $query->orderBy('exit_time', 'desc')->paginate(15);

    // Get staff members
    $staffMembers = Person::whereHas('staffCode', function($query) {
            $query->where('is_active', 1);
        })
        ->where('type', 'individual')
        ->with(['staffCode'])
        ->orderBy('name')
        ->get();

    // Get statistics
    $todayPasses = GatePass::whereDate('exit_time', Carbon::today());
    
    $stats = [
        'total_today' => $todayPasses->count(),
        'active' => $todayPasses->whereNotIn('status', ['returned'])->count(),
        'overdue' => GatePass::where('status', 'overdue')->count(),
        'returned' => $todayPasses->where('status', 'returned')->count()
    ];

    return view('gate-passes.index', compact('gatePasses', 'staffMembers', 'stats'));
}

    public function create()
    {
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory'])
            ->orderBy('name')
            ->get();
            
        return view('gate-passes.create', compact('staffMembers'));
    }

   public function store(Request $request)
{
    $request->validate([
        'person_id' => 'required|exists:persons,id',
        'exit_time' => 'required|date|after_or_equal:now',
        'duration_minutes' => 'required|integer|min:5|max:480',
        'purpose' => 'required|in:personal,official,emergency,medical,bank,post_office,market,other',
        'reason' => 'required|string|max:500',
        'destination' => 'nullable|string|max:255',
        'contact_number' => 'nullable|string|max:15',
        'vehicle_number' => 'nullable|string|max:20',
        'items_carried' => 'nullable|string|max:500'
    ]);

    // Calculate expected return time
    $exitTime = Carbon::parse($request->exit_time);
    $expectedReturn = $exitTime->copy()->addMinutes($request->duration_minutes);

    // Check for overlapping active gate passes
    $existing = GatePass::where('person_id', $request->person_id)
        ->whereIn('status', ['active', 'approved'])
        ->where(function($query) use ($exitTime, $expectedReturn) {
            $query->whereBetween('exit_time', [$exitTime, $expectedReturn])
                  ->orWhereBetween('expected_return', [$exitTime, $expectedReturn])
                  ->orWhere(function($q) use ($exitTime, $expectedReturn) {
                      $q->where('exit_time', '<=', $exitTime)
                        ->where('expected_return', '>=', $expectedReturn);
                  });
        })
        ->exists();

    if ($existing) {
        return back()->withErrors(['error' => 'This staff member already has an active gate pass during this time.'])
                    ->withInput();
    }

    $gatePass = new GatePass();
    $gatePassNumber = $gatePass->generateGatePassNumber();

    // Create gate pass and auto-approve it immediately
    GatePass::create([
        'person_id' => $request->person_id,
        'requested_by' => Auth::id(),
        'gate_pass_number' => $gatePassNumber,
        'exit_time' => $exitTime,
        'expected_return' => $expectedReturn,
        'duration_minutes' => $request->duration_minutes,
        'purpose' => $request->purpose,
        'reason' => $request->reason,
        'destination' => $request->destination,
        'contact_number' => $request->contact_number,
        'vehicle_number' => $request->vehicle_number,
        'items_carried' => $request->items_carried,
        'emergency_pass' => $request->purpose === 'emergency',
        'status' => 'active',  // Set directly to active (no approval needed)
        'approved_by' => Auth::id(),  // Auto-approved by creator
        'approved_at' => now()
    ]);

    return redirect()->route('gate-passes.index')
                    ->with('success', 'Gate pass created and activated successfully!');
}

    public function show(GatePass $gatePass)
    {
        $gatePass->load(['person', 'requestedBy', 'approvedBy']);
        return view('gate-passes.show', compact('gatePass'));
    }

    public function updateStatus(Request $request, GatePass $gatePass)
    {
        if (!Auth::user()->checkAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string|max:500'
        ]);

        $gatePass->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // If approved and exit time is now or past, mark as active
        if ($request->status === 'approved' && $gatePass->exit_time <= Carbon::now()) {
            $gatePass->update(['status' => 'active']);
        }

        $message = $request->status === 'approved' ? 'Gate pass approved successfully!' : 'Gate pass rejected.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $gatePass->status
            ]);
        }

        return redirect()->route('gate-passes.index')->with('success', $message);
    }

   public function markReturn(GatePass $gatePass)
{
    // Allow marking return for any non-returned gate pass
    if (in_array($gatePass->status, ['returned'])) {
        return response()->json([
            'success' => false,
            'message' => 'This gate pass has already been marked as returned.'
        ], 400);
    }

    // Mark as returned regardless of current status
    $gatePass->update([
        'status' => 'returned',
        'actual_return' => Carbon::now()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Staff member marked as returned successfully!'
    ]);
}
    public function print(GatePass $gatePass)
    {
        $gatePass->load(['person', 'requestedBy', 'approvedBy']);
        return view('gate-passes.print', compact('gatePass'));
    }

    public function dashboard()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_today' => GatePass::today()->count(),
            'pending' => GatePass::where('status', 'pending')->today()->count(),
            'active' => GatePass::active()->today()->count(),
            'overdue' => GatePass::overdue()->count(),
            'returned' => GatePass::where('status', 'returned')->today()->count()
        ];

        $activePasses = GatePass::with(['person'])
            ->whereIn('status', ['approved', 'active', 'overdue'])
            ->orderBy('expected_return')
            ->get();

        $overdueCount = $activePasses->where('status', 'overdue')->count();

        return view('gate-passes.dashboard', compact('stats', 'activePasses', 'overdueCount'));
    }
    // Add this method to handle automatic status updates based on time
public function updateOverdueStatus()
{
    // Update overdue passes
    GatePass::whereNotIn('status', ['returned'])
        ->where('expected_return', '<', Carbon::now())
        ->update(['status' => 'overdue']);
        
    // Activate passes that have reached their exit time
    GatePass::where('status', 'active')
        ->where('exit_time', '<=', Carbon::now())
        ->where('expected_return', '>', Carbon::now())
        ->update(['status' => 'active']);
}

    public function destroy(GatePass $gatePass)
    {
        // Only allow deletion if pending or user is admin
        if ($gatePass->status !== 'pending' && !Auth::user()->checkAdmin()) {
            return redirect()->route('gate-passes.index')
                            ->with('error', 'Cannot delete a processed gate pass.');
        }

        $gatePass->delete();

        return redirect()->route('gate-passes.index')
                        ->with('success', 'Gate pass deleted successfully!');
    }
}