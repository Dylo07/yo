@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Food Menu Generator</h5>
                        <div>
                            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Date Selection -->
                    <div class="mb-4">
                        <h5>Select Date</h5>
                        
                        <form action="{{ route('food-menu.index') }}" method="GET" class="mb-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" 
                                        value="{{ request('date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Load Bookings</button>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <a href="{{ route('food-menu.print-daily', ['date' => request('date', date('Y-m-d'))]) }}" 
                                       target="_blank" class="btn btn-info">
                                        <i class="fas fa-print me-1"></i> Print All Menus for This Date
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Bookings for Selected Date -->
                    @if(isset($bookings) && $bookings->count() > 0)
                        <h5>Bookings for {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('F j, Y') }}</h5>
                        
                        <div class="row g-3 mb-4">
                            @foreach($bookings as $booking)
                                <div class="col-md-4">
                                    <div class="card {{ isset($selectedBooking) && $selectedBooking->id == $booking->id ? 'border-primary' : '' }}" 
                                         style="cursor: pointer;"
                                         onclick="window.location.href='{{ route('food-menu.index', ['date' => request('date', date('Y-m-d')), 'booking_id' => $booking->id]) }}'">
                                        <div class="card-header">
                                            <strong>{{ $booking->function_type }}</strong>
                                            <span class="badge {{ $booking->food_menu_exists ? 'bg-success' : 'bg-warning' }} float-end">
                                                {{ $booking->food_menu_exists ? 'Menu Created' : 'No Menu' }}
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div><strong>ID:</strong> {{ $booking->id }}</div>
                                            <div><strong>Guest Count:</strong> {{ $booking->guest_count }}</div>
                                            <div><strong>Rooms:</strong> {{ is_array($booking->room_numbers) ? implode(', ', $booking->room_numbers) : $booking->room_numbers }}</div>
                                            <div><strong>Time:</strong> {{ $booking->start->format('g:i A') }} - {{ $booking->end ? $booking->end->format('g:i A') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(isset($bookings))
                        <div class="alert alert-info">
                            No bookings found for {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('F j, Y') }}.
                        </div>
                    @endif
                    
                    <!-- Selected Booking Details -->
                    @if(isset($selectedBooking))
                        <div class="alert alert-info">
                            <h6>Booking Details:</h6>
                            <p><strong>ID:</strong> {{ $selectedBooking->id }}</p>
                            <p><strong>Function Type:</strong> {{ $selectedBooking->function_type }}</p>
                            <p><strong>Guest Count:</strong> {{ $selectedBooking->guest_count }}</p>
                            <p><strong>Rooms:</strong> {{ is_array($selectedBooking->room_numbers) ? implode(', ', $selectedBooking->room_numbers) : $selectedBooking->room_numbers }}</p>
                            <p><strong>Start:</strong> {{ $selectedBooking->start->format('Y-m-d g:i A') }}</p>
                            <p><strong>End:</strong> {{ $selectedBooking->end ? $selectedBooking->end->format('Y-m-d g:i A') : 'N/A' }}</p>
                        </div>
                        
                        <!-- Menu Form -->
                        <div class="mt-4">
                            <h5>Food Menu</h5>
                            <form action="{{ route('food-menu.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                
                                <!-- Bed Tea (New) -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="bed_tea" class="form-label">Bed Tea Menu</label>
                                            <textarea name="bed_tea" id="bed_tea" class="form-control" rows="3">{{ $menu->bed_tea ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="bed_tea_time" class="form-label">Bed Tea Time</label>
                                            <input type="time" name="bed_tea_time" id="bed_tea_time" class="form-control" 
                                                value="{{ $menu && $menu->bed_tea_time ? $menu->bed_tea_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Breakfast -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="breakfast" class="form-label">Breakfast Menu</label>
                                            <textarea name="breakfast" id="breakfast" class="form-control" rows="3">{{ $menu->breakfast ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="breakfast_time" class="form-label">Breakfast Time</label>
                                            <input type="time" name="breakfast_time" id="breakfast_time" class="form-control" 
                                                value="{{ $menu && $menu->breakfast_time ? $menu->breakfast_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Morning Snack (New) -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="morning_snack" class="form-label">Morning Snack Menu</label>
                                            <textarea name="morning_snack" id="morning_snack" class="form-control" rows="3">{{ $menu->morning_snack ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="morning_snack_time" class="form-label">Morning Snack Time</label>
                                            <input type="time" name="morning_snack_time" id="morning_snack_time" class="form-control" 
                                                value="{{ $menu && $menu->morning_snack_time ? $menu->morning_snack_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lunch -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="lunch" class="form-label">Lunch Menu</label>
                                            <textarea name="lunch" id="lunch" class="form-control" rows="3">{{ $menu->lunch ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="lunch_time" class="form-label">Lunch Time</label>
                                            <input type="time" name="lunch_time" id="lunch_time" class="form-control" 
                                                value="{{ $menu && $menu->lunch_time ? $menu->lunch_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Evening Snack -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="evening_snack" class="form-label">Evening Snack Menu</label>
                                            <textarea name="evening_snack" id="evening_snack" class="form-control" rows="3">{{ $menu->evening_snack ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="evening_snack_time" class="form-label">Evening Snack Time</label>
                                            <input type="time" name="evening_snack_time" id="evening_snack_time" class="form-control" 
                                                value="{{ $menu && $menu->evening_snack_time ? $menu->evening_snack_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dinner -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="dinner" class="form-label">Dinner Menu</label>
                                            <textarea name="dinner" id="dinner" class="form-control" rows="3">{{ $menu->dinner ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dinner_time" class="form-label">Dinner Time</label>
                                            <input type="time" name="dinner_time" id="dinner_time" class="form-control" 
                                                value="{{ $menu && $menu->dinner_time ? $menu->dinner_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-success">Save Menu</button>
                                    <a href="{{ route('food-menu.print', ['booking' => $selectedBooking->id, 'date' => $date]) }}" 
                                       target="_blank" class="btn btn-info ms-2">Print Menu</a>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection