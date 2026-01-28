<?php

namespace App\Http\Controllers;

use App\Models\VehicleSecurity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleSecurityController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $filter = $request->input('filter', 'all');
        
        $query = VehicleSecurity::query();
        
        switch ($filter) {
            case 'in':
                $query->whereNull('checkout_time')
                      ->where('is_temp_out', false)
                      ->where('is_note', false);
                break;
            case 'out':
                $query->whereNotNull('checkout_time')
                      ->where('is_note', false);
                break;
            case 'temp':
                $query->whereNull('checkout_time')
                      ->where('is_temp_out', true)
                      ->where('is_note', false);
                break;
            case 'today':
                $query->whereDate('created_at', $today)
                      ->where('is_note', false);
                break;
            case 'room':
                $query->whereNull('checkout_time')
                      ->whereNotNull('room_numbers')
                      ->where('room_numbers', '<>', '[]')
                      ->where('is_note', false);
                break;
            case 'pool':
                $query->whereNull('checkout_time')
                      ->where(function($q) {
                          $q->where('adult_pool_count', '>', 0)
                            ->orWhere('kids_pool_count', '>', 0);
                      })
                      ->where('is_note', false);
                break;
            default:
                // Default 'all' filter 
                $query->where(function($query) use ($today) {
                    $query->where(function($q) {
                        // Show all unchecked vehicles
                        $q->whereNull('checkout_time')
                          ->where('is_note', false);
                    })->orWhere(function($q) use ($today) {
                        // Show vehicles checked out today
                        $q->whereDate('checkout_time', $today)
                          ->where('is_note', false);
                    })->orWhere(function($q) use ($today) {
                        // Show today's new entries
                        $q->whereDate('created_at', $today)
                          ->where('is_note', false);
                    })->orWhere(function($q) use ($today) {
                        // Show only today's notes
                        $q->whereDate('created_at', $today)
                          ->where('is_note', true);
                    });
                });
                break;
        }
        
        $vehicles = $query->latest()->get();
    
        // Get all occupied rooms from unchecked out vehicles
        $occupiedRooms = VehicleSecurity::whereNull('checkout_time')
            ->whereNotNull('room_numbers')
            ->where('is_note', false)
            ->get()
            ->pluck('room_numbers')
            ->map(function($rooms) {
                return json_decode($rooms) ?: [];
            })
            ->flatten()
            ->unique()
            ->values();
    
        // Get available rooms
        $allRooms = VehicleSecurity::getRoomOptions();
        $availableRooms = array_values(array_diff($allRooms, $occupiedRooms->toArray()));
        
        // Get dashboard statistics
        $stats = $this->getStats();
    
        return view('vehicle-security.index', [
            'vehicles' => $vehicles,
            'selectedDate' => $request->input('date', $today->format('Y-m-d')),
            'selectedFilter' => $filter,
            'matterOptions' => VehicleSecurity::getMatterOptions(),
            'roomOptions' => VehicleSecurity::getRoomOptions(),
            'availableRooms' => $availableRooms,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get dashboard statistics for the vehicle security system
     * 
     * @return array
     */
    private function getStats()
    {
        $today = Carbon::today();
        
        // Get vehicles on property (unchecked out)
        $checkedIn = VehicleSecurity::whereNull('checkout_time')
            ->where('is_note', false)
            ->count();
        
        // Get vehicles checked out today
        $checkedOut = VehicleSecurity::whereDate('checkout_time', $today)
            ->where('is_note', false)
            ->count();
        
        // Get temporarily out vehicles
        $tempOut = VehicleSecurity::whereNull('checkout_time')
            ->where('is_temp_out', true)
            ->where('is_note', false)
            ->count();
        
        // Get occupied and available rooms
        $occupiedRooms = VehicleSecurity::whereNull('checkout_time')
            ->whereNotNull('room_numbers')
            ->where('is_note', false)
            ->get()
            ->pluck('room_numbers')
            ->map(function($rooms) {
                return json_decode($rooms) ?: [];
            })
            ->flatten()
            ->unique()
            ->count();
        
        $allRoomsCount = count(VehicleSecurity::getRoomOptions());
        $availableRooms = $allRoomsCount - $occupiedRooms;
        
        // Get pool usage
        $poolUsage = VehicleSecurity::whereNull('checkout_time')
            ->where('is_note', false)
            ->selectRaw('COALESCE(SUM(adult_pool_count), 0) as adults, COALESCE(SUM(kids_pool_count), 0) as kids')
            ->first();
        
        return [
            'totalVehicles' => $checkedIn + $checkedOut,
            'checkedIn' => $checkedIn,
            'checkedOut' => $checkedOut,
            'tempOut' => $tempOut,
            'occupiedRooms' => $occupiedRooms,
            'availableRooms' => $availableRooms,
            'poolUsage' => [
                'adults' => (int)$poolUsage->adults,
                'kids' => (int)$poolUsage->kids
            ]
        ];
    }
    
    /**
     * API endpoint to get dashboard statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        return response()->json($this->getStats());
    }

    /**
     * Get daily vehicle security summary for a specific date
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailySummary(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // Get vehicles currently on property (checked in but not checked out)
        $vehiclesOnProperty = VehicleSecurity::whereNull('checkout_time')
            ->where('is_note', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get vehicles checked out on the selected date
        $vehiclesCheckedOutToday = VehicleSecurity::whereBetween('checkout_time', [$dateStart, $dateEnd])
            ->where('is_note', false)
            ->orderBy('checkout_time', 'desc')
            ->get();

        // Get vehicles checked in on the selected date (for reference)
        $vehiclesCheckedInToday = VehicleSecurity::whereBetween('created_at', [$dateStart, $dateEnd])
            ->where('is_note', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine for display: currently on property + checked out today
        $allRelevantVehicles = $vehiclesOnProperty->merge($vehiclesCheckedOutToday)->unique('id');

        $totalVehicles = $allRelevantVehicles->count();
        
        // Count by status
        $checkedIn = $vehiclesOnProperty->filter(function($v) {
            return !$v->is_temp_out;
        })->count();
        
        $checkedOut = $vehiclesCheckedOutToday->count();
        
        $tempOut = $vehiclesOnProperty->filter(function($v) {
            return $v->is_temp_out;
        })->count();

        // Group by matter (purpose) - show ALL relevant vehicles (on property + checked out today)
        $byMatter = $allRelevantVehicles->groupBy('matter')->map(function($group, $key) {
            return [
                'name' => $key ?: 'General',
                'count' => $group->count(),
                'vehicles' => $group->map(function($v) {
                    $isCheckedOut = !is_null($v->checkout_time);
                    return [
                        'id' => $v->id,
                        'vehicle_number' => $v->vehicle_number,
                        'matter' => $v->matter,
                        'description' => $v->description,
                        'room_numbers' => $v->room_numbers,
                        'adult_pool_count' => $v->adult_pool_count,
                        'kids_pool_count' => $v->kids_pool_count,
                        'team' => $v->team,
                        'check_in' => $v->created_at->format('M d, H:i'),
                        'check_out' => $isCheckedOut ? $v->checkout_time->format('M d, H:i') : null,
                        'status' => $isCheckedOut ? 'Checked Out' : ($v->is_temp_out ? 'Temp Out' : 'On Property')
                    ];
                })->values()
            ];
        })->sortByDesc('count')->values();

        // Pool usage - from vehicles currently on property
        $poolUsage = [
            'adults' => $vehiclesOnProperty->sum('adult_pool_count'),
            'kids' => $vehiclesOnProperty->sum('kids_pool_count')
        ];

        // Room occupancy - from vehicles currently on property
        $roomsUsed = $vehiclesOnProperty->filter(function($v) {
            return !empty($v->room_numbers);
        })->pluck('room_numbers')->flatten()->unique()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_vehicles' => $totalVehicles,
                'checked_in' => $checkedIn,
                'checked_out' => $checkedOut,
                'temp_out' => $tempOut,
                'on_property' => $vehiclesOnProperty->count(),
                'checked_in_today' => $vehiclesCheckedInToday->count(),
                'by_matter' => $byMatter,
                'pool_usage' => $poolUsage,
                'rooms_used' => $roomsUsed,
                'date' => $date,
            ]
        ]);
    }

    public function showByDate($date = null)
    {
        try {
            // If no date provided, use today's date
            $selectedDate = $date ? Carbon::parse($date) : Carbon::today();
            $filter = request()->input('filter', 'all');
            
            $query = VehicleSecurity::query();
            
            // Apply filter first
            switch ($filter) {
                case 'in':
                    $query->whereNull('checkout_time')
                          ->where('is_temp_out', false)
                          ->where('is_note', false);
                    break;
                case 'out':
                    $query->whereNotNull('checkout_time')
                          ->where('is_note', false);
                    break;
                case 'temp':
                    $query->whereNull('checkout_time')
                          ->where('is_temp_out', true)
                          ->where('is_note', false);
                    break;
                case 'room':
                    $query->whereNull('checkout_time')
                          ->whereNotNull('room_numbers')
                          ->where('room_numbers', '<>', '[]')
                          ->where('is_note', false);
                    break;
                case 'pool':
                    $query->whereNull('checkout_time')
                          ->where(function($q) {
                              $q->where('adult_pool_count', '>', 0)
                                ->orWhere('kids_pool_count', '>', 0);
                          })
                          ->where('is_note', false);
                    break;
                default:
                    // Only after applying filter, apply date constraints
                    $query->where(function($query) use ($selectedDate) {
                        $query->where(function($q) {
                            // Show all unchecked vehicles
                            $q->whereNull('checkout_time')
                              ->where('is_note', false);
                        })->orWhere(function($q) use ($selectedDate) {
                            // Show vehicles checked out on selected date
                            $q->whereDate('checkout_time', $selectedDate)
                              ->where('is_note', false);
                        })->orWhere(function($q) use ($selectedDate) {
                            // Show selected date's new entries
                            $q->whereDate('created_at', $selectedDate)
                              ->where('is_note', false);
                        })->orWhere(function($q) use ($selectedDate) {
                            // Show only selected date's notes
                            $q->whereDate('created_at', $selectedDate)
                              ->where('is_note', true);
                        });
                    });
                    break;
            }
            
            // If we're using a filter other than 'all', and it's not today's date
            // additionally restrict to that date
            if ($filter !== 'all' && !$selectedDate->isToday()) {
                $query->whereDate('created_at', $selectedDate);
            }

            $vehicles = $query->latest()->get();

            // Get all occupied rooms from unchecked out vehicles
            $occupiedRooms = VehicleSecurity::whereNull('checkout_time')
                ->whereNotNull('room_numbers')
                ->where('is_note', false)
                ->get()
                ->pluck('room_numbers')
                ->map(function($rooms) {
                    return json_decode($rooms) ?: [];
                })
                ->flatten()
                ->unique()
                ->values();

            // Get available rooms
            $allRooms = VehicleSecurity::getRoomOptions();
            $availableRooms = array_values(array_diff($allRooms, $occupiedRooms->toArray()));
            
            // Get dashboard statistics
            $stats = $this->getStats();

            return view('vehicle-security.index', [
                'vehicles' => $vehicles,
                'selectedDate' => $selectedDate->format('Y-m-d'),
                'selectedFilter' => $filter,
                'matterOptions' => VehicleSecurity::getMatterOptions(),
                'roomOptions' => VehicleSecurity::getRoomOptions(),
                'availableRooms' => $availableRooms,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date format: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string',
            'matter' => 'required|string',
            'description' => 'nullable|string',
            'room_numbers' => 'nullable|json',
            'adult_pool_count' => 'nullable|integer|min:0',
            'kids_pool_count' => 'nullable|integer|min:0',
            'is_note' => 'boolean'
        ]);
    
        $validated['is_note'] = $request->input('is_note', false);
    
        $vehicle = VehicleSecurity::create($validated);
    
        return response()->json([
            'success' => true,
            'message' => $validated['is_note'] ? 'Note saved successfully' : 'Vehicle entry created successfully',
            'vehicle' => $vehicle
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string',
            'matter' => 'required|string',
            'description' => 'nullable|string',
           'room_numbers' => 'nullable|json',
            'adult_pool_count' => 'nullable|integer|min:0',
            'kids_pool_count' => 'nullable|integer|min:0',
        ]);

        $vehicle = VehicleSecurity::findOrFail($id);
        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle entry updated successfully',
            'vehicle' => $vehicle->fresh()
        ]);
    }

    public function checkout($id)
    {
        $vehicle = VehicleSecurity::findOrFail($id);
        $checkoutTime = now();
        $startTime = $vehicle->created_at;
        
        // Calculate duration in hours with decimal points
        $duration = $startTime->diffInMinutes($checkoutTime) / 60;
        $formattedDuration = number_format($duration, 1);
        
        $vehicle->update([
            'checkout_time' => $checkoutTime,
            'duration_hours' => $formattedDuration
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Vehicle checked out successfully',
            'checkout_time' => $checkoutTime->format('Y-m-d H:i'),
            'duration_hours' => $formattedDuration,
            'vehicle' => $vehicle->fresh()
        ]);
    }
    
    public function updateTeam(Request $request, $id)
    {
        $validated = $request->validate([
            'team' => 'nullable|string'
        ]);

        $vehicle = VehicleSecurity::findOrFail($id);
        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'vehicle' => $vehicle->fresh()
        ]);
    }

    public function tempCheckout($id)
    {
        $vehicle = VehicleSecurity::findOrFail($id);
        $vehicle->update([
            'temp_checkout_time' => now(),
            'is_temp_out' => true,
            'temp_checkin_time' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle temporarily checked out',
            'vehicle' => $vehicle->fresh()
        ]);
    }

    public function tempCheckin($id)
    {
        $vehicle = VehicleSecurity::findOrFail($id);
        $vehicle->update([
            'temp_checkin_time' => now(),
            'is_temp_out' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle checked back in',
            'vehicle' => $vehicle->fresh()
        ]);
    }

    public function edit($id)
    {
        $vehicle = VehicleSecurity::findOrFail($id);
        return response()->json($vehicle);
    }
}