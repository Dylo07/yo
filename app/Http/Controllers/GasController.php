<?php

namespace App\Http\Controllers;

use App\Models\GasCylinder;
use App\Models\GasPurchase;
use App\Models\GasIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the gas management dashboard
     */
    public function index()
    {
        $cylinders = GasCylinder::where('is_active', true)->get();
        
        // Get current month stats
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        
        // Monthly purchases
        $monthlyPurchases = GasPurchase::whereBetween('purchase_date', [$currentMonth, $currentMonthEnd])
            ->sum('filled_received');
        $monthlyPurchaseAmount = GasPurchase::whereBetween('purchase_date', [$currentMonth, $currentMonthEnd])
            ->sum('total_amount');
            
        // Monthly issues
        $monthlyIssues = GasIssue::whereBetween('issue_date', [$currentMonth, $currentMonthEnd])
            ->sum('quantity');
            
        // Today's stats
        $today = Carbon::today();
        $todayPurchases = GasPurchase::whereDate('purchase_date', $today)->sum('filled_received');
        $todayIssues = GasIssue::whereDate('issue_date', $today)->sum('quantity');
        
        // Total stock value (only filled cylinders have value)
        $totalStockValue = $cylinders->sum(function($c) {
            return $c->filled_stock * $c->price;
        });
        
        // Total filled stock
        $totalFilledStock = $cylinders->sum('filled_stock');
        
        // Total empty stock
        $totalEmptyStock = $cylinders->sum('empty_stock');
        
        // Low stock alerts
        $lowStockCylinders = $cylinders->filter(function($c) {
            return $c->isLowStock();
        });
        
        // Recent transactions (last 10)
        $recentPurchases = GasPurchase::with(['gasCylinder', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $recentIssues = GasIssue::with(['gasCylinder', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('gas.index', compact(
            'cylinders',
            'monthlyPurchases',
            'monthlyPurchaseAmount',
            'monthlyIssues',
            'todayPurchases',
            'todayIssues',
            'totalStockValue',
            'totalFilledStock',
            'totalEmptyStock',
            'lowStockCylinders',
            'recentPurchases',
            'recentIssues'
        ));
    }

    /**
     * Store a new gas cylinder type
     */
    public function storeCylinder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight_kg' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        GasCylinder::create([
            'name' => $request->name,
            'weight_kg' => $request->weight_kg,
            'price' => $request->price,
            'filled_stock' => 0,
            'empty_stock' => 0,
            'minimum_stock' => $request->minimum_stock,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Gas cylinder type added successfully!');
    }

    /**
     * Update gas cylinder details
     */
    public function updateCylinder(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight_kg' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        $cylinder = GasCylinder::findOrFail($id);
        $cylinder->update([
            'name' => $request->name,
            'weight_kg' => $request->weight_kg,
            'price' => $request->price,
            'minimum_stock' => $request->minimum_stock,
        ]);

        return redirect()->back()->with('success', 'Gas cylinder updated successfully!');
    }

    /**
     * Record a gas exchange (incoming filled, outgoing empty)
     */
    public function storePurchase(Request $request)
    {
        $request->validate([
            'gas_cylinder_id' => 'required|exists:gas_cylinders,id',
            'filled_received' => 'required|integer|min:1',
            'empty_returned' => 'required|integer|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'dealer_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $cylinder = GasCylinder::findOrFail($request->gas_cylinder_id);
        
        // Validate we have enough empty cylinders to return
        if ($request->empty_returned > $cylinder->empty_stock) {
            return redirect()->back()->with('error', 'Insufficient empty cylinders! Available: ' . $cylinder->empty_stock);
        }

        DB::transaction(function () use ($request, $cylinder) {
            $totalAmount = $request->filled_received * $request->price_per_unit;

            GasPurchase::create([
                'gas_cylinder_id' => $request->gas_cylinder_id,
                'filled_received' => $request->filled_received,
                'empty_returned' => $request->empty_returned,
                'price_per_unit' => $request->price_per_unit,
                'total_amount' => $totalAmount,
                'dealer_name' => $request->dealer_name,
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            // Update stock: add filled, remove empty
            $cylinder->increment('filled_stock', $request->filled_received);
            $cylinder->decrement('empty_stock', $request->empty_returned);
            
            // Optionally update the price
            if ($request->has('update_price') && $request->update_price) {
                $cylinder->update(['price' => $request->price_per_unit]);
            }
        });

        return redirect()->back()->with('success', 'Gas exchange recorded successfully!');
    }

    /**
     * Record a gas issue (filled to kitchen, becomes empty)
     */
    public function storeIssue(Request $request)
    {
        $request->validate([
            'gas_cylinder_id' => 'required|exists:gas_cylinders,id',
            'quantity' => 'required|integer|min:1',
            'issued_to' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $cylinder = GasCylinder::findOrFail($request->gas_cylinder_id);
        
        if ($cylinder->filled_stock < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient filled cylinders! Available: ' . $cylinder->filled_stock);
        }

        DB::transaction(function () use ($request, $cylinder) {
            GasIssue::create([
                'gas_cylinder_id' => $request->gas_cylinder_id,
                'quantity' => $request->quantity,
                'issued_to' => $request->issued_to,
                'issue_date' => $request->issue_date,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            // Update stock: remove from filled, add to empty
            $cylinder->decrement('filled_stock', $request->quantity);
            $cylinder->increment('empty_stock', $request->quantity);
        });

        return redirect()->back()->with('success', 'Gas issued successfully! Cylinders moved to empty stock.');
    }

    /**
     * Get usage statistics for charts
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'month'); // month, year
        
        if ($period === 'year') {
            // Last 12 months
            $stats = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                
                $purchases = GasPurchase::whereBetween('purchase_date', [$monthStart, $monthEnd])->sum('filled_received');
                $issues = GasIssue::whereBetween('issue_date', [$monthStart, $monthEnd])->sum('quantity');
                
                $stats[] = [
                    'label' => $date->format('M Y'),
                    'purchases' => $purchases,
                    'issues' => $issues,
                ];
            }
        } else {
            // Last 30 days
            $stats = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                $purchases = GasPurchase::whereDate('purchase_date', $date)->sum('filled_received');
                $issues = GasIssue::whereDate('issue_date', $date)->sum('quantity');
                
                $stats[] = [
                    'label' => $date->format('d M'),
                    'purchases' => $purchases,
                    'issues' => $issues,
                ];
            }
        }
        
        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Get transaction history
     */
    public function getHistory(Request $request)
    {
        $type = $request->get('type', 'all'); // all, purchases, issues
        $cylinderId = $request->get('cylinder_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $purchases = collect();
        $issues = collect();
        
        if ($type === 'all' || $type === 'purchases') {
            $query = GasPurchase::with(['gasCylinder', 'user']);
            if ($cylinderId) $query->where('gas_cylinder_id', $cylinderId);
            if ($startDate) $query->whereDate('purchase_date', '>=', $startDate);
            if ($endDate) $query->whereDate('purchase_date', '<=', $endDate);
            $purchases = $query->orderBy('purchase_date', 'desc')->get();
        }
        
        if ($type === 'all' || $type === 'issues') {
            $query = GasIssue::with(['gasCylinder', 'user']);
            if ($cylinderId) $query->where('gas_cylinder_id', $cylinderId);
            if ($startDate) $query->whereDate('issue_date', '>=', $startDate);
            if ($endDate) $query->whereDate('issue_date', '<=', $endDate);
            $issues = $query->orderBy('issue_date', 'desc')->get();
        }
        
        return response()->json([
            'success' => true,
            'purchases' => $purchases,
            'issues' => $issues,
        ]);
    }

    /**
     * Delete a cylinder type (soft delete by deactivating)
     */
    public function deleteCylinder($id)
    {
        $cylinder = GasCylinder::findOrFail($id);
        $cylinder->update(['is_active' => false]);
        
        return redirect()->back()->with('success', 'Gas cylinder type deactivated!');
    }
}
