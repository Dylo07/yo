<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use App\Models\RoomLog;  // Add this at the top of your controller

class RoomAvailabilityController extends Controller
{
    public function index()
    {
        $rooms = Room::with('checklistItems')->get();
        $roomLogs = RoomLog::with(['room', 'user'])
            ->latest()
            ->paginate(10);
        return view('rooms.availability', compact('rooms', 'roomLogs'));
    }
    

    public function storeRoom(Request $request)
    {
        $room = Room::create($request->validate([
            'name' => 'required|string'
        ]) + ['daily_checked' => false]);
        
        // Attach all existing checklist items to the new room
        $checklistItems = ChecklistItem::all();
        foreach ($checklistItems as $item) {
            $room->checklistItems()->attach($item->id, [
                'is_checked' => true  // Set default checked
            ]);
        }
        
        return redirect()->route('rooms.availability')
            ->with('success', 'Room added successfully');
    }

    public function storeChecklistItem(Request $request)
    {
        $item = ChecklistItem::create($request->validate([
            'name' => 'required|string'
        ]));
        
        // Attach the new checklist item to all rooms
        $rooms = Room::all();
        foreach ($rooms as $room) {
            $room->checklistItems()->attach($item->id, [
                'is_checked' => true  // Set default checked
            ]);
        }
        
        return redirect()->route('rooms.availability')
            ->with('success', 'Checklist item added successfully');
    }

    public function dailyCheck($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        // Prevent updates if room is booked
        if ($room->is_booked) {
            return redirect()->route('rooms.availability')
                ->with('error', 'Cannot modify daily check while room is booked');
        }

        $room->daily_checked = !$room->daily_checked;
        $room->save();

        return redirect()->route('rooms.availability')
            ->with('success', $room->daily_checked ? 'Room checked successfully' : 'Room check removed');
    }

public function toggleBooking($roomId)
{
    $room = Room::findOrFail($roomId);
    $room->is_booked = !$room->is_booked;
    
    if ($room->is_booked) {
        // Reset all checklist items
        foreach ($room->checklistItems as $item) {
            $room->checklistItems()->updateExistingPivot($item->id, [
                'is_checked' => false
            ]);
        }
        
        // Reset daily check
        $room->daily_checked = false;
    }
    
    $room->save();

    return redirect()->route('rooms.availability')
        ->with('success', $room->is_booked ? 'Room booked and checklist reset' : 'Room booking cancelled');
}
public function updateChecklist($roomId, Request $request)
{
    $room = Room::findOrFail($roomId);
    $checkedItems = $request->input('checklist', []);
    
    foreach ($room->checklistItems as $item) {
        $isChecked = in_array($item->id, $checkedItems);
        $room->checklistItems()->updateExistingPivot(
            $item->id,
            ['is_checked' => $isChecked]
        );
    }
    
    $this->logRoomAction($room, 'checklist_updated', [
        'checked_items' => $checkedItems
    ]);
    
    return redirect()->route('rooms.availability')
        ->with('success', 'Checklist updated successfully');
}



    public function guestIn($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        // Check if room is available
        $isAvailable = $room->checklistItems->every(function($item) {
            return $item->pivot->is_checked;
        }) && $room->daily_checked;

        if (!$isAvailable) {
            return redirect()->route('rooms.availability')
                ->with('error', 'Room must be available before checking in a guest');
        }

        $room->is_booked = true;
        $room->save();

        return redirect()->route('rooms.availability')
            ->with('success', 'Guest checked in successfully');
    }

    public function guestOut($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        if (!$room->is_booked) {
            return redirect()->route('rooms.availability')
                ->with('error', 'Room is not currently occupied');
        }

        // Reset all checklist items
        foreach ($room->checklistItems as $item) {
            $room->checklistItems()->updateExistingPivot($item->id, [
                'is_checked' => false
            ]);
        }

        // Reset daily check and booking status
        $room->is_booked = false;
        $room->daily_checked = false;
        $room->save();

        return redirect()->route('rooms.availability')
            ->with('success', 'Guest checked out and room reset successfully');
    }

    private function logRoomAction($room, $action, $details = null)
{
    RoomLog::create([
        'room_id' => $room->id,
        'user_id' => auth()->id(),
        'action' => $action,
        'details' => $details
    ]);
}

}