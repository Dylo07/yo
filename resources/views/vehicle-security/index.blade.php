@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
   <div class="card shadow-sm">
       <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3">
           <h3 class="mb-0 font-weight-bold">Vehicle Security Management</h3>
           <input type="date" class="form-control bg-light w-auto" 
                  value="{{ $selectedDate ?? date('Y-m-d') }}" 
                  onchange="window.location.href='{{ route('vehicle-security.by-date', '') }}/' + this.value">
       </div>

       <div class="card-body">
           @if(session('success'))
               <div class="alert alert-success alert-dismissible fade show">
                   {{ session('success') }}
                   <button type="button" class="close" data-dismiss="alert">&times;</button>
               </div>
           @endif

           <!-- Dashboard Summary Section -->
<div class="dashboard-summary mb-4">
    <div class="row">
        <!-- Vehicles Card -->
        <div class="col-md-4">
            <div class="card dashboard-card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-car-side mr-2"></i> Vehicles Today</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-center px-2">
                            <div class="stat-circle bg-primary mb-2">{{ $stats['checkedIn'] }}</div>
                            <div class="stat-label">On Property</div>
                        </div>
                        <div class="text-center px-2">
                            <div class="stat-circle bg-info mb-2">{{ $stats['tempOut'] }}</div>
                            <div class="stat-label">Temp Out</div>
                        </div>
                        <div class="text-center px-2">
                            <div class="stat-circle bg-success mb-2">{{ $stats['checkedOut'] }}</div>
                            <div class="stat-label">Checked Out</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status Card -->
        <div class="col-md-4">
            <div class="card dashboard-card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-bed mr-2"></i> Room Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-around">
                        <div class="text-center px-2">
                            <div class="stat-circle bg-danger mb-2">{{ $stats['occupiedRooms'] }}</div>
                            <div class="stat-label">Occupied</div>
                        </div>
                        <div class="text-center px-2">
                            <div class="stat-circle bg-success mb-2">{{ $stats['availableRooms'] }}</div>
                            <div class="stat-label">Available</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pool Usage Card -->
        <div class="col-md-4">
            <div class="card dashboard-card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-swimming-pool mr-2"></i> Pool Usage</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-center px-2">
                            <div class="stat-circle bg-primary mb-2">{{ $stats['poolUsage']['adults'] }}</div>
                            <div class="stat-label">Adults</div>
                        </div>
                        <div class="text-center px-2">
                            <div class="stat-circle bg-warning mb-2">{{ $stats['poolUsage']['kids'] }}</div>
                            <div class="stat-label">Kids</div>
                        </div>
                        <div class="text-center px-2">
                            <div class="stat-circle bg-info mb-2">{{ $stats['poolUsage']['adults'] + $stats['poolUsage']['kids'] }}</div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                   
                   
           <!-- Quick Filters Section -->
           <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
               <h4 class="font-weight-bold mb-3">
                   <i class="fas fa-filter mr-2"></i>Quick Filters
               </h4>
               
               <div class="d-flex flex-wrap gap-2 mb-3">
                   <a href="{{ route('vehicle-security.index', ['filter' => 'all']) }}" 
                      class="btn {{ $selectedFilter == 'all' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-car mr-1"></i> All Vehicles
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'in']) }}" 
                      class="btn {{ $selectedFilter == 'in' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-parking mr-1"></i> On Property
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'out']) }}" 
                      class="btn {{ $selectedFilter == 'out' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-sign-out-alt mr-1"></i> Checked Out
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'temp']) }}" 
                      class="btn {{ $selectedFilter == 'temp' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-clock mr-1"></i> Temp Out
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'today']) }}" 
                      class="btn {{ $selectedFilter == 'today' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-calendar-day mr-1"></i> Today Only
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'room']) }}" 
                      class="btn {{ $selectedFilter == 'room' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-bed mr-1"></i> With Room
                   </a>
                   <a href="{{ route('vehicle-security.index', ['filter' => 'pool']) }}" 
                      class="btn {{ $selectedFilter == 'pool' ? 'btn-primary' : 'btn-light' }} mr-2 mb-2">
                       <i class="fas fa-swimming-pool mr-1"></i> Pool Access
                   </a>
               </div>
               
               <!-- Quick Search for Vehicle Number -->
               <div class="input-group">
                   <div class="input-group-prepend">
                       <span class="input-group-text bg-primary text-white">
                           <i class="fas fa-search"></i>
                       </span>
                   </div>
                   <input type="text" id="quickSearch" class="form-control form-control-lg" 
                          placeholder="Quick search by vehicle #" 
                          onkeyup="quickSearchVehicle(this.value)">
               </div>
           </div>

           <!-- Simplified Entry Form -->
           <div class="card mb-4 bg-light shadow-sm">
               <div class="card-header bg-white">
                   <h4 class="mb-0 font-weight-bold text-primary">
                       <i class="fas fa-car-side mr-2"></i>New Vehicle Entry
                   </h4>
               </div>
               <div class="card-body">
                   <form action="{{ route('vehicle-security.store') }}" method="POST" id="vehicleEntryForm" onsubmit="addVehicle(event)">
                       @csrf
                       
                       <!-- Basic Info Section - Always visible and simplified -->
                       <div class="row mb-4">
                           <!-- Vehicle Number with large touch-friendly input -->
                           <div class="col-md-4">
                               <label class="font-weight-bold text-gray-700 mb-2">Vehicle Number</label>
                               <input type="text" name="vehicle_number" class="form-control form-control-lg" 
                                      placeholder="Enter vehicle number" required>
                           </div>
                           
                           <!-- Matter with visual selection -->
                           <div class="col-md-4">
                               <label class="font-weight-bold text-gray-700 mb-2">Purpose</label>
                               <select name="matter" class="form-control form-control-lg" required>
                                   <option value="">Select purpose...</option>
                                   @foreach($matterOptions as $option)
                                       <option value="{{ $option }}">{{ $option }}</option>
                                   @endforeach
                               </select>
                           </div>
                           
                           <!-- Description - Optional but always visible -->
                           <div class="col-md-4">
                               <label class="font-weight-bold text-gray-700 mb-2">
                                   Description <span class="text-muted">(Optional)</span>
                               </label>
                               <textarea name="description" class="form-control" rows="1" 
                                         placeholder="Additional details..."></textarea>
                           </div>
                       </div>
                       
                       <!-- Option Cards - Larger, touch-friendly toggle buttons -->
                       <div class="row mb-4">
                           <div class="col-md-6">
                               <div class="card option-card" onclick="toggleOption('showRoom')">
                                   <div class="card-body d-flex align-items-center" id="roomOptionCard">
                                       <div class="custom-control custom-switch mr-3">
                                           <input type="checkbox" name="showRoom" id="showRoom" 
                                                  class="custom-control-input" onchange="toggleRoomInput(this)">
                                           <label class="custom-control-label" for="showRoom"></label>
                                       </div>
                                       <div>
                                           <i class="fas fa-bed fa-2x mr-3 text-primary"></i>
                                       </div>
                                       <div>
                                           <h5 class="m-0">Add Room</h5>
                                           <small class="text-muted">Assign room(s) to this vehicle</small>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="col-md-6">
                               <div class="card option-card" onclick="toggleOption('showPool')">
                                   <div class="card-body d-flex align-items-center" id="poolOptionCard">
                                       <div class="custom-control custom-switch mr-3">
                                           <input type="checkbox" name="showPool" id="showPool" 
                                                  class="custom-control-input" onchange="togglePoolInput(this)">
                                           <label class="custom-control-label" for="showPool"></label>
                                       </div>
                                       <div>
                                           <i class="fas fa-swimming-pool fa-2x mr-3 text-info"></i>
                                       </div>
                                       <div>
                                           <h5 class="m-0">Add Pool</h5>
                                           <small class="text-muted">Add pool access counts</small>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                       
                       <!-- Room Selection - Only visible when Add Room is checked -->
<div id="roomSection" class="card mb-4 shadow-sm" style="display:none;">
    <div class="card-body bg-light">
        <h5 class="text-primary mb-3">
            <i class="fas fa-door-open mr-2"></i>Select Rooms
        </h5>
        
        <div class="room-selection-grid">
            @foreach($roomOptions as $room)
                <label class="room-select-card {{ in_array($room, $availableRooms) ? '' : 'occupied' }}">
                    <input type="checkbox" name="room_numbers[]" value="{{ $room }}"
                           class="room-checkbox" {{ in_array($room, $availableRooms) ? '' : 'disabled' }}>
                    <div class="room-card-body">
                        <span class="room-number">{{ $room }}</span>
                        @if(!in_array($room, $availableRooms))
                            <span class="occupied-badge">
                                <i class="fas fa-user-lock"></i>
                            </span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</div>
                       
                       <!-- Improved Pool Count Section -->
<div id="poolSection" class="card mb-4 shadow-sm" style="display:none;">
    <div class="card-body bg-light">
        <h5 class="text-info mb-3">
            <i class="fas fa-swimming-pool mr-2"></i>Pool Count
        </h5>
        
        <div class="row">
            <div class="col-md-6">
                <label class="font-weight-bold text-dark">Adults</label>
                <div class="input-group count-control">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary" 
                                onclick="decrementCount('adult_pool_count')">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    <input type="number" name="adult_pool_count" id="adult_pool_count" 
                           class="form-control form-control-lg text-center" value="0" min="0">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" 
                                onclick="incrementCount('adult_pool_count')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="font-weight-bold text-dark">Children</label>
                <div class="input-group count-control">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary" 
                                onclick="decrementCount('kids_pool_count')">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    <input type="number" name="kids_pool_count" id="kids_pool_count" 
                           class="form-control form-control-lg text-center" value="0" min="0">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" 
                                onclick="incrementCount('kids_pool_count')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                       
                       
                       <!-- Submit Buttons - Large and Touch-friendly -->
                       <div class="d-flex flex-wrap gap-2">
                           <button type="submit" class="btn btn-lg btn-primary flex-grow-1 mr-2">
                               <i class="fas fa-save mr-2"></i> Save Entry
                           </button>
                           
                           <button type="submit" name="is_note" value="1" class="btn btn-lg btn-secondary flex-grow-1">
                               <i class="fas fa-sticky-note mr-2"></i> Save as Note
                           </button>
                       </div>
                   </form>
               </div>
           </div>

           <!-- Available Rooms Display -->
           <div class="card mb-4 bg-white border rounded">
                           <div class="card-body">
                               <div class="d-flex align-items-center mb-2">
                                   <i class="fas fa-door-open text-primary mr-2"></i>
                                   <h5 class="font-weight-bold text-primary m-0">
                                       Available Rooms: <span class="badge badge-primary">{{ count($availableRooms) }}</span>
                                   </h5>
                               </div>
                               
                               <div class="d-flex flex-wrap">
                                   @forelse($availableRooms as $room)
                                       <span class="room-badge m-1">{{ $room }}</span>
                                   @empty
                                       <span class="text-muted font-italic">No rooms available</span>
                                   @endforelse
                               </div>
                           </div>
                       </div>

           <!-- Vehicles Table with Enhanced UI -->
           <div class="table-responsive">
               <table class="table table-bordered table-hover shadow-sm">
                   <thead class="thead-dark">
                       <tr>
                           <th><i class="far fa-clock mr-1"></i> Check In</th>
                           <th><i class="fas fa-car mr-1"></i> Vehicle Number</th>
                           <th><i class="fas fa-tasks mr-1"></i> Matter</th>
                           <th><i class="fas fa-comment-alt mr-1"></i> Description</th>
                           <th><i class="fas fa-door-open mr-1"></i> Room</th>
                           <th><i class="fas fa-swimming-pool mr-1"></i> Pool Count (A/K)</th>
                           <th><i class="far fa-clock mr-1"></i> Check Out</th>
                           <th><i class="fas fa-users mr-1"></i> Team</th>
                           <th><i class="fas fa-cog mr-1"></i> Actions</th>
                       </tr>
                   </thead>
                   <tbody id="vehicleTableBody">
                       @foreach($vehicles as $vehicle)
                       <tr data-vehicle-id="{{ $vehicle->id }}" class="{{ $vehicle->team ? 'team-'.str_replace(' ', '', $vehicle->team) : '' }} 
                           {{ $vehicle->is_temp_out ? 'temp-out-row' : '' }} 
                           {{ $vehicle->checkout_time ? 'checked-out-row' : '' }}">
                           <td class="align-middle">
                               <span class="checkin-badge">
                                   {{ $vehicle->created_at->format('Y-m-d H:i') }}
                               </span>
                           </td>
                           <td class="align-middle vehicle-number">{{ $vehicle->vehicle_number }}</td>
                           <td class="align-middle matter">{{ $vehicle->matter }}</td>
                           <td class="align-middle description">{{ $vehicle->description }}</td>
                           <td class="align-middle room">
                               @if($vehicle->room_numbers)
                                   @foreach(json_decode($vehicle->room_numbers) as $room)
                                       <span class="room-badge">{{ $room }}</span>
                                   @endforeach
                               @endif
                           </td>
                           <td class="align-middle pool-cell">
                               @if($vehicle->adult_pool_count || $vehicle->kids_pool_count)
                                   <span class="pool-badge">
                                       {{ $vehicle->adult_pool_count }}/{{ $vehicle->kids_pool_count }}
                                   </span>
                               @endif
                           </td>
                           <td class="align-middle checkout-cell">
                               @if($vehicle->checkout_time)
                                   <span class="checkout-badge">
                                       {{ $vehicle->checkout_time->format('Y-m-d h:i A') }}<br>
                                       <span class="duration-badge">
                                           Duration: {{ 
                                               number_format(
                                                   $vehicle->created_at->diffInMinutes($vehicle->checkout_time) / 60, 
                                                   1
                                               ) 
                                           }} hours
                                       </span>
                                   </span>
                               @elseif($vehicle->temp_checkout_time)
                                   <span class="temp-badge">
                                       Temp Out: {{ $vehicle->temp_checkout_time->format('Y-m-d h:i A') }}<br>
                                       @if($vehicle->temp_checkin_time)
                                           <small class="temp-in-time">
                                               Temp In: {{ $vehicle->temp_checkin_time->format('Y-m-d h:i A') }}
                                           </small>
                                       @endif
                                   </span>
                               @else
                                   <span class="text-warning">-</span>
                               @endif
                           </td>
                           <td class="align-middle team-cell">
                               @if(!$vehicle->checkout_time)
                                   <select class="form-control form-control-sm" onchange="updateTeam({{ $vehicle->id }}, this.value)">
                                       <option value="">Select Team</option>
                                       @for($i = 1; $i <= 10; $i++)
                                           <option value="Team {{ $i }}" {{ $vehicle->team == "Team $i" ? 'selected' : '' }}>
                                               Team {{ $i }}
                                           </option>
                                       @endfor
                                   </select>
                               @else
                                   <span class="team-badge team-badge-{{ str_replace(' ', '', $vehicle->team) }}">
                                       {{ $vehicle->team }}
                                   </span>
                               @endif
                           </td>
                           <td class="align-middle actions-cell">
                               @if(!$vehicle->is_note)
                                   @if(!$vehicle->checkout_time)
                                       <div class="btn-group">
                                           <button type="button" class="btn btn-sm btn-primary" onclick="editVehicle({{ $vehicle->id }})">
                                               <i class="fas fa-edit"></i> Edit
                                           </button>
                                           
                                           <form action="{{ route('vehicle-security.checkout', $vehicle->id) }}" 
                                                 method="POST" style="display:inline;" 
                                                 onsubmit="checkoutVehicle(event, {{ $vehicle->id }})">
                                               @csrf
                                               <button type="submit" class="btn btn-lg main-checkout-btn mx-2">
                                                   <i class="fas fa-sign-out-alt"></i> CHECK OUT
                                               </button>
                                           </form>

                                           @if(!$vehicle->is_temp_out)
                                               <button type="button" class="btn btn-sm btn-info" 
                                                       onclick="tempCheckout({{ $vehicle->id }})">
                                                   <i class="fas fa-clock"></i> Temp Out
                                               </button>
                                           @else
                                               <button type="button" class="btn btn-sm btn-success" 
                                                       onclick="tempCheckin({{ $vehicle->id }})">
                                                   <i class="fas fa-undo"></i> Temp In
                                               </button>
                                           @endif
                                       </div>
                                   @else
                                       <span class="checkout-status-badge">
                                           <i class="fas fa-check-circle"></i> Checked Out
                                       </span>
                                   @endif
                               @endif
                           </td>
                       </tr>
                       @endforeach
                   </tbody>
               </table>
           </div>
       </div>
   </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <div class="modal-header bg-primary text-white">
               <h5 class="modal-title">
                   <i class="fas fa-edit mr-1"></i> Edit Vehicle Entry
               </h5>
               <button type="button" class="close text-white" data-dismiss="modal">
                   <span>&times;</span>
               </button>
           </div>
           <div class="modal-body">
               <form id="editForm" method="POST" onsubmit="updateVehicle(event)">
                   @csrf
                   @method('PUT')
                   <div class="row">
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Vehicle Number</label>
                               <input type="text" name="vehicle_number" class="form-control" required>
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Matter</label>
                               <select name="matter" class="form-control" required>
                                   @foreach($matterOptions as $option)
                                       <option value="{{ $option }}">{{ $option }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       <div class="col-md-12">
                           <div class="form-group">
                               <label>Description</label>
                               <textarea name="description" class="form-control" rows="2"></textarea>
                           </div>
                       </div>
                       <div class="col-md-12">
                           <div class="form-group">
                               <label>Room Numbers</label>
                               <div class="room-selection-grid">
                                   @foreach($roomOptions as $room)
                                       <label class="room-select-card">
                                           <input type="checkbox" id="edit_room_{{ $room }}" 
                                                  name="room_numbers[]" value="{{ $room }}">
                                           <div class="room-card-body">
                                               <span class="room-number">{{ $room }}</span>
                                           </div>
                                       </label>
                                   @endforeach
                               </div>
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Adult Pool Count</label>
                               <div class="input-group count-control">
                                   <div class="input-group-prepend">
                                       <button type="button" class="btn btn-outline-secondary" 
                                               onclick="decrementCount('edit_adult_pool_count')">
                                           <i class="fas fa-minus"></i>
                                       </button>
                                   </div>
                                   <input type="number" name="adult_pool_count" id="edit_adult_pool_count" 
                                          class="form-control text-center" value="0" min="0">
                                   <div class="input-group-append">
                                       <button type="button" class="btn btn-outline-secondary" 
                                               onclick="incrementCount('edit_adult_pool_count')">
                                           <i class="fas fa-plus"></i>
                                       </button>
                                   </div>
                               </div>
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="form-group">
                               <label>Kids Pool Count</label>
                               <div class="input-group count-control">
                                   <div class="input-group-prepend">
                                       <button type="button" class="btn btn-outline-secondary" 
                                               onclick="decrementCount('edit_kids_pool_count')">
                                           <i class="fas fa-minus"></i>
                                       </button>
                                   </div>
                                   <input type="number" name="kids_pool_count" id="edit_kids_pool_count" 
                                          class="form-control text-center" value="0" min="0">
                                   <div class="input-group-append">
                                       <button type="button" class="btn btn-outline-secondary" 
                                               onclick="incrementCount('edit_kids_pool_count')">
                                           <i class="fas fa-plus"></i>
                                       </button>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">
                   <i class="fas fa-times mr-1"></i> Close
               </button>
               <button type="submit" form="editForm" class="btn btn-primary">
                   <i class="fas fa-save mr-1"></i> Save changes
               </button>
           </div>
       </div>
   </div>
</div>

<style>
/* Enhanced Styles for the Improved UI */
.card-header {
    background: linear-gradient(45deg, #1a237e, #283593);
}

/* Dashboard cards */
.card {
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.border-left-primary {
    border-left: 4px solid #4e73df;
}

.border-left-success {
    border-left: 4px solid #1cc88a;
}

.border-left-info {
    border-left: 4px solid #36b9cc;
}

.border-left-warning {
    border-left: 4px solid #f6c23e;
}

/* Enhanced Badge Styles */
.checkin-badge, .checkout-badge, .pool-badge, .room-badge, 
.temp-badge, .checkout-status-badge, .team-badge {
    color: #4a484d;
    border-radius: 15px;
    font-size: 0.9rem;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 6px 12px;
}

.checkin-badge {
    background: linear-gradient(45deg, #f5f5f5, #894aff);
}

.checkout-badge {
    background: linear-gradient(45deg, #20c997, #28a745);
    min-width: 200px;
    padding: 8px 12px;
}

.pool-badge {
    background: linear-gradient(45deg, #0dcaf0, #0d6efd);
}

.room-badge {
    background: linear-gradient(45deg, #17a2b8, #138496);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
    margin: 2px;
}

/* Room Selection Grid */
.room-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 8px;
    margin-top: 10px;
}

.room-select-card {
    position: relative;
    display: block;
    cursor: pointer;
    user-select: none;
}

.room-select-card input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.room-card-body {
    border: 2px solid #d4c7ed;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    transition: all 0.2s ease;
    background-color: #fff;
}

.room-select-card input:checked ~ .room-card-body {
    border-color: #4e73df;
    background-color: #4e73df;
    color: white;
    box-shadow: 0 3px 8px rgba(78, 115, 223, 0.25);
}

.room-select-card.occupied .room-card-body {
    background-color: #d4c7ed;
    border-color: #d4c7ed;
    color: #b7b9bc;
    cursor: not-allowed;
}

.occupied-badge {
    display: block;
    font-size: 0.75rem;
    color: #e74a3b;
    margin-top: 4px;
}

/* Enhanced Button Styles */
.btn {
    border: none;
    color: white !important;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7) !important;
}

.btn-warning, .btn-check-out, .main-checkout-btn {
    background: linear-gradient(45deg, #dc3545, #c82333) !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 10px 20px !important;
}

.btn-info {
    background: linear-gradient(45deg, #17a2b8, #0dcaf0) !important;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
}

/* Team Colors */
.team-Team1 { background: linear-gradient(to bottom, #d4c7ed, #deb5f7) !important; }
.team-Team2 { background: linear-gradient(to bottom, #b3ffb3, #80ff80) !important; }
.team-Team3 { background: linear-gradient(to bottom, #b3d9ff, #80bfff) !important; }
.team-Team4 { background: linear-gradient(to bottom, #ffd9b3, #ffbf80) !important; }
.team-Team5 { background: linear-gradient(to bottom, #ffb3ff, #ff80ff) !important; }
.team-Team6 { background: linear-gradient(to bottom, #b3fff9, #80fff5) !important; }
.team-Team7 { background: linear-gradient(to bottom, #ffcccc, #ff9999) !important; }
.team-Team8 { background: linear-gradient(to bottom, #d9ffcc, #b3ff99) !important; }
.team-Team9 { background: linear-gradient(to bottom, #ccd9ff, #99b3ff) !important; }
.team-Team10 { background: linear-gradient(to bottom, #fff2cc, #ffe699) !important; }

/* Status Row Highlighting */
.temp-out-row {
    border-left: 4px solid #17a2b8 !important;
}

.checked-out-row {
    border-left: 4px solid #28a745 !important;
    background-color: rgba(40, 167, 69, 0.05);
}

/* Count Controls */
.count-control {
    max-width: 150px;
}

.count-control .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 38px;
}

.count-control input {
    height: 38px;
    font-weight: bold;
}

/* Option Cards */
.option-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #d4c7ed;
}

.option-card:hover {
    border-color: #4e73df;
    background-color: #d4c7ed;
}

.option-card.active {
    border-color: #4e73df;
    background-color: #d4c7ed;
}

/* Quick Filters */
.quick-filters .btn {
    border-radius: 30px;
    font-size: 0.9rem;
    padding: 8px 16px;
    margin-right: 8px;
    margin-bottom: 8px;
    display: inline-flex;
    align-items: center;
}

.quick-filters .btn i {
    margin-right: 6px;
}

/* Quick Search */
#quickSearch {
    height: 50px;
    border-radius: 25px;
    padding-left: 50px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
    background-repeat: no-repeat;
    background-position: 20px center;
}

/* Responsive Adjustments for Tablets */
@media (max-width: 1024px) {
    .room-selection-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .btn {
        padding: 12px 20px;
        min-height: 50px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .room-selection-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .room-card-body {
        padding: 15px 10px;
    }
    
    .count-control .btn {
        width: 50px;
        height: 50px;
    }
    
    .count-control input {
        height: 50px;
        font-size: 18px;
    }
}

/* Animation */
@keyframes highlight {
    0% { background-color: rgba(255, 255, 0, 0.5); }
    100% { background-color: transparent; }
}

.highlight {
    animation: highlight 1s ease-out;
}
</style>

<!-- JavaScript Functions for Enhanced UI -->
<script>
// Toggle option cards and their inputs
function toggleOption(inputId) {
    const checkbox = document.getElementById(inputId);
    checkbox.checked = !checkbox.checked;
    
    if (inputId === 'showRoom') {
        toggleRoomInput(checkbox);
        document.getElementById('roomOptionCard').closest('.card').classList.toggle('active', checkbox.checked);
    } else if (inputId === 'showPool') {
        togglePoolInput(checkbox);
        document.getElementById('poolOptionCard').closest('.card').classList.toggle('active', checkbox.checked);
    }
}

// Toggle room input section
function toggleRoomInput(checkbox) {
    document.getElementById('roomSection').style.display = checkbox.checked ? 'block' : 'none';
    if (!checkbox.checked) {
        // Clear all room checkboxes when hiding the section
        document.querySelectorAll('input[name="room_numbers[]"]').forEach(cb => {
            cb.checked = false;
        });
    }
}

// Toggle pool input section
function togglePoolInput(checkbox) {
    document.getElementById('poolSection').style.display = checkbox.checked ? 'block' : 'none';
    if (!checkbox.checked) {
        // Clear pool counts when hiding the section
        document.querySelector('input[name="adult_pool_count"]').value = "0";
        document.querySelector('input[name="kids_pool_count"]').value = "0";
    }
}

// Increment and decrement count controls
function incrementCount(inputId) {
    const input = document.getElementById(inputId);
    input.value = parseInt(input.value || 0) + 1;
}

function decrementCount(inputId) {
    const input = document.getElementById(inputId);
    const currentValue = parseInt(input.value || 0);
    input.value = currentValue > 0 ? currentValue - 1 : 0;
}

// Quick search function
function quickSearchVehicle(searchTerm) {
    searchTerm = searchTerm.toLowerCase();
    const rows = document.querySelectorAll('#vehicleTableBody tr');
    
    rows.forEach(row => {
        const vehicleNumber = row.querySelector('.vehicle-number').textContent.toLowerCase();
        const matter = row.querySelector('.matter').textContent.toLowerCase();
        const description = row.querySelector('.description').textContent.toLowerCase();
        
        if (vehicleNumber.includes(searchTerm) || 
            matter.includes(searchTerm) || 
            description.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Original functions from scripts.blade.php
function showAlert(message, type = 'success') {
   const alert = `
       <div class="alert alert-${type} alert-dismissible fade show">
           ${message}
           <button type="button" class="close" data-dismiss="alert">&times;</button>
       </div>`;
   const alertsContainer = document.querySelector('.card-body');
   alertsContainer.insertAdjacentHTML('afterbegin', alert);
}

function updateTeam(id, team) {
   fetch(`/vehicle-security/${id}/update-team`, {
       method: 'POST',
       headers: {
           'Content-Type': 'application/json',
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
       },
       body: JSON.stringify({ team: team })
   })
   .then(response => response.json())
   .then(data => {
       if(data.success) {
           const row = document.querySelector(`tr[data-vehicle-id="${id}"]`);
           row.className = team ? `team-${team.replace(' ', '')}` : '';
           row.classList.add('highlight');
           showAlert('Team updated successfully');
       }
   })
   .catch(error => {
       showAlert('Error updating team', 'danger');
       console.error('Error:', error);
   });
}

function addVehicle(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Handle room numbers
    const selectedRooms = [];
    form.querySelectorAll('input[name="room_numbers[]"]:checked').forEach(checkbox => {
        selectedRooms.push(checkbox.value);
    });
    formData.set('room_numbers', JSON.stringify(selectedRooms));

    // Handle note status
    const isNote = event.submitter && event.submitter.name === 'is_note';
    if (isNote) {
        formData.set('is_note', '1');
    }

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const newRow = createVehicleRow(data.vehicle);
            document.querySelector('#vehicleTableBody').insertAdjacentHTML('afterbegin', newRow);
            form.reset();
            document.getElementById('roomSection').style.display = 'none';
            document.getElementById('poolSection').style.display = 'none';
            showAlert(data.message);
        }
    })
    .catch(error => {
        showAlert('Error creating entry', 'danger');
        console.error('Error:', error);
    });
}

function checkoutVehicle(event, id) {
    event.preventDefault();
    const form = event.target;
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const row = document.querySelector(`tr[data-vehicle-id="${id}"]`);
            row.classList.add('checked-out-row');
            
            // Update checkout time cell
            row.querySelector('.checkout-cell').innerHTML = `
                <span class="checkout-badge">
                    ${data.checkout_time}
                    <br>
                    <small class="duration-badge">Duration: ${data.duration_hours} hours</small>
                </span>
            `;
            
            // Update action cell to show checked out status
            row.querySelector('.actions-cell').innerHTML = `
                <span class="checkout-status-badge">
                    <i class="fas fa-check-circle"></i> Checked Out
                </span>
            `;
            
            showAlert('Vehicle checked out successfully');
        }
    })
    .catch(error => {
        showAlert('Error checking out vehicle', 'danger');
        console.error('Error:', error);
    });
}

function tempCheckout(id) {
    fetch(`/vehicle-security/${id}/temp-checkout`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const row = document.querySelector(`tr[data-vehicle-id="${id}"]`);
            row.classList.add('temp-out-row');
            
            // Update checkout time display
            row.querySelector('.checkout-cell').innerHTML = `
                <span class="temp-badge">
                    Temp Out: ${new Date(data.vehicle.temp_checkout_time).toLocaleString()}
                </span>
            `;
            
            // Update action buttons
            const actionsCell = row.querySelector('.actions-cell');
            actionsCell.innerHTML = `
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary" onclick="editVehicle(${id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form action="/vehicle-security/${id}/checkout" method="POST" style="display:inline;" 
                          onsubmit="checkoutVehicle(event, ${id})">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="fas fa-sign-out-alt"></i> Check Out
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-success" onclick="tempCheckin(${id})">
                        <i class="fas fa-undo"></i> Temp In
                    </button>
                </div>
            `;
            
            showAlert('Vehicle temporarily checked out');
        }
    })
    .catch(error => {
        showAlert('Error in temporary checkout', 'danger');
        console.error('Error:', error);
    });
}

function tempCheckin(id) {
    fetch(`/vehicle-security/${id}/temp-checkin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const row = document.querySelector(`tr[data-vehicle-id="${id}"]`);
            row.classList.remove('temp-out-row');
            
            // Update checkout time display to show both times
            row.querySelector('.checkout-cell').innerHTML = `
                <span class="temp-badge">
                    Temp Out: ${new Date(data.vehicle.temp_checkout_time).toLocaleString()}
                    <br>
                    <small class="temp-in-time">
                        Temp In: ${new Date(data.vehicle.temp_checkin_time).toLocaleString()}
                    </small>
                </span>
            `;
            
            // Update action buttons
            const actionsCell = row.querySelector('.actions-cell');
            actionsCell.innerHTML = `
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary" onclick="editVehicle(${id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form action="/vehicle-security/${id}/checkout" method="POST" style="display:inline;" 
                          onsubmit="checkoutVehicle(event, ${id})">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="fas fa-sign-out-alt"></i> Check Out
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-info" onclick="tempCheckout(${id})">
                        <i class="fas fa-clock"></i> Temp Out
                    </button>
                </div>
            `;
            
            showAlert('Vehicle checked back in');
        }
    })
    .catch(error => {
        showAlert('Error in temporary check-in', 'danger');
        console.error('Error:', error);
    });
}

function editVehicle(id) {
   fetch(`/vehicle-security/${id}/edit`)
       .then(response => response.json())
       .then(data => {
           if (data.checkout_time) {
               showAlert('Cannot edit checked-out vehicle', 'warning');
               return;
           }
           const form = document.getElementById('editForm');
           form.action = `/vehicle-security/${id}`;
           form.elements.vehicle_number.value = data.vehicle_number;
           form.elements.matter.value = data.matter;
           form.elements.description.value = data.description || '';
           
           // Clear previous room selections
           form.querySelectorAll('input[name="room_numbers[]"]').forEach(checkbox => {
               checkbox.checked = false;
           });
           
           // Set selected rooms
           if (data.room_numbers) {
               const selectedRooms = JSON.parse(data.room_numbers);
               selectedRooms.forEach(room => {
                   const checkbox = form.querySelector(`input[value="${room}"]`);
                   if (checkbox) checkbox.checked = true;
               });
           }
           
           form.elements.adult_pool_count.value = data.adult_pool_count || 0;
           form.elements.kids_pool_count.value = data.kids_pool_count || 0;
           
           $('#editModal').modal('show');
       })
       .catch(error => {
           showAlert('Error loading vehicle data', 'danger');
           console.error('Error:', error);
       });
}

function updateVehicle(event) {
   event.preventDefault();
   const form = event.target;
   const formData = new FormData(form);
   
   // Collect selected room numbers
   const selectedRooms = [];
   form.querySelectorAll('input[name="room_numbers[]"]:checked').forEach(checkbox => {
       selectedRooms.push(checkbox.value);
   });
   formData.set('room_numbers', JSON.stringify(selectedRooms));
   
   fetch(form.action, {
       method: 'POST',
       body: formData,
       headers: {
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
       }
   })
   .then(response => response.json())
   .then(data => {
       if(data.success) {
           $('#editModal').modal('hide');
           const row = document.querySelector(`tr[data-vehicle-id="${data.vehicle.id}"]`);
           updateRowData(row, data.vehicle);
           row.classList.add('highlight');
           showAlert('Vehicle updated successfully');
       }
   })
   .catch(error => {
       showAlert('Error updating vehicle', 'danger');
       console.error('Error:', error);
   });
}

function updateRowData(row, data) {
   row.querySelector('.vehicle-number').textContent = data.vehicle_number;
   row.querySelector('.matter').textContent = data.matter;
   row.querySelector('.description').textContent = data.description || '';
   
   // Update room badges
   const roomBadges = data.room_numbers ? 
       JSON.parse(data.room_numbers)
           .map(room => `<span class="room-badge">${room}</span>`)
           .join('') : '';
   row.querySelector('.room').innerHTML = roomBadges;
   
   if(data.adult_pool_count || data.kids_pool_count) {
       row.querySelector('.pool-cell').innerHTML = `
           <span class="pool-badge">
               ${data.adult_pool_count}/${data.kids_pool_count}
           </span>
       `;
   } else {
       row.querySelector('.pool-cell').innerHTML = '';
   }
}

function createVehicleRow(vehicle) {
   const date = new Date(vehicle.created_at);
   const formattedDate = date.toLocaleString('en-US', {
       year: 'numeric',
       month: '2-digit',
       day: '2-digit',
       hour: '2-digit',
       minute: '2-digit',
       hour12: true
   });

   let actionsCell = '';
   if(!vehicle.is_note) {
       if(vehicle.checkout_time) {
           actionsCell = `
               <span class="checkout-status-badge">
                   <i class="fas fa-check-circle"></i> Checked Out
               </span>
           `;
       } else {
           actionsCell = `
               <div class="btn-group">
                   <button type="button" class="btn btn-sm btn-primary" onclick="editVehicle(${vehicle.id})">
                       <i class="fas fa-edit"></i> Edit
                   </button>
                   <form action="/vehicle-security/${vehicle.id}/checkout" method="POST" style="display:inline;" 
                         onsubmit="checkoutVehicle(event, ${vehicle.id})">
                       @csrf
                       <button type="submit" class="btn btn-lg main-checkout-btn mx-2">
                           <i class="fas fa-sign-out-alt"></i> CHECK OUT
                       </button>
                   </form>
                   ${!vehicle.is_temp_out ? 
                       `<button type="button" class="btn btn-sm btn-info" onclick="tempCheckout(${vehicle.id})">
                           <i class="fas fa-clock"></i> Temp Out
                       </button>` : 
                       `<button type="button" class="btn btn-sm btn-success" onclick="tempCheckin(${vehicle.id})">
                           <i class="fas fa-undo"></i> Temp In
                       </button>`
                   }
               </div>
           `;
       }
   }

   return `
       <tr data-vehicle-id="${vehicle.id}" class="${vehicle.team ? 'team-' + vehicle.team.replace(' ', '') : ''}
           ${vehicle.is_temp_out ? 'temp-out-row' : ''} 
           ${vehicle.checkout_time ? 'checked-out-row' : ''}">
           <td class="align-middle">
               <span class="checkin-badge">
                   ${formattedDate}
               </span>
           </td>
           <td class="align-middle vehicle-number">${vehicle.vehicle_number}</td>
           <td class="align-middle matter">${vehicle.matter}</td>
           <td class="align-middle description">${vehicle.description || ''}</td>
           <td class="align-middle room">
               ${vehicle.room_numbers ? JSON.parse(vehicle.room_numbers).map(room => 
                   `<span class="room-badge">${room}</span>`
               ).join('') : ''}
           </td>
           <td class="align-middle pool-cell">
               ${vehicle.adult_pool_count || vehicle.kids_pool_count ? 
                   `<span class="pool-badge">
                       ${vehicle.adult_pool_count}/${vehicle.kids_pool_count}
                   </span>` : ''
               }
           </td>
           <td class="align-middle checkout-cell">
               ${vehicle.checkout_time ? 
                   `<span class="checkout-badge">
                       ${new Date(vehicle.checkout_time).toLocaleString('en-US', {
                           year: 'numeric',
                           month: '2-digit',
                           day: '2-digit',
                           hour: '2-digit',
                           minute: '2-digit',
                           hour12: true
                       })}
                       ${vehicle.duration_hours ? 
                           `<br><small class="duration-time">Duration: ${vehicle.duration_hours} hours</small>` 
                           : ''
                       }
                   </span>` :
                   vehicle.temp_checkout_time ?
                       `<span class="temp-badge">
                           Temp Out: ${new Date(vehicle.temp_checkout_time).toLocaleString('en-US', {
                               year: 'numeric',
                               month: '2-digit',
                               day: '2-digit',
                               hour: '2-digit',
                               minute: '2-digit',
                               hour12: true
                           })}<br>
                           ${vehicle.temp_checkin_time ?
                               `<small class="temp-in-time">
                                   Temp In: ${new Date(vehicle.temp_checkin_time).toLocaleString('en-US', {
                                       year: 'numeric',
                                       month: '2-digit',
                                       day: '2-digit',
                                       hour: '2-digit',
                                       minute: '2-digit',
                                       hour12: true
                                   })}
                               </small>` : ''
                           }
                       </span>` :
                       `<span class="text-warning">-</span>`
               }
           </td>
           <td class="align-middle team-cell">
               ${!vehicle.checkout_time ?
                   `<select class="form-control form-control-sm" onchange="updateTeam(${vehicle.id}, this.value)">
                       <option value="">Select Team</option>
                       ${[1,2,3,4,5,6,7,8,9,10].map(i => 
                           `<option value="Team ${i}" ${vehicle.team === `Team ${i}` ? 'selected' : ''}>
                               Team ${i}
                           </option>`
                       ).join('')}
                   </select>` :
                   vehicle.team ? 
                       `<span class="team-badge team-badge-${vehicle.team.replace(' ', '')}">
                           ${vehicle.team}
                       </span>` : ''
               }
           </td>
           <td class="align-middle actions-cell">${actionsCell}</td>
       </tr>
   `;
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if Bootstrap's tooltip is available
    if(typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Initialize the active state of option cards
    if(document.getElementById('showRoom').checked) {
        document.getElementById('roomOptionCard').closest('.card').classList.add('active');
    }
    
    if(document.getElementById('showPool').checked) {
        document.getElementById('poolOptionCard').closest('.card').classList.add('active');
    }
});
</script>

@endsection

@push('scripts')
    {{-- Additional scripts can be pushed here --}}
@endpush