@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Room Availability Management</h2>
        </div>
        
        <div class="card-body">
            <!-- Add Room Form -->
            <form action="{{ route('rooms.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Room Name" required>
                    <button type="submit" class="btn btn-primary">Add Room</button>
                </div>
            </form>

            <!-- Add Checklist Item Form -->
            <form action="{{ route('rooms.checklist.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Checklist Item" required>
                    <button type="submit" class="btn btn-primary">Add Checklist Item</button>
                </div>
            </form>

            <!-- Room Status Table -->
            <table class="table table-bordered">
    <thead class="bg-dark text-white">
    <tr>
                        <th width="15%">Room Name</th>
                        <th width="30%">Check List</th>
                        <th width="15%">Daily Check</th>
                        <th width="20%" class="bg-primary text-white text-center">
                            <span class="fs-5">Room Availability</span>
                        </th>
                        <th width="20%">Room Booking</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                    <tr>
                        <!-- Previous columns remain the same -->
                        <td class="align-middle">{{ $room->name }}</td>
                        <td>
                        <form action="{{ route('rooms.update-checklist', $room->id) }}" method="POST">
        @csrf
        @foreach($room->checklistItems as $item)
        <div class="form-check mb-2">
            <input type="checkbox" 
                   name="checklist[]" 
                   value="{{ $item->id }}"
                   class="form-check-input"
                   {{ $item->pivot->is_checked ? 'checked disabled' : '' }}
                   {{ $room->is_booked ? 'disabled' : '' }}>
            <label class="form-check-label {{ $room->is_booked || $item->pivot->is_checked ? 'text-muted' : '' }}">
                {{ $item->name }}
            </label>
        </div>
        @endforeach
        <button type="submit" 
                class="btn btn-primary btn-sm mt-2"
                {{ $room->is_booked || $room->checklistItems->every(fn($item) => $item->pivot->is_checked) ? 'disabled' : '' }}>
            Save Checklist
        </button>
    </form>
</td>
                        <td class="align-middle text-center">
                            <form action="{{ route('rooms.daily-check', $room->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn {{ $room->daily_checked ? 'btn-success' : 'btn-warning' }}"
                                        {{ $room->is_booked ? 'disabled' : '' }}>
                                    {{ $room->daily_checked ? 'Checked' : 'Need to Check Again' }}
                                </button>
                            </form>
                        </td>
                        <!-- Enhanced Availability Column -->
                        <td class="align-middle text-center availability-column">
                            @php
                                $isAvailable = $room->checklistItems->every(function($item) {
                                    return $item->pivot->is_checked;
                                }) && $room->daily_checked;
                            @endphp
                            <div class="status-badge {{ $isAvailable && !$room->is_booked ? 'status-available' : ($room->is_booked ? 'status-unavailable' : 'status-need-clean') }}">
    {{ $room->is_booked ? 'BOOKED' : ($isAvailable ? 'AVAILABLE' : 'NEED TO CLEAN') }}
</div>
                        </td>
                        <td class="align-middle text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <form action="{{ route('rooms.guest-in', $room->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-success"
                                            {{ (!$isAvailable || $room->is_booked) ? 'disabled' : '' }}>
                                        Guest In
                                    </button>
                                </form>
                                <form action="{{ route('rooms.guest-out', $room->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-danger"
                                            {{ !$room->is_booked ? 'disabled' : '' }}>
                                        Guest Out
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.form-check {
    margin-bottom: 0.5rem;
    padding-left: 2rem;
}
.table > :not(caption) > * > * {
    padding: 1rem;
}
.gap-2 {
    gap: 0.5rem;
}
.text-muted {
    opacity: 0.6;
}

/* Enhanced Availability Styling */
.availability-column {
    background-color: #f8f9fa;
    border-left: 2px solid #dee2e6;
    border-right: 2px solid #dee2e6;
}

.status-badge {
    padding: 15px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 1.1rem;
    letter-spacing: 1px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 100%;
}

.status-available {
    background-color: #28a745;
    color: white;
    animation: pulse 2s infinite;
}
/* Add this custom style for NEED TO CLEAN */
.status-badge:contains('NEED TO CLEAN') {
    background-color: #6f42c1;  /* Purple color */
    color: white;
}
.status-need-clean {
    background-color: #6f42c1;  /* Purple color */
    color: white;
}

.status-unavailable {
    background-color: #dc3545;
    color: white;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    50% {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
}

/* Make header more prominent */
.table thead th {
    vertical-align: middle;
    font-weight: 600;
}

.table thead th.bg-primary {
    position: relative;
    overflow: hidden;
}

.table thead th.bg-primary::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255,255,255,0.5);
}
</style>
@endsection