<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\FoodMenu;
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
        
        return view('food-menu.index', [
            'bookings' => $bookings,
            'selectedBooking' => $selectedBooking,
            'menu' => $menu,
            'date' => $date
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
                'breakfast' => 'nullable|string',
                'lunch' => 'nullable|string',
                'evening_snack' => 'nullable|string',
                'dinner' => 'nullable|string'
            ]);
            
            // Find existing menu or create new one
            $menu = FoodMenu::updateOrCreate(
                [
                    'booking_id' => $validated['booking_id'],
                    'date' => $validated['date']
                ],
                [
                    'breakfast' => $validated['breakfast'] ?? null,
                    'lunch' => $validated['lunch'] ?? null,
                    'evening_snack' => $validated['evening_snack'] ?? null,
                    'dinner' => $validated['dinner'] ?? null,
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