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
                    <div class="alert alert-dark shadow-sm text-center">
                        <h4 class="mb-0">Total S/C for {{ Carbon\Carbon::parse($selectedMonth)->format('F Y') }}: 
                            <span class="fw-bold">Rs {{ number_format($serviceCharge, 2) }}</span>
                        </h4>
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
                            ['url' => '/staff/attendance', 'title' => 'Staff Attendance', 'icon' => 'attendance'], 
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
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">Inventory Changes</h5>
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                <input type="date"
                        name="inventory_date"
                        class="form-control form-control-sm me-2 dark-input"
                        value="{{ request('inventory_date', date('Y-m-d')) }}">
                <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
                <!-- Preserve other request parameters -->
                @if(request('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                @if(request('period'))
                    <input type="hidden" name="period" value="{{ request('period') }}">
                @endif
                @if(request('month'))
                    <input type="hidden" name="month" value="{{ request('month') }}">
                @endif
            </form>
        </div>
        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-light">
            Manage Inventory
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Time</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Action</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Current Stock</th>
                        <th>Updated By</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryChanges as $change)
                    <tr>
                        <td>{{ $change->created_at->format('H:i') }}</td>
                        <td><strong>{{ $change->item ? $change->item->name : 'Unknown Item' }}</strong></td>
                        <td>{{ ($change->item && $change->item->group) ? $change->item->group->name : 'Unknown Category' }}</td>
                        <td>
                            @if($change->action == 'add')
                                <span class="badge bg-success">
                                    <i class="fas fa-plus"></i> Added
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-minus"></i> Removed
                                </span>
                            @endif
                        </td>
                        <td>
                            @switch($change->action)
                                @case('add')
                                    <span class="badge bg-success">
                                        <i class="fas fa-warehouse"></i> Stock Addition
                                    </span>
                                    @break
                                @case('remove_main_kitchen')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-utensils"></i> Main Kitchen
                                    </span>
                                    @break
                                @case('remove_banquet_hall_kitchen')
                                    <span class="badge bg-info">
                                        <i class="fas fa-birthday-cake"></i> Banquet Hall Kitchen
                                    </span>
                                    @break
                                @case('remove_banquet_hall')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-glass-cheers"></i> Banquet Hall
                                    </span>
                                    @break
                                @case('remove_restaurant')
                                    <span class="badge bg-success">
                                        <i class="fas fa-concierge-bell"></i> Restaurant
                                    </span>
                                    @break
                                @case('remove_rooms')
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-bed"></i> Rooms
                                    </span>
                                    @break
                                @case('remove_garden')
                                    <span class="badge bg-dark">
                                        <i class="fas fa-tree"></i> Garden
                                    </span>
                                    @break
                                @case('remove_other')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-question"></i> Other
                                    </span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst(str_replace(['remove_', '_'], ['', ' '], $change->action)) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="{{ $change->action == 'add' ? 'text-success' : 'text-danger' }}">
                            <strong>
                                @if($change->action == 'add')
                                    +{{ $change->quantity }}
                                @else
                                    -{{ $change->quantity }}
                                @endif
                            </strong>
                        </td>
                        <td class="fw-bold">
                            @if(isset($currentStockLevels[$change->item_id]))
                                <span class="badge bg-light text-dark">
                                    {{ $currentStockLevels[$change->item_id] }}
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $change->user ? $change->user->name : 'Unknown User' }}</td>
                        <td>
                            <small class="text-muted">{{ $change->description }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
                            No inventory changes found for {{ \Carbon\Carbon::parse($inventoryDate)->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Summary Section -->
        @if($inventoryChanges->count() > 0)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-light border">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total Changes:</strong> {{ $inventoryChanges->count() }}
                        </div>
                        <div class="col-md-3">
                            <strong>Items Added:</strong> 
                            <span class="text-success">
                                {{ $inventoryChanges->where('action', 'add')->sum('quantity') }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Items Removed:</strong> 
                            <span class="text-danger">
                                {{ $inventoryChanges->whereIn('action', [
                                    'remove_main_kitchen', 'remove_banquet_hall_kitchen', 'remove_banquet_hall',
                                    'remove_restaurant', 'remove_rooms', 'remove_garden', 'remove_other'
                                ])->sum('quantity') }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Locations Used:</strong> 
                            {{ $inventoryChanges->whereIn('action', [
                                'remove_main_kitchen', 'remove_banquet_hall_kitchen', 'remove_banquet_hall',
                                'remove_restaurant', 'remove_rooms', 'remove_garden', 'remove_other'
                            ])->pluck('action')->unique()->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

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
        <h5 class="mb-0">Pending Tasks</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-outline-light">
            Add Task
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Date Added</th>
                        <th width="30%">Task</th>
                        <th>Category</th>
                        <th>Person Incharge</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->user }}</td>
                        <td>{{ $task->date_added }}</td>
                        <td class="task-cell">{{ $task->task }}</td>
                        <td>{{ $task->taskCategory->name }}</td>
                        <td>{{ $task->person_incharge }}</td>
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
                                    Complete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No pending tasks found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
                        <th class="text-center">Number of Advances</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaryAdvances->groupBy('person.name') as $employeeName => $advances)
                        <tr>
                            <td><strong>{{ $employeeName }}</strong></td>
                            <td class="text-end text-danger fw-bold">Rs. {{ number_format($advances->sum('amount'), 2) }}</td>
                            <td class="text-center">{{ $advances->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No salary advances found for this period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end text-warning"><strong>Rs. {{ number_format($totalAdvance, 2) }}</strong></td>
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
</style>
@endsection