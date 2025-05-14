<!-- Tomorrow's Events Tab Content with Date and Time -->
<h2 class="text-2xl font-bold mb-4">Tomorrow's Functions & Menus</h2>

@if(count($tomorrowBookings) > 0)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="tomorrow-events-container">
    @foreach($tomorrowBookings as $booking)
    <div class="border rounded-lg shadow-md p-4 bg-white">
        <div class="flex justify-between items-center mb-2">
            <div class="text-xl font-bold">{{ $booking->function_type }}</div>
           <div class="text-lg flex flex-wrap items-center bg-gray-100 px-3 py-1 rounded-lg">
    <div class="flex items-center mr-2">
        <i class="fas fa-sign-in-alt text-blue-600 mr-1"></i>
        <span class="font-medium">Check In:</span>
        <span class="ml-1">{{ \Carbon\Carbon::parse($booking->start)->format('d M, g:i A') }}</span>
    </div>
    <div class="mx-1 text-gray-400">|</div>
    <div class="flex items-center">
        <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>
        <span class="font-medium">Check Out:</span>
        <span class="ml-1">{{ $booking->end ? \Carbon\Carbon::parse($booking->end)->format('d M, g:i A') : 'N/A' }}</span>
    </div>
</div>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-3">
            <div class="flex items-center text-gray-600">
                <i class="fas fa-users mr-2"></i>
                <span>{{ $booking->guest_count ?? '0' }} guests</span>
            </div>
            <div class="flex items-center text-gray-600">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>{{ $booking->location ?? 'Main Venue' }}</span>
            </div>
            <div class="flex items-center text-gray-600">
                <i class="fas fa-bed mr-2"></i>
                <span>
                    @if(is_array($booking->room_numbers))
                        {{ implode(', ', $booking->room_numbers) }}
                    @elseif(is_string($booking->room_numbers))
                        {{ $booking->room_numbers }}
                    @else
                        Room(s) not assigned
                    @endif
                </span>
            </div>
            <div class="flex items-center text-gray-600">
                <i class="fas fa-utensils mr-2"></i>
                <span class="capitalize {{ $booking->menu ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $booking->menu ? 'Menu Created' : 'No Menu' }}
                </span>
            </div>
        </div>
        
        @if($booking->menu)
        <div class="mt-3 pt-3 border-t">
            <div class="food-menu-summary">
                @if($booking->menu->bed_tea)
                <div class="mb-2">
                    <div class="font-semibold text-emerald-700">
                        <i class="fas fa-mug-hot mr-1"></i> Bed Tea
                        @if($booking->menu->bed_tea_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->bed_tea_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->bed_tea, 100) }}</div>
                </div>
                @endif
                
                @if($booking->menu->breakfast)
                <div class="mb-2">
                    <div class="font-semibold text-blue-700">
                        <i class="fas fa-coffee mr-1"></i> Breakfast
                        @if($booking->menu->breakfast_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->breakfast_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->breakfast, 100) }}</div>
                </div>
                @endif
                
                @if($booking->menu->morning_snack)
                <div class="mb-2">
                    <div class="font-semibold text-amber-700">
                        <i class="fas fa-bread-slice mr-1"></i> Morning Snack
                        @if($booking->menu->morning_snack_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->morning_snack_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->morning_snack, 100) }}</div>
                </div>
                @endif
                
                @if($booking->menu->lunch)
                <div class="mb-2">
                    <div class="font-semibold text-green-700">
                        <i class="fas fa-hamburger mr-1"></i> Lunch
                        @if($booking->menu->lunch_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->lunch_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->lunch, 100) }}</div>
                </div>
                @endif
                
                @if($booking->menu->evening_snack)
                <div class="mb-2">
                    <div class="font-semibold text-orange-700">
                        <i class="fas fa-cookie mr-1"></i> Evening Snack
                        @if($booking->menu->evening_snack_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->evening_snack_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->evening_snack, 100) }}</div>
                </div>
                @endif
                
                @if($booking->menu->dinner)
                <div class="mb-2">
                    <div class="font-semibold text-purple-700">
                        <i class="fas fa-utensils mr-1"></i> Dinner
                        @if($booking->menu->dinner_time)
                            <span class="text-sm text-gray-600 ml-2">
                                {{ \Carbon\Carbon::parse($booking->menu->dinner_time)->format('g:i A') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 ml-6">{{ Str::limit($booking->menu->dinner, 100) }}</div>
                </div>
                @endif
            </div>
        </div>
        @elseif(!empty($booking->special_requests))
        <div class="special-request">
            <div class="flex">
                <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                <span class="text-sm">{{ $booking->special_requests }}</span>
            </div>
        </div>
        @endif
        
        <div class="flex justify-end gap-2 mt-3">
            <a href="{{ route('food-menu.index', ['date' => now()->addDay()->format('Y-m-d'), 'booking_id' => $booking->id]) }}" 
               class="bg-blue-600 text-white px-3 py-1 rounded text-sm action-button">
                <i class="fas fa-edit mr-1"></i> Manage Menu
            </a>
            @if($booking->menu)
            <a href="{{ route('food-menu.print', ['booking' => $booking->id, 'date' => now()->addDay()->format('Y-m-d')]) }}" 
               target="_blank" class="bg-green-500 text-white px-3 py-1 rounded text-sm action-button">
                <i class="fas fa-print mr-1"></i> Print Menu
            </a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-lg shadow-md p-8 text-center">
    <i class="fas fa-calendar-week text-gray-400 text-5xl mb-4"></i>
    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Events Tomorrow</h3>
    <p class="text-gray-500">There are no scheduled events or functions for tomorrow.</p>
</div>
@endif

<!-- Link to food menu generator -->
<div class="mt-6 text-center">
    <a href="{{ route('food-menu.index', ['date' => now()->addDay()->format('Y-m-d')]) }}" class="bg-blue-600 text-white px-4 py-2 rounded shadow-md hover:bg-blue-700 transition">
        <i class="fas fa-edit mr-2"></i> Go to Food Menu Generator
    </a>
</div>