<?php

namespace App\Http\Controllers;

use App\Models\VehicleSecurity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleSecurityController extends Controller
{
    public function index()
    {
        $vehicles = VehicleSecurity::where(function($query) {
            $query->whereDate('checkout_time', Carbon::today())
                  ->orWhereNull('checkout_time')
                  ->orWhereDate('created_at', Carbon::today());
        })->latest()->get();

        return view('vehicle-security.index', [
            'vehicles' => $vehicles,
            'matterOptions' => VehicleSecurity::getMatterOptions(),
            'roomOptions' => VehicleSecurity::getRoomOptions()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string',
            'matter' => 'required|string',
            'description' => 'nullable|string',
            'room_numbers' => 'nullable|string',
            'adult_pool_count' => 'nullable|integer|min:0',
            'kids_pool_count' => 'nullable|integer|min:0',
        ]);

        $vehicle = VehicleSecurity::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle entry created successfully',
            'vehicle' => $vehicle
        ]);
    }

    public function showByDate($date)
    {
        $vehicles = VehicleSecurity::where(function($query) use ($date) {
            $query->whereDate('checkout_time', $date)
                  ->orWhereNull('checkout_time')
                  ->orWhereDate('created_at', $date);
        })->latest()->get();

        return view('vehicle-security.index', [
            'vehicles' => $vehicles,
            'selectedDate' => $date,
            'matterOptions' => VehicleSecurity::getMatterOptions(),
            'roomOptions' => VehicleSecurity::getRoomOptions()
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string',
            'matter' => 'required|string',
            'description' => 'nullable|string',
           'room_numbers' => 'nullable|string',
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
        $vehicle->update(['checkout_time' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle checked out successfully',
            'checkout_time' => $vehicle->checkout_time->format('Y-m-d H:i'),
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