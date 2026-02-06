<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\FoodMenu;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FoodMenuController extends Controller
{
    /**
     * Display the food menu generator view
     */
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $bookingId = $request->input('booking_id');
        
        // Find all bookings for the selected date
        $bookings = Booking::where(function($query) use ($date) {
            $day = Carbon::parse($date);
            
            $query->whereDate('start', '<=', $day->format('Y-m-d'))
                  ->whereDate('end', '>=', $day->format('Y-m-d'))
                  ->whereNotNull('end');
                  
            $query->orWhere(function($q) use ($day) {
                $q->whereDate('start', $day->format('Y-m-d'))
                  ->where(function($sq) {
                      $sq->whereNull('end')
                         ->orWhere('end', 'N/A')
                         ->orWhere('end', '');
                  });
            });
        })->get();
        
        // Check if each booking has a food menu for this date
        $bookings->map(function($booking) use ($date) {
            $booking->food_menu_exists = FoodMenu::where('booking_id', $booking->id)
                                               ->where('date', $date)
                                               ->exists();
            return $booking;
        });
        
        $selectedBooking = null;
        $menu = null;
        
        // If a booking ID is provided, load that specific booking
        if ($bookingId) {
            $selectedBooking = Booking::find($bookingId);
            
            if ($selectedBooking) {
                $menu = FoodMenu::where('booking_id', $bookingId)
                              ->where('date', $date)
                              ->first();
            }
        }
        
        // Get all packages with their menu items for import feature
        $packages = Package::with('category')->get();
        
        return view('food-menu.index', [
            'bookings' => $bookings,
            'selectedBooking' => $selectedBooking,
            'menu' => $menu,
            'date' => $date,
            'packages' => $packages
        ]);
    }

    /**
     * Save or update a food menu for a booking on a specific date
     */
    public function saveMenu(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer',
                'date' => 'required|date',
                'bed_tea' => 'nullable|string',
                'bed_tea_time' => 'nullable|string',
                'breakfast' => 'nullable|string',
                'breakfast_time' => 'nullable|string',
                'morning_snack' => 'nullable|string',
                'morning_snack_time' => 'nullable|string',
                'lunch' => 'nullable|string',
                'lunch_time' => 'nullable|string',
                'evening_snack' => 'nullable|string',
                'evening_snack_time' => 'nullable|string',
                'dinner' => 'nullable|string',
                'dinner_time' => 'nullable|string',
                'bites' => 'nullable|string',
            'bites_time' => 'nullable|string'
            ]);
            
            // Format times if provided
            $times = ['bed_tea_time', 'breakfast_time', 'morning_snack_time', 'lunch_time', 'evening_snack_time', 'dinner_time',  'bites_time'];
            foreach ($times as $timeField) {
                if (!empty($validated[$timeField])) {
                    // Combine the date with the time
                    $validated[$timeField] = Carbon::parse($validated['date'] . ' ' . $validated[$timeField]);
                } else {
                    $validated[$timeField] = null;
                }
            }
            
            // Find existing menu or create new one
            $menu = FoodMenu::updateOrCreate(
                [
                    'booking_id' => $validated['booking_id'],
                    'date' => $validated['date']
                ],
                [
                    'bed_tea' => $validated['bed_tea'] ?? null,
                    'bed_tea_time' => $validated['bed_tea_time'],
                    'breakfast' => $validated['breakfast'] ?? null,
                    'breakfast_time' => $validated['breakfast_time'],
                    'morning_snack' => $validated['morning_snack'] ?? null,
                    'morning_snack_time' => $validated['morning_snack_time'],
                    'lunch' => $validated['lunch'] ?? null,
                    'lunch_time' => $validated['lunch_time'],
                    'evening_snack' => $validated['evening_snack'] ?? null,
                    'evening_snack_time' => $validated['evening_snack_time'],
                    'dinner' => $validated['dinner'] ?? null,
                    'dinner_time' => $validated['dinner_time'],
                    'bites' => $validated['bites'] ?? null,
                'bites_time' => $validated['bites_time'],
                    'created_by' => auth()->id()
                ]
            );
            
            // Redirect back with success message
            return redirect()->route('food-menu.index', [
                'date' => $validated['date'],
                'booking_id' => $validated['booking_id']
            ])->with('success', 'Food menu saved successfully');
            
        } catch (\Exception $e) {
            Log::error('Error saving food menu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to save food menu: ' . $e->getMessage());
        }
    }

    /**
     * Print a food menu for a specific booking on a specific date
     */
    public function printMenu($bookingId, $date)
    {
        try {
            $booking = Booking::findOrFail($bookingId);
            $menu = FoodMenu::where('booking_id', $bookingId)
                          ->where('date', $date)
                          ->first();
            
            if (!$menu) {
                $menu = new FoodMenu([
                    'booking_id' => $bookingId,
                    'date' => $date
                ]);
            }
            
            return view('food-menu.print', [
                'booking' => $booking,
                'menu' => $menu,
                'date' => Carbon::parse($date)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error printing food menu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to print food menu: ' . $e->getMessage());
        }
    }

    /**
     * Print all function menus for a specific date
     */
    public function printDailyMenus(Request $request)
    {
        try {
            $date = $request->input('date', date('Y-m-d'));
            $day = Carbon::parse($date);
            
            // Find all bookings active on this date
            $bookings = Booking::where(function($query) use ($day) {
                $query->whereDate('start', '<=', $day->format('Y-m-d'))
                      ->whereDate('end', '>=', $day->format('Y-m-d'))
                      ->whereNotNull('end');
                      
                $query->orWhere(function($q) use ($day) {
                    $q->whereDate('start', $day->format('Y-m-d'))
                      ->where(function($sq) {
                          $sq->whereNull('end')
                             ->orWhere('end', 'N/A')
                             ->orWhere('end', '');
                      });
                });
            })->get();
            
            // Get menus for each booking - using a Laravel Collection
            $bookingsWithMenus = collect();
            
            foreach ($bookings as $booking) {
                $menu = FoodMenu::where('booking_id', $booking->id)
                              ->where('date', $date)
                              ->first();
                
                if (!$menu) {
                    $menu = new FoodMenu([
                        'booking_id' => $booking->id,
                        'date' => $date
                    ]);
                }
                
                $bookingsWithMenus->push([
                    'booking' => $booking,
                    'menu' => $menu
                ]);
            }
            
            Log::info('Printing daily menus for ' . $date, [
                'bookings_count' => $bookingsWithMenus->count()
            ]);
            
            return view('food-menu.print-daily', [
                'bookingsWithMenus' => $bookingsWithMenus,
                'date' => $day
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error printing daily menus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to print daily menus: ' . $e->getMessage());
        }
    }
}