@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Service Charge Summary</h5>
                        <select name="month" class="form-select-dark w-auto" onchange="this.form.submit()" form="month-form">
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <form id="month-form" action="{{ route('home') }}" method="GET"></form>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="alert alert-secondary mb-0 text-center shadow-sm">
                                <h6 class="mb-1 text-muted">Billed S/C</h6>
                                <h4 class="mb-0">Rs {{ number_format($billedSC, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-secondary mb-0 text-center shadow-sm">
                                <h6 class="mb-1 text-muted">Assigned S/C</h6>
                                <h4 class="mb-0">Rs {{ number_format($includedSC, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-dark mb-0 text-center shadow-sm">
                                <h6 class="mb-1 text-muted">Total S/C</h6>
                                <h4 class="mb-0 fw-bold">Rs {{ number_format($serviceCharge, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(to right, #d81b60, #e11d48); border-radius: 8px 8px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold">
                        <i class="fas fa-broom me-2"></i> Housekeeping Status
                    </h5>
                    <div>
                        <button onclick="showManageRoomsModal()" class="btn btn-sm text-white py-0 px-2 border-0 shadow-none me-1" style="background: rgba(255,255,255,0.2);" title="Manage Rooms">
                            <i class="fas fa-cog"></i>
                        </button>
                        <button onclick="showHousekeepingLogs()" class="btn btn-sm text-white py-0 px-2 border-0 shadow-none me-1" style="background: rgba(255,255,255,0.2);" title="View History">
                            <i class="fas fa-history"></i>
                        </button>
                        <button onclick="refreshHousekeeping()" class="btn btn-sm text-white py-0 px-2 border-0 shadow-none" style="background: rgba(255,255,255,0.2);" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 bg-white" style="border: 1px solid #eaeaea; border-top: 0; border-radius: 0 0 8px 8px;">
                    <!-- Housekeeping Stats -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="hk-stat-box" style="background-color: #f8f9fa;">
                                <div class="hk-stat-title text-muted">Total</div>
                                <div class="hk-stat-val text-dark" id="hkTotal">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hk-stat-box" style="background-color: #dcfce7;">
                                <div class="hk-stat-title" style="color: #15803d;">Available</div>
                                <div class="hk-stat-val" style="color: #15803d;" id="hkAvailable">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hk-stat-box" style="background-color: #fef9c3;">
                                <div class="hk-stat-title" style="color: #a16207;">Occupied</div>
                                <div class="hk-stat-val" style="color: #a16207;" id="hkOccupied">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hk-stat-box" style="background-color: #fee2e2;">
                                <div class="hk-stat-title" style="color: #b91c1c;">Needs Cleaning</div>
                                <div class="hk-stat-val" style="color: #b91c1c;" id="hkNeedsCleaning">0</div>
                            </div>
                        </div>
                    </div>
                    <!-- Room Grid -->
                    <div id="hkRoomGrid" class="d-flex flex-wrap gap-2">
                        <div class="text-center py-2 text-muted small w-100"><i class="fas fa-spinner fa-spin"></i> Loading rooms...</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <h5 class="mb-0">Dashboard</h5>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    <div class="row g-4">
                        @foreach([
                            ['route' => 'management', 'title' => 'Management', 'icon' => 'management'],
                            ['route' => 'cashier', 'title' => 'Cashier', 'icon' => 'cashier'],
                            ['url' => '/inventory/stock', 'title' => 'Beer & Soft Drink Stock', 'icon' => 'bottle'],
                            ['route' => 'report', 'title' => 'Report', 'icon' => 'report', 'admin' => true],
                            ['url' => '/calendar', 'title' => 'Booking Calendar', 'icon' => 'calendar'],
                            ['url' => '/stock', 'title' => 'Grocery Item Store', 'icon' => 'food'],
                            ['url' => '/inv-inventory', 'title' => 'Physical Item Inventory', 'icon' => 'inv'],
                            ['url' => '/costs', 'title' => 'Daily Expense', 'icon' => 'expense'],
                            ['url' => '/tasks', 'title' => 'Daily Tasks', 'icon' => 'task'],
                            ['url' => '/rooms/availability', 'title' => 'Room Availability', 'icon' => 'room'],
                            ['url' => '/manual-attendance', 'title' => 'Staff Attendance', 'icon' => 'attendance'], 
                            ['url' => '/vehicle-security', 'title' => 'Security Management', 'icon' => 'vehicle'],
                            ['url' => '/packages', 'title' => 'Hotel Packages', 'icon' => 'package'],
                            ['url' => '/quotations', 'title' => 'Quotations', 'icon' => 'quotation'],
                            ['url' => '/damage-items', 'title' => 'Damage Items', 'icon' => 'damage'],
                            ['url' => '/salary', 'title' => 'Monthly Salay', 'icon' => 'salary'],
                            ['url' => '/service-charge', 'title' => 'service-charge', 'icon' => 'service'],
                            ['url' => '/cashier/balance', 'title' => 'Balance', 'icon' => 'balance'],
                            ['url' => '/lenders', 'title' => 'Creditors', 'icon' => 'credit'],
                            ['url' => '/report/daily-summary', 'title' => 'Daily Summary', 'icon' => 'dail'],
                            ['url' => '/kitchen', 'title' => 'Kitchen', 'icon' => 'kitchen'],
                            ['url' => '/leave-requests', 'title' => 'Staff Leave Request', 'icon' => 'leave'],
                            ['url' => '/gate-passes', 'title' => 'Gate Passes', 'icon' => 'gate'],
                            ['url' => '/kitchen/comparison', 'title' => 'Kitchen & Sales', 'icon' => 'compare'],
                            ['url' => '/staff-information', 'title' => 'Staff Informations', 'icon' => 'staff'],
                            ['url' => '/water-bottle', 'title' => 'Water Bottle Issuance', 'icon' => 'bottle'],
                            ['url' => '/welfare-fund', 'title' => 'Welfare Fund', 'icon' => 'welfare'],
                            ['url' => '/duty-roster', 'title' => 'Duty Roster', 'icon' => 'room'],
                            ['url' => '/leads', 'title' => 'Lead Management (CRM)', 'icon' => 'crm'],
                            ['url' => '/gas', 'title' => 'LP Gas Management', 'icon' => 'gas'],
                            ] as $item)
                            @if(!isset($item['admin']) || (isset($item['admin']) && Auth::user()->checkAdmin()))
                                <div class="col-lg-4 col-md-6">
                                    <a href="{{ isset($item['route']) ? route($item['route']) : $item['url'] }}" 
                                       class="text-decoration-none">
                                        <div class="card h-100 menu-card">
                                            <div class="card-body text-center p-4">
                                                <img class="mb-3" width="60" src="{{ asset('image/' . $item['icon'] . '.svg') }}" alt="{{ $item['title'] }}"/>
                                                <h5 class="text-dark mb-0">{{ $item['title'] }}</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Housekeeping Logs Modal -->
    <div class="modal fade" id="housekeepingLogsModal" tabindex="-1" aria-labelledby="housekeepingLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="housekeepingLogsModalLabel"><i class="fas fa-history me-2"></i>Housekeeping Status History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="hkLogsContainer" class="p-3">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading history...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Rooms Modal -->
    <div class="modal fade" id="manageRoomsModal" tabindex="-1" aria-labelledby="manageRoomsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="manageRoomsModalLabel"><i class="fas fa-cog me-2"></i>Manage Rooms</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs for Rooms and Teams -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms-content" type="button" role="tab">
                                <i class="fas fa-door-open me-1"></i> Rooms
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams-content" type="button" role="tab">
                                <i class="fas fa-users me-1"></i> Teams
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Rooms Tab -->
                        <div class="tab-pane fade show active" id="rooms-content" role="tabpanel">
                            <!-- Add Room Form -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Room</h6>
                                </div>
                                <div class="card-body">
                                    <form id="addRoomForm" onsubmit="addRoom(event)">
                                        <div class="input-group">
                                            <input type="text" id="newRoomName" class="form-control" placeholder="Enter room number or name" required>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add Room
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Existing Rooms List -->
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Existing Rooms</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div id="roomsListContainer" style="max-height: 400px; overflow-y: auto;">
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i> Loading rooms...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Teams Tab -->
                        <div class="tab-pane fade" id="teams-content" role="tabpanel">
                            <!-- Add Team Form -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Team/Group</h6>
                                </div>
                                <div class="card-body">
                                    <form id="addTeamForm" onsubmit="addTeam(event)">
                                        <div class="mb-2">
                                            <input type="text" id="newTeamName" class="form-control form-control-sm" placeholder="Team name (e.g., Cricket Team, Family Group)" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">Choose Color:</label>
                                            <div class="d-flex gap-2">
                                                <label class="color-option">
                                                    <input type="radio" name="teamColor" value="#3b82f6" required>
                                                    <span class="color-box" style="background: #3b82f6"></span>
                                                </label>
                                                <label class="color-option">
                                                    <input type="radio" name="teamColor" value="#10b981" required>
                                                    <span class="color-box" style="background: #10b981"></span>
                                                </label>
                                                <label class="color-option">
                                                    <input type="radio" name="teamColor" value="#f59e0b" required>
                                                    <span class="color-box" style="background: #f59e0b"></span>
                                                </label>
                                                <label class="color-option">
                                                    <input type="radio" name="teamColor" value="#ef4444" required>
                                                    <span class="color-box" style="background: #ef4444"></span>
                                                </label>
                                                <label class="color-option">
                                                    <input type="radio" name="teamColor" value="#8b5cf6" required>
                                                    <span class="color-box" style="background: #8b5cf6"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <textarea id="newTeamNotes" class="form-control form-control-sm" placeholder="Notes (optional)" rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus me-1"></i> Add Team
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Existing Teams List -->
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Existing Teams</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div id="teamsListContainer" style="max-height: 350px; overflow-y: auto;">
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i> Loading teams...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="card mt-4 shadow-sm">
   <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
       <h5 class="mb-0">Room Check-in Vehicles</h5>
       <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
           <input type="date" 
                  name="date" 
                  class="form-control form-control-sm me-2 dark-input" 
                  value="{{ request('date', date('Y-m-d')) }}">
           <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
       </form>
   </div>
   <div class="card-body">
       <div class="table-responsive">
           <table class="table table-hover">
               <thead class="table-dark">
                   <tr>
                       <th>Check In Time</th>
                       <th>Vehicle Number</th>
                       <th>Room Numbers</th>
                       <th>Description</th>
                       <th>Check Out Time</th>
                       <th>Status</th>
                   </tr>
               </thead>
               <tbody>
                   @forelse($roomVehicles as $vehicle)
                       @if($vehicle->room_numbers && is_array(json_decode($vehicle->room_numbers)) && !empty(json_decode($vehicle->room_numbers)))
                           <tr class="{{ $vehicle->team ? 'team-'.str_replace(' ', '', $vehicle->team) : '' }}">
                               <td>{{ $vehicle->created_at->format('Y-m-d H:i') }}</td>
                               <td>{{ $vehicle->vehicle_number }}</td>
                               <td>
                                   @foreach(json_decode($vehicle->room_numbers) as $room)
                                       <span class="room-badge">{{ $room }}</span>
                                   @endforeach
                               </td>
                               <td>{{ $vehicle->description }}</td>
                               <td>
                                   @if($vehicle->checkout_time)
                                       <span class="text-success">
                                           {{ $vehicle->checkout_time->format('Y-m-d H:i') }}
                                       </span>
                                   @else
                                       <span class="text-warning">Not checked out</span>
                                   @endif
                               </td>
                               <td>
                                   @if($vehicle->checkout_time)
                                       <span class="badge bg-success">Checked Out</span>
                                   @else
                                       <span class="badge bg-warning text-dark">Checked In</span>
                                   @endif
                               </td>
                           </tr>
                       @endif
                   @empty
                       <tr>
                           <td colspan="6" class="text-center text-muted py-4">
                               No room check-ins found for selected date
                           </td>
                       </tr>
                   @endforelse
               </tbody>
           </table>
       </div>
   </div>
</div>


<!-- Water Bottle Summary Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-tint"></i> Water Bottle Summary</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="water_bottle_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ $waterBottleDate }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('inventory_date'))
                    <input type="hidden" name="inventory_date" value="{{ request('inventory_date') }}">
                @endif
                @if(request('month'))
                    <input type="hidden" name="month" value="{{ request('month') }}">
                @endif
            </form>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-info me-3 fs-6">Current Stock: {{ $waterBottleCurrentStock }}</span>
            <a href="{{ route('water-bottle.index') }}" class="btn btn-sm btn-outline-light">
                Manage Water Bottles
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-danger mb-0 text-center">
                    <h6 class="mb-1">Issued</h6>
                    <h3 class="mb-0">{{ $waterBottleIssued }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-success mb-0 text-center">
                    <h6 class="mb-1">Added</h6>
                    <h3 class="mb-0">{{ $waterBottleAdded }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-{{ ($waterBottleAdded - $waterBottleIssued) >= 0 ? 'info' : 'warning' }} mb-0 text-center">
                    <h6 class="mb-1">Net Change</h6>
                    <h3 class="mb-0">{{ ($waterBottleAdded - $waterBottleIssued) >= 0 ? '+' : '' }}{{ $waterBottleAdded - $waterBottleIssued }}</h3>
                </div>
            </div>
        </div>
        
        @if($waterBottleHistory->count() > 0)
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Room/Note</th>
                        <th>Bill #</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waterBottleHistory as $record)
                    <tr class="{{ $record->stock > 0 ? 'table-success' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>
                            @if($record->stock > 0)
                                <span class="badge bg-success">Added</span>
                            @else
                                <span class="badge bg-danger">Issued</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                            </span>
                        </td>
                        <td>
                            @if($record->notes)
                                <span class="badge bg-info">{{ str_replace('Room: ', '', $record->notes) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge bg-primary">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-tint fa-2x mb-3 d-block"></i>
            No water bottle activity for {{ \Carbon\Carbon::parse($waterBottleDate)->format('M d, Y') }}
        </div>
        @endif
    </div>
</div>

<!-- Soft Drink Summary Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-glass-whiskey"></i> Soft Drink Summary</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="soft_drink_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ $softDrinkDate }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('water_bottle_date'))
                    <input type="hidden" name="water_bottle_date" value="{{ request('water_bottle_date') }}">
                @endif
            </form>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-info me-3 fs-6">Total Stock: {{ $softDrinkCurrentStock }}</span>
            <a href="{{ url('/inventory/stock') }}" class="btn btn-sm btn-outline-light">
                Manage Stock
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-danger mb-0 text-center">
                    <h6 class="mb-1">Issued</h6>
                    <h3 class="mb-0">{{ $softDrinkIssued }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-success mb-0 text-center">
                    <h6 class="mb-1">Added</h6>
                    <h3 class="mb-0">{{ $softDrinkAdded }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-{{ ($softDrinkAdded - $softDrinkIssued) >= 0 ? 'info' : 'warning' }} mb-0 text-center">
                    <h6 class="mb-1">Net Change</h6>
                    <h3 class="mb-0">{{ ($softDrinkAdded - $softDrinkIssued) >= 0 ? '+' : '' }}{{ $softDrinkAdded - $softDrinkIssued }}</h3>
                </div>
            </div>
        </div>
        
        @if($softDrinkHistory->count() > 0)
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Bill #</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($softDrinkHistory as $record)
                    <tr class="{{ $record->stock > 0 ? 'table-success' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>{{ $record->menu->name ?? 'Unknown' }}</td>
                        <td>
                            @if($record->stock > 0)
                                <span class="badge bg-success">Added</span>
                            @else
                                <span class="badge bg-danger">Issued</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                            </span>
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge bg-primary">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-glass-whiskey fa-2x mb-3 d-block"></i>
            No soft drink activity for {{ \Carbon\Carbon::parse($softDrinkDate)->format('M d, Y') }}
        </div>
        @endif
    </div>
</div>

<!-- Beer Summary Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-beer"></i> Beer Summary</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="beer_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ $beerDate }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('water_bottle_date'))
                    <input type="hidden" name="water_bottle_date" value="{{ request('water_bottle_date') }}">
                @endif
            </form>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-info me-3 fs-6">Total Stock: {{ $beerCurrentStock }}</span>
            <a href="{{ url('/inventory/stock') }}" class="btn btn-sm btn-outline-light">
                Manage Stock
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-danger mb-0 text-center">
                    <h6 class="mb-1">Issued</h6>
                    <h3 class="mb-0">{{ $beerIssued }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-success mb-0 text-center">
                    <h6 class="mb-1">Added</h6>
                    <h3 class="mb-0">{{ $beerAdded }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-{{ ($beerAdded - $beerIssued) >= 0 ? 'info' : 'warning' }} mb-0 text-center">
                    <h6 class="mb-1">Net Change</h6>
                    <h3 class="mb-0">{{ ($beerAdded - $beerIssued) >= 0 ? '+' : '' }}{{ $beerAdded - $beerIssued }}</h3>
                </div>
            </div>
        </div>
        
        @if($beerHistory->count() > 0)
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Bill #</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($beerHistory as $record)
                    <tr class="{{ $record->stock > 0 ? 'table-success' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>{{ $record->menu->name ?? 'Unknown' }}</td>
                        <td>
                            @if($record->stock > 0)
                                <span class="badge bg-success">Added</span>
                            @else
                                <span class="badge bg-danger">Issued</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                            </span>
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge bg-primary">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-beer fa-2x mb-3 d-block"></i>
            No beer activity for {{ \Carbon\Carbon::parse($beerDate)->format('M d, Y') }}
        </div>
        @endif
    </div>
</div>

<!-- Arrack Summary Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-wine-bottle"></i> Arrack Summary</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="arrack_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ $arrackDate }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('water_bottle_date'))
                    <input type="hidden" name="water_bottle_date" value="{{ request('water_bottle_date') }}">
                @endif
            </form>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-info me-3 fs-6">Total Stock: {{ $arrackCurrentStock }}</span>
            <a href="{{ url('/inventory/stock') }}" class="btn btn-sm btn-outline-light">
                Manage Stock
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-danger mb-0 text-center">
                    <h6 class="mb-1">Issued</h6>
                    <h3 class="mb-0">{{ $arrackIssued }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-success mb-0 text-center">
                    <h6 class="mb-1">Added</h6>
                    <h3 class="mb-0">{{ $arrackAdded }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-{{ ($arrackAdded - $arrackIssued) >= 0 ? 'info' : 'warning' }} mb-0 text-center">
                    <h6 class="mb-1">Net Change</h6>
                    <h3 class="mb-0">{{ ($arrackAdded - $arrackIssued) >= 0 ? '+' : '' }}{{ $arrackAdded - $arrackIssued }}</h3>
                </div>
            </div>
        </div>
        
        @if($arrackHistory->count() > 0)
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Bill #</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($arrackHistory as $record)
                    <tr class="{{ $record->stock > 0 ? 'table-success' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>{{ $record->menu->name ?? 'Unknown' }}</td>
                        <td>
                            @if($record->stock > 0)
                                <span class="badge bg-success">Added</span>
                            @else
                                <span class="badge bg-danger">Issued</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                            </span>
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge bg-primary">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-wine-bottle fa-2x mb-3 d-block"></i>
            No arrack activity for {{ \Carbon\Carbon::parse($arrackDate)->format('M d, Y') }}
        </div>
        @endif
    </div>
</div>

<!-- Swimming Pool Tickets Summary Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3"><i class="fas fa-swimming-pool"></i> Swimming Pool Tickets</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="pool_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ $poolDate }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('inventory_date'))
                    <input type="hidden" name="inventory_date" value="{{ request('inventory_date') }}">
                @endif
                @if(request('water_bottle_date'))
                    <input type="hidden" name="water_bottle_date" value="{{ request('water_bottle_date') }}">
                @endif
                @if(request('month'))
                    <input type="hidden" name="month" value="{{ request('month') }}">
                @endif
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-primary mb-0 text-center">
                    <h6 class="mb-1">Adult Tickets</h6>
                    <h3 class="mb-0">{{ $adultTicketsSold }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-info mb-0 text-center">
                    <h6 class="mb-1">Kids Tickets</h6>
                    <h3 class="mb-0">{{ $kidsTicketsSold }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-dark mb-0 text-center">
                    <h6 class="mb-1">Total Sold</h6>
                    <h3 class="mb-0">{{ $totalTicketsSold }}</h3>
                </div>
            </div>
        </div>
        
        @if($poolTicketHistory->count() > 0)
        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Bill #</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($poolTicketHistory as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>
                            @if($record->menu_id == 252)
                                <span class="badge bg-primary">Adult</span>
                            @else
                                <span class="badge bg-info">Kids</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                            </span>
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge bg-secondary">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-swimming-pool fa-2x mb-3 d-block"></i>
            No pool ticket sales for {{ \Carbon\Carbon::parse($poolDate)->format('M d, Y') }}
        </div>
        @endif
    </div>
</div>

<!-- Inventory Changes Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h5 class="mb-0 me-3">Inventory Changes</h5>
            <input type="date" id="homeInventoryDatePicker" 
                class="form-control form-control-sm dark-input"
                style="width: auto;"
                value="{{ date('Y-m-d') }}"
                onchange="loadHomeInventory(this.value)">
            <button onclick="loadHomeInventoryToday()" class="btn btn-sm btn-outline-light">Today</button>
        </div>
        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-light">
            Manage Inventory
        </a>
    </div>
    <div class="card-body">
        <!-- Summary Stats -->
        <div class="row mb-3">
            <div class="col-4 text-center">
                <div class="small text-muted">Total Changes</div>
                <div class="h5 fw-bold" id="homeInvTotalChanges">0</div>
            </div>
            <div class="col-4 text-center">
                <div class="small text-muted">Items Added</div>
                <div class="h5 fw-bold text-success" id="homeInvItemsAdded">0</div>
            </div>
            <div class="col-4 text-center">
                <div class="small text-muted">Items Removed</div>
                <div class="h5 fw-bold text-danger" id="homeInvItemsRemoved">0</div>
            </div>
        </div>

        <!-- Inventory List -->
        <div id="homeInventoryList">
            <div class="text-center py-4 text-muted">Loading...</div>
        </div>
        
        <!-- Cost Summary -->
        <div id="homeInventoryCostSummary" class="alert alert-warning border mt-3" style="display: none;">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <strong><i class="fas fa-plus-circle text-success me-1"></i>Cost Added:</strong> 
                    <span class="text-success fw-bold" id="homeInvCostAdded">Rs 0</span>
                </div>
                <div class="col-md-4 text-center">
                    <strong><i class="fas fa-minus-circle text-danger me-1"></i>Cost Used:</strong> 
                    <span class="text-danger fw-bold" id="homeInvCostUsed">Rs 0</span>
                </div>
                <div class="col-md-4 text-center">
                    <strong><i class="fas fa-calculator me-1"></i>Total Daily Cost:</strong> 
                    <span class="fw-bold text-danger fs-5" id="homeInvTotalDailyCost">Rs 0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let homeCurrentInventoryDate = '{{ date("Y-m-d") }}';

async function loadHomeInventory(date = null) {
    if (date) {
        homeCurrentInventoryDate = date;
    }
    
    const list = document.getElementById('homeInventoryList');
    list.innerHTML = '<div class="text-center py-4 text-muted">Loading...</div>';
    
    try {
        const url = `/api/duty-roster/inventory-changes?date=${homeCurrentInventoryDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            const summary = data.summary;
            
            // Update summary stats
            document.getElementById('homeInvTotalChanges').textContent = summary.total_changes;
            document.getElementById('homeInvItemsAdded').textContent = summary.items_added;
            document.getElementById('homeInvItemsRemoved').textContent = summary.items_removed;
            
            // Render inventory list
            renderHomeInventoryList(data.grouped_changes);
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
        list.innerHTML = '<div class="text-center text-danger py-4">Error loading data</div>';
    }
}

function renderHomeInventoryList(groupedChanges) {
    const list = document.getElementById('homeInventoryList');
    const costSummary = document.getElementById('homeInventoryCostSummary');
    
    if (!groupedChanges || groupedChanges.length === 0) {
        list.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-box-open fa-2x mb-3 d-block"></i>No inventory changes found</div>';
        costSummary.style.display = 'none';
        return;
    }
    
    let totalCostAdded = 0;
    let totalCostUsed = 0;
    
    let html = '<div class="accordion" id="homeInventoryAccordion">';
    groupedChanges.forEach((group, index) => {
        const groupId = `home_inv_group_${index}`;
        
        // Calculate category cost
        let categoryCostUsed = 0;
        group.items.forEach(log => {
            const cost = log.cost || 0;
            if (log.type === 'added') {
                totalCostAdded += cost;
            } else {
                totalCostUsed += cost;
                categoryCostUsed += cost;
            }
        });
        
        const costBadge = categoryCostUsed > 0 ? 
            `<span class="badge bg-danger ms-2">Rs ${categoryCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>` : '';
        
        html += `
            <div class="accordion-item border mb-2 rounded">
                <div class="accordion-header bg-light p-2 rounded-top d-flex justify-content-between align-items-center" 
                     style="cursor: pointer;" 
                     onclick="toggleHomeInvAccordion('${groupId}')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-right me-2 text-muted" id="icon_${groupId}" style="transition: transform 0.2s;"></i>
                        <strong>${group.name}</strong>
                    </div>
                    <div>
                        <span class="badge bg-secondary me-1">${group.count} items</span>
                        ${costBadge}
                    </div>
                </div>
                <div id="collapse_${groupId}" class="border-top" style="display: none;">
                    <div class="p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Item</th>
                                        <th>Action</th>
                                        <th>Location</th>
                                        <th>Qty</th>
                                        <th>Cost</th>
                                        <th>Stock</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${group.items.map(log => {
                                        const isAdded = log.type === 'added';
                                        const itemCost = log.cost || 0;
                                        const costDisplay = itemCost > 0 ? 
                                            `<span class="${isAdded ? 'text-success' : 'text-danger'}">Rs ${itemCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>` : 
                                            '<span class="text-muted">-</span>';
                                        const locationBadge = getHomeLocationBadge(log.action);
                                        
                                        return `
                                            <tr>
                                                <td>${log.time}</td>
                                                <td><strong>${log.item_name}</strong></td>
                                                <td>
                                                    ${isAdded ? 
                                                        '<span class="badge bg-success"><i class="fas fa-plus"></i></span>' : 
                                                        '<span class="badge bg-danger"><i class="fas fa-minus"></i></span>'}
                                                </td>
                                                <td>${locationBadge}</td>
                                                <td class="${isAdded ? 'text-success' : 'text-danger'} fw-bold">
                                                    ${isAdded ? '+' : '-'}${Math.abs(log.quantity)}
                                                </td>
                                                <td>${costDisplay}</td>
                                                <td>${log.current_stock}</td>
                                                <td><small>${log.user}</small></td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    list.innerHTML = html;
    
    // Update cost summary
    document.getElementById('homeInvCostAdded').textContent = 'Rs ' + totalCostAdded.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('homeInvCostUsed').textContent = 'Rs ' + totalCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('homeInvTotalDailyCost').textContent = 'Rs ' + totalCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Show cost summary
    costSummary.style.display = 'block';
}

function getHomeLocationBadge(action) {
    switch(action) {
        case 'add': return '<span class="badge bg-success">Stock</span>';
        case 'remove_main_kitchen': return '<span class="badge bg-primary">Main Kitchen</span>';
        case 'remove_banquet_hall_kitchen': return '<span class="badge bg-info">Banquet Kitchen</span>';
        case 'remove_banquet_hall': return '<span class="badge bg-warning text-dark">Banquet Hall</span>';
        case 'remove_restaurant': return '<span class="badge bg-success">Restaurant</span>';
        case 'remove_rooms': return '<span class="badge bg-secondary">Rooms</span>';
        case 'remove_garden': return '<span class="badge bg-dark">Garden</span>';
        case 'remove_other': return '<span class="badge bg-danger">Other</span>';
        default: return `<span class="badge bg-light text-dark">${action}</span>`;
    }
}

function toggleHomeInvAccordion(groupId) {
    const content = document.getElementById('collapse_' + groupId);
    const icon = document.getElementById('icon_' + groupId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function loadHomeInventoryToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('homeInventoryDatePicker').value = today;
    loadHomeInventory(today);
}

// Load inventory on page load
document.addEventListener('DOMContentLoaded', function() {
    loadHomeInventory();
});
</script>

<style>
.dark-input {
    background-color: #2d3748;
    border-color: #4a5568;
    color: white;
}

.dark-input:focus {
    background-color: #2d3748;
    border-color: #63b3ed;
    color: white;
    box-shadow: 0 0 0 0.2rem rgba(99, 179, 237, 0.25);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.025);
}

.badge {
    font-size: 0.75em;
    padding: 0.375em 0.75em;
}

.badge i {
    margin-right: 0.25rem;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>

    <div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <h5 class="mb-0">Pending Tasks <span class="badge bg-warning text-dark ms-2">{{ $pendingTasks->count() }}</span></h5>
        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-tasks"></i> View All Tasks
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th width="30%">Task</th>
                        <th>Department</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTasks->take(10) as $task)
                    <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                        <td>{{ $task->id }}</td>
                        <td>
                            {{ $task->date_added }}
                            @if($task->isOverdue())
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                        </td>
                        <td class="task-cell">{{ $task->task }}</td>
                        <td>
                            @if($task->staff_category)
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $task->staff_category)) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($task->assignedPerson)
                                {{ $task->assignedPerson->name }}
                            @else
                                <span class="text-muted">{{ $task->person_incharge ?? 'Unassigned' }}</span>
                            @endif
                        </td>
                        <td>
                            @if($task->priority_order == 'High')
                                <span class="badge bg-danger">High</span>
                            @elseif($task->priority_order == 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-success">Low</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_done" value="1">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> Done
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                            No pending tasks - All caught up!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($pendingTasks->count() > 10)
                <div class="text-center mt-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-dark btn-sm">
                        View all {{ $pendingTasks->count() }} pending tasks <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>


<!-- Add this after the Pending Tasks section in resources/views/home.blade.php -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0">Salary Advances</h5>
                    <select class="form-select form-select-sm bg-dark text-white border-secondary" 
                            style="width: auto; min-width: 200px;"
                            onchange="window.location.href='{{ route('home') }}?period=' + this.value">
                        @foreach($periods as $index => $period)
                            <option value="{{ $index }}" {{ $selectedPeriod == $index ? 'selected' : '' }}>
                                {{ $period['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 me-3 text-warning">Total: Rs. {{ number_format($totalAdvance, 2) }}</h6>
                <a href="{{ route('costs.create') }}" class="btn btn-sm btn-outline-light">Add Advance</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Balance Amount</th>
                        <th class="text-center">Number of Advances</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaryAdvances->groupBy('person_id') as $personId => $advances)
                        @php
                            $employeeName = $advances->first()->person->name ?? 'Unknown';
                            $balance = $salaryBalances[$personId] ?? 0;
                            $uniqueId = 'advance-' . $personId;
                        @endphp
                        <tr class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#{{ $uniqueId }}" style="cursor: pointer;">
                            <td>
                                <i class="fas fa-chevron-right me-2 collapse-icon" id="icon-{{ $uniqueId }}"></i>
                                <strong>{{ $employeeName }}</strong>
                            </td>
                            <td class="text-end text-danger fw-bold">Rs. {{ number_format($advances->sum('amount'), 2) }}</td>
                            <td class="text-end {{ $balance >= 0 ? 'text-success' : 'text-danger' }} fw-bold">Rs. {{ number_format($balance, 2) }}</td>
                            <td class="text-center">{{ $advances->count() }}</td>
                        </tr>
                        <tr class="collapse" id="{{ $uniqueId }}">
                            <td colspan="4" class="p-0">
                                <div class="bg-light p-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th style="width: 20%;">Date</th>
                                                <th style="width: 25%;">Amount</th>
                                                <th style="width: 40%;">Description</th>
                                                <th style="width: 15%;">Added By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($advances->sortByDesc('cost_date') as $advance)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($advance->cost_date)->format('M d, Y') }}</td>
                                                    <td class="text-danger fw-bold">Rs. {{ number_format($advance->amount, 2) }}</td>
                                                    <td>{{ $advance->description ?? '-' }}</td>
                                                    <td class="text-muted small">{{ $advance->user->name ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No salary advances found for this period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end text-warning"><strong>Rs. {{ number_format($totalAdvance, 2) }}</strong></td>
                        <td class="text-end text-success"><strong>Rs. {{ number_format(array_sum($salaryBalances), 2) }}</strong></td>
                        <td class="text-center"><strong>{{ $salaryAdvances->count() }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</div>

<style>
/* Modern Black & White Theme */
:root {
    --primary-black: #0a0a0a;
    --secondary-black: #1a1a1a;
    --accent-gray: #2d2d2d;
    --light-gray: #f8f9fa;
    --border-gray: #e0e0e0;
}

.bg-black {
    background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
}

.form-select-dark {
    background-color: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    border-radius: 0.5rem;
    backdrop-filter: blur(10px);
}

.form-select-dark option {
    background-color: var(--primary-black);
    color: white;
}

.dark-input {
    background-color: rgba(255,255,255,0.95);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.dark-input:focus {
    box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    border-color: var(--primary-black);
}

/* Modern Dashboard Cards */
.menu-card {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    border-radius: 1rem;
    background: white;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}

.menu-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-black), var(--accent-gray));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.menu-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.menu-card:hover::before {
    opacity: 1;
}

.menu-card .card-body {
    padding: 1.5rem;
}

.menu-card img {
    transition: transform 0.3s ease;
    filter: grayscale(0%);
}

.menu-card:hover img {
    transform: scale(1.1);
}

.menu-card h5 {
    font-weight: 600;
    letter-spacing: -0.02em;
}

/* Modern Card Styling */
.card {
    border-radius: 1rem;
    overflow: hidden;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.card-header {
    border-bottom: none;
    padding: 1.25rem 1.5rem;
}

.card-header h5 {
    font-weight: 600;
    letter-spacing: -0.01em;
}

.card-body {
    padding: 1.5rem;
}

/* Modern Table Styling */
.table {
    margin-bottom: 0;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 1rem;
    border-bottom: 2px solid var(--border-gray);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-gray);
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

.table-dark {
    background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
}

.table-dark th {
    border-bottom-color: rgba(255,255,255,0.1);
}

/* Modern Badges */
.badge {
    font-weight: 500;
    padding: 0.5em 0.85em;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
}

/* Modern Alerts */
.alert {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.alert-dark {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid var(--primary-black);
    color: var(--primary-black);
}

.alert-danger {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border-left: 4px solid #e53e3e;
}

.alert-success {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    border-left: 4px solid #38a169;
}

.alert-info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    border-left: 4px solid #3182ce;
}

.alert-warning {
    background: linear-gradient(135deg, #fffff0 0%, #fefcbf 100%);
    border-left: 4px solid #d69e2e;
}

/* Team Color Picker */
.color-option {
    cursor: pointer;
    display: inline-block;
}

.color-option input[type="radio"] {
    display: none;
}

.color-box {
    display: block;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 3px solid transparent;
    transition: all 0.2s;
}

.color-option input[type="radio"]:checked + .color-box {
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    transform: scale(1.1);
}

.color-option:hover .color-box {
    transform: scale(1.05);
}
}

/* Task Cell Highlight */
.task-cell {
    background: linear-gradient(135deg, #fffef0 0%, #fef9c3 100%);
    font-size: 1.05em;
    font-weight: 600;
    border-radius: 0.25rem;
}

/* Modern Buttons */
.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-outline-light {
    border-width: 2px;
}

.btn-outline-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255,255,255,0.2);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Section Dividers */
.card + .card {
    margin-top: 1.5rem;
}

/* Scrollbar Styling */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Room Badge */
.room-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--primary-black) 0%, var(--accent-gray) 100%);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 0.4rem;
    font-size: 0.8rem;
    font-weight: 500;
    margin: 0.1rem;
}

/* Sticky Table Header */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Animation for page load */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.5s ease forwards;
}

.card:nth-child(2) { animation-delay: 0.1s; }
.card:nth-child(3) { animation-delay: 0.2s; }
.card:nth-child(4) { animation-delay: 0.3s; }

/* Form Controls */
.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid var(--border-gray);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-black);
    box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
}

/* Stats Cards in Water Bottle Section */
.alert h3 {
    font-weight: 700;
    letter-spacing: -0.02em;
}

.alert h6 {
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.7rem;
    opacity: 0.8;
}

/* Housekeeping Widget Styles */
.hk-stat-box {
    text-align: center;
    border-radius: 0.5rem;
    padding: 0.75rem 0.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.hk-stat-title {
    font-size: 0.7rem;
    margin-bottom: 0.25rem;
    font-weight: 500;
}
.hk-stat-val {
    font-size: 1.25rem;
    font-weight: 700;
}
.hk-room-card:hover {
    opacity: 0.8;
}
.hk-room-card:active {
    transform: scale(0.95);
}

/* Chevron rotation for collapsible rows */
.collapse-icon {
    transition: transform 0.3s ease;
}
.collapse-icon.rotated {
    transform: rotate(90deg);
}
</style>

<script>
// Handle chevron icon rotation for salary advance dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Get all collapsible elements in salary advances
    const collapsibleElements = document.querySelectorAll('[id^="advance-"]');
    
    collapsibleElements.forEach(function(element) {
        const iconId = 'icon-' + element.id;
        const icon = document.getElementById(iconId);
        
        if (icon) {
            element.addEventListener('show.bs.collapse', function() {
                icon.classList.add('rotated');
            });
            
            element.addEventListener('hide.bs.collapse', function() {
                icon.classList.remove('rotated');
            });
        }
    });
});

// ===== Housekeeping Status =====
document.addEventListener('DOMContentLoaded', function() {
    loadHousekeepingStatus();
});

async function loadHousekeepingStatus() {
    try {
        // Show loading state
        document.getElementById('hkRoomGrid').innerHTML = '<div class="text-center py-2 text-muted small w-100"><i class="fas fa-spinner fa-spin"></i> Loading rooms...</div>';
        
        const response = await fetch('/api/duty-roster/housekeeping-status');
        const data = await response.json();
        
        if (data.success) {
            updateHousekeepingStats(data.stats);
            renderHousekeepingGrid(data.rooms);
        } else {
            document.getElementById('hkRoomGrid').innerHTML = '<div class="text-center py-2 text-danger small w-100">Failed to load data</div>';
        }
    } catch (error) {
        console.error('Error loading housekeeping status:', error);
        document.getElementById('hkRoomGrid').innerHTML = '<div class="text-center py-2 text-danger small w-100"><i class="fas fa-exclamation-triangle"></i> Error loading rooms</div>';
    }
}

function updateHousekeepingStats(stats) {
    if (document.getElementById('hkTotal')) document.getElementById('hkTotal').textContent = stats.total;
    if (document.getElementById('hkAvailable')) document.getElementById('hkAvailable').textContent = stats.available;
    if (document.getElementById('hkOccupied')) document.getElementById('hkOccupied').textContent = stats.occupied;
    if (document.getElementById('hkNeedsCleaning')) document.getElementById('hkNeedsCleaning').textContent = stats.needs_cleaning;
}

function getRoomStatusStyle(status) {
    switch (status) {
        case 'available':
            return { bg: '#dcfce7', text: '#15803d', icon: 'fa-check-circle', border: '#bbf7d0', label: 'Available' };
        case 'occupied':
            return { bg: '#fef9c3', text: '#a16207', icon: 'fa-user', border: '#fef08a', label: 'Occupied' };
        case 'needs_cleaning':
            return { bg: '#fee2e2', text: '#b91c1c', icon: 'fa-broom', border: '#fecaca', label: 'Needs Cleaning' };
        default:
            return { bg: '#dcfce7', text: '#15803d', icon: 'fa-check-circle', border: '#bbf7d0', label: 'Available' };
    }
}

function renderHousekeepingGrid(rooms) {
    const container = document.getElementById('hkRoomGrid');
    if (rooms.length === 0) {
        container.innerHTML = '<div class="text-center py-2 text-muted small w-100">No rooms found</div>';
        return;
    }
    container.innerHTML = rooms.map(room => {
        const s = getRoomStatusStyle(room.status);
        const isOccupied = room.status === 'occupied';
        const teamBorder = (isOccupied && room.team_color) ? `border-left: 4px solid ${room.team_color};` : '';
        const teamTooltip = room.team_name ? ` | Team: ${room.team_name}` : '';
        return `
            <div class="hk-room-card"
                 style="background-color: ${s.bg}; color: ${s.text}; border: 1px solid ${s.border}; ${teamBorder} padding: 6px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; user-select: none; transition: all 0.2s;"
                 title="${room.name}: ${s.label}${teamTooltip} (click to change)"
                 onclick="cycleRoomStatus(${room.id})"
                 id="hk-room-${room.id}">
                <i class="fas ${s.icon} me-1" style="font-size: 0.7rem;"></i> ${room.name}
            </div>
        `;
    }).join('');
}

async function cycleRoomStatus(roomId) {
    const el = document.getElementById('hk-room-' + roomId);
    if (!el) return;
    
    const originalOpacity = el.style.opacity;
    el.style.opacity = '0.5';
    
    try {
        const response = await fetch('/api/duty-roster/cycle-room-status', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            body: JSON.stringify({ room_id: roomId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const s = getRoomStatusStyle(data.new_status);
            el.style.backgroundColor = s.bg;
            el.style.color = s.text;
            el.style.borderColor = s.border;
            el.title = el.textContent.trim().split(' ').pop() + ': ' + s.label + ' (click to change)';
            
            const roomName = el.textContent.trim();
            el.innerHTML = `<i class="fas ${s.icon} me-1" style="font-size: 0.7rem;"></i> ${roomName}`;
            
            updateHousekeepingStats(data.stats);
        } else {
            alert('Failed to update room status');
        }
    } catch (error) {
        console.error('Error cycling room status:', error);
        alert('An error occurred while updating room status');
    } finally {
        el.style.opacity = originalOpacity || '1';
    }
}

function refreshHousekeeping() {
    loadHousekeepingStatus();
}

async function showHousekeepingLogs() {
    const modal = new bootstrap.Modal(document.getElementById('housekeepingLogsModal'));
    modal.show();
    
    const container = document.getElementById('hkLogsContainer');
    container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Loading history...</div>';
    
    try {
        const response = await fetch('/api/duty-roster/housekeeping-logs');
        const data = await response.json();
        
        if (data.success) {
            if (data.logs.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted">No recent status changes found.</div>';
                return;
            }
            
            let html = '<div class="list-group list-group-flush">';
            
            data.logs.forEach(log => {
                const oldStatusBadge = getStatusBadgeHTML(log.old_status || 'unknown');
                const newStatusBadge = getStatusBadgeHTML(log.new_status);
                
                html += `
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold">${log.room_name}</h6>
                            <small class="text-muted" title="${log.time}">${log.time_diff}</small>
                        </div>
                        <div class="mb-1 small">
                            ${oldStatusBadge} <i class="fas fa-arrow-right mx-1 text-muted"></i> ${newStatusBadge}
                        </div>
                        <small class="text-muted"><i class="fas fa-user me-1"></i> ${log.user_name}</small>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-4 text-danger">Failed to load history</div>';
        }
    } catch (error) {
        console.error('Error loading housekeeping logs:', error);
        container.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading history</div>';
    }
}

function getStatusBadgeHTML(status) {
    const style = getRoomStatusStyle(status);
    return `<span class="badge" style="background-color: ${style.bg}; color: ${style.text}; border: 1px solid ${style.border};">
                <i class="fas ${style.icon} me-1"></i>${style.label}
            </span>`;
}

async function showManageRoomsModal() {
    const modal = new bootstrap.Modal(document.getElementById('manageRoomsModal'));
    modal.show();
    await Promise.all([loadRoomsList(), loadTeamsList()]);
}

async function loadRoomsList() {
    const container = document.getElementById('roomsListContainer');
    container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Loading rooms...</div>';
    
    try {
        const response = await fetch('/api/duty-roster/rooms');
        const data = await response.json();
        
        if (data.success) {
            if (data.rooms.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted">No rooms found</div>';
                return;
            }
            
            // Fetch teams for the dropdown
            const teamsResponse = await fetch('/api/duty-roster/teams');
            const teamsData = await teamsResponse.json();
            const teams = teamsData.success ? teamsData.teams : [];
            
            let html = '<div class="list-group list-group-flush">';
            
            data.rooms.forEach(room => {
                const statusStyle = getRoomStatusStyle(room.housekeeping_status);
                const isBooked = room.is_booked;
                const teamColorBar = room.team_color ? `<div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: ${room.team_color};"></div>` : '';
                
                html += `
                    <div class="list-group-item" style="position: relative;">
                        ${teamColorBar}
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1" style="padding-left: ${room.team_color ? '8px' : '0'}">
                                <h6 class="mb-1 fw-bold">${room.name}</h6>
                                <div class="mb-2">
                                    <small class="badge" style="background-color: ${statusStyle.bg}; color: ${statusStyle.text};">
                                        <i class="fas ${statusStyle.icon} me-1"></i>${statusStyle.label}
                                    </small>
                                    ${isBooked ? '<small class="badge bg-danger ms-1">Booked</small>' : ''}
                                    ${room.team_name ? `<small class="badge ms-1" style="background: ${room.team_color}; color: white;">${room.team_name}</small>` : ''}
                                </div>
                                <select class="form-select form-select-sm" style="max-width: 200px;" onchange="assignTeamToRoom(${room.id}, this.value)">
                                    <option value="">No Team</option>
                                    ${teams.map(team => `<option value="${team.id}" ${room.team_id == team.id ? 'selected' : ''} style="background: ${team.color}; color: white;">${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <button 
                                onclick="deleteRoomConfirm(${room.id}, '${room.name}', ${isBooked})" 
                                class="btn btn-sm btn-danger ms-2"
                                ${isBooked ? 'disabled title="Cannot delete booked room"' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-4 text-danger">Failed to load rooms</div>';
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
        container.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading rooms</div>';
    }
}

async function addRoom(event) {
    event.preventDefault();
    
    const roomName = document.getElementById('newRoomName').value.trim();
    if (!roomName) return;
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    
    try {
        const response = await fetch('/api/duty-roster/rooms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: roomName })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('newRoomName').value = '';
            await loadRoomsList();
            await loadHousekeepingStatus();
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-2';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            event.target.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error adding room:', error);
        alert('Error adding room. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function deleteRoomConfirm(roomId, roomName, isBooked) {
    if (isBooked) {
        alert('Cannot delete a booked room');
        return;
    }
    
    if (confirm(`Are you sure you want to delete room "${roomName}"?`)) {
        deleteRoom(roomId);
    }
}

async function deleteRoom(roomId) {
    try {
        const response = await fetch(`/api/duty-roster/rooms/${roomId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadRoomsList();
            await loadHousekeepingStatus();
            
            const container = document.getElementById('roomsListContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show m-3';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            container.insertAdjacentElement('beforebegin', alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error deleting room:', error);
        alert('Error deleting room. Please try again.');
    }
}

// ===== Team Management Functions =====
async function loadTeamsList() {
    const container = document.getElementById('teamsListContainer');
    container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Loading teams...</div>';
    
    try {
        const response = await fetch('/api/duty-roster/teams');
        const data = await response.json();
        
        if (data.success) {
            if (data.teams.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted">No teams found. Add your first team above!</div>';
                return;
            }
            
            let html = '<div class="list-group list-group-flush">';
            
            data.teams.forEach(team => {
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width: 24px; height: 24px; background: ${team.color}; border-radius: 4px; margin-right: 8px;"></div>
                                    <h6 class="mb-0 fw-bold">${team.name}</h6>
                                </div>
                                ${team.notes ? `<small class="text-muted d-block mb-1"><i class="fas fa-info-circle me-1"></i>${team.notes}</small>` : ''}
                                <small class="text-muted">
                                    <i class="fas fa-door-open me-1"></i>${team.rooms_count} room${team.rooms_count !== 1 ? 's' : ''}
                                </small>
                            </div>
                            <button 
                                onclick="deleteTeamConfirm(${team.id}, '${team.name}')" 
                                class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-4 text-danger">Failed to load teams</div>';
        }
    } catch (error) {
        console.error('Error loading teams:', error);
        container.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading teams</div>';
    }
}

async function addTeam(event) {
    event.preventDefault();
    
    const teamName = document.getElementById('newTeamName').value.trim();
    const teamColor = document.querySelector('input[name="teamColor"]:checked')?.value;
    const teamNotes = document.getElementById('newTeamNotes').value.trim();
    
    if (!teamName || !teamColor) {
        alert('Please provide a team name and select a color');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    
    try {
        const response = await fetch('/api/duty-roster/teams', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: teamName,
                color: teamColor,
                notes: teamNotes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('newTeamName').value = '';
            document.getElementById('newTeamNotes').value = '';
            document.querySelectorAll('input[name="teamColor"]').forEach(input => input.checked = false);
            
            await loadTeamsList();
            await loadRoomsList(); // Refresh room list to update team dropdown
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-2';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            event.target.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error adding team:', error);
        alert('Error adding team. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function deleteTeamConfirm(teamId, teamName) {
    if (confirm(`Are you sure you want to delete team "${teamName}"? This will unassign all rooms from this team.`)) {
        deleteTeam(teamId);
    }
}

async function deleteTeam(teamId) {
    try {
        const response = await fetch(`/api/duty-roster/teams/${teamId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadTeamsList();
            await loadRoomsList();
            await loadHousekeepingStatus();
            
            const container = document.getElementById('teamsListContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show m-3';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            container.insertAdjacentElement('beforebegin', alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error deleting team:', error);
        alert('Error deleting team. Please try again.');
    }
}

async function assignTeamToRoom(roomId, teamId) {
    try {
        const response = await fetch(`/api/duty-roster/rooms/${roomId}/assign-team`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ team_id: teamId || null })
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadRoomsList();
            await loadHousekeepingStatus();
        } else {
            alert('Error: ' + data.error);
            await loadRoomsList(); // Reload to reset dropdown
        }
    } catch (error) {
        console.error('Error assigning team:', error);
        alert('Error assigning team. Please try again.');
        await loadRoomsList(); // Reload to reset dropdown
    }
}
</script>
@endsection