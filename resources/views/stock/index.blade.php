@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Processing...</p>
        </div>
    </div>

    <!-- Card for Navigation -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs border-bottom-0">
                <li class="nav-item">
                    <a href="{{ route('stock.index') }}" 
                       class="nav-link {{ Request::routeIs('stock.index') ? 'active' : '' }}">
                        Stock Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('categories-products.index') }}" 
                       class="nav-link {{ Request::routeIs('categories-products.index') ? 'active' : '' }}">
                        Category & Product Management
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Category Selection Dropdown -->
    <form action="{{ route('stock.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="category_filter" class="form-label">Select Category</label>
                <select name="category_id" id="category_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">Please select a category</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('category_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <input type="hidden" name="year" value="{{ $currentYear }}">
        </div>
    </form>

    <!-- Month Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <form action="{{ route('stock.index') }}" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                
                @php
                    $currentDate = Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                    $previousMonth = $currentDate->copy()->subMonth();
                    $nextMonth = $currentDate->copy()->addMonth();
                @endphp
                
                <div class="col-auto">
                    <div class="btn-group">
                        <a href="{{ route('stock.index', [
                            'month' => $previousMonth->month,
                            'year' => $previousMonth->year,
                            'category_id' => request('category_id')
                        ]) }}" 
                        class="btn btn-outline-primary">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        
                        <button type="button" class="btn btn-outline-primary" disabled>
                            {{ $currentDate->format('F Y') }}
                        </button>

                        @if($nextMonth->lte(now()))
                        <a href="{{ route('stock.index', [
                            'month' => $nextMonth->month,
                            'year' => $nextMonth->year,
                            'category_id' => request('category_id')
                        ]) }}" 
                        class="btn btn-outline-primary">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="col-auto">
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            @php
                                $monthDate = Carbon\Carbon::createFromDate($currentYear, $m, 1);
                                $isDisabled = $monthDate->isAfter(now());
                            @endphp
                            <option value="{{ $m }}" 
                                    {{ $currentMonth == $m ? 'selected' : '' }}
                                    {{ $isDisabled ? 'disabled' : '' }}>
                                {{ $monthDate->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @foreach(range(now()->subYears(2)->year, now()->year) as $y)
                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" name="month" value="{{ now()->month }}" class="btn btn-secondary">
                        Current Month
                    </button>
                    <button type="submit" name="month" value="{{ now()->subMonth()->month }}" 
                            class="btn btn-outline-secondary"
                            onclick="this.form.year.value='{{ now()->subMonth()->year }}'">
                        Last Month
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedGroup)
        <!-- Stock Table for Selected Category -->
        <h4>{{ $selectedGroup->name }}</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        @for($i = 1; $i <= 31; $i++)
                            <th>{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedGroup->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            @for($i = 1; $i <= 31; $i++)
                                @php
                                    $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
                                    $inventory = $item->inventory->firstWhere('stock_date', $date);
                                    
                                    if ($inventory) {
                                        $displayStock = $inventory->stock_level;
                                    } else {
                                        $previousInventory = $item->inventory
                                            ->where('stock_date', '<', $date)
                                            ->sortByDesc('stock_date')
                                            ->first();
                                        
                                        if ($i === 1 && !$inventory && $previousInventory) {
                                            $displayStock = $previousInventory->stock_level;
                                        } else {
                                            $displayStock = $previousInventory ? $previousInventory->stock_level : '-';
                                        }
                                    }
                                    
                                    $stockLogs = $monthLogs->where('item_id', $item->id)
                                        ->filter(function($log) use ($date) {
                                            return $log->created_at->format('Y-m-d') === $date;
                                        });
                                    $additions = $stockLogs->where('action', 'add')->sum('quantity');
                                    $removals = $stockLogs->whereIn('action', ['remove_main_kitchen', 'remove_banquet_hall_kitchen', 'remove_banquet_hall', 'remove_restaurant', 'remove_rooms', 'remove_garden', 'remove_other'])->sum('quantity');
                                @endphp
                                <td>
                                    @if($date <= now()->toDateString())
                                        <div>{{ $displayStock }}</div>
                                        @if($additions)
                                            <div class="text-success">+{{ $additions }}</div>
                                        @endif
                                        @if($removals)
                                            <div class="text-danger">-{{ $removals }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Stock Update Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Update Stock</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('stock.update') }}" method="POST" class="mb-4" onsubmit="showLoading()">
                    @csrf
                    <div class="mb-3">
                        <label for="item" class="form-label">Item</label>
                        <select name="item_id" id="item" class="form-select" required>
                            <option value="">Select an item</option>
                            @foreach($selectedGroup->items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" 
                               step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" name="description" id="description" class="form-control" 
                               placeholder="Enter a description" required>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="add" class="btn btn-success">Add Stock</button>
                        <button type="submit" name="action" value="remove_main_kitchen" class="btn btn-danger" onclick="return confirmRemoval('Main Kitchen')">Main Kitchen</button>
                        <button type="submit" name="action" value="remove_banquet_hall_kitchen" class="btn btn-danger" onclick="return confirmRemoval('Banquet Hall Kitchen')">Banquet Hall Kitchen</button>
                        <button type="submit" name="action" value="remove_banquet_hall" class="btn btn-danger" onclick="return confirmRemoval('Banquet Hall')">Banquet Hall</button>
                        <button type="submit" name="action" value="remove_restaurant" class="btn btn-danger" onclick="return confirmRemoval('Restaurant')">Restaurant</button>
                        <button type="submit" name="action" value="remove_rooms" class="btn btn-danger" onclick="return confirmRemoval('Rooms')">Rooms</button>
                        <button type="submit" name="action" value="remove_garden" class="btn btn-danger" onclick="return confirmRemoval('Garden')">Garden</button>
                        <button type="submit" name="action" value="remove_other" class="btn btn-danger" onclick="return confirmRemoval('Other')">Other</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category-wise Usage Report Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Category-wise Usage Report</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('stock.index') }}" method="GET" class="mb-3">
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    <input type="hidden" name="month" value="{{ $currentMonth }}">
                    <input type="hidden" name="year" value="{{ $currentYear }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="usage_date" class="form-label">Select Date</label>
                            <input type="date" id="usage_date" name="usage_date" class="form-control" 
                                   value="{{ request('usage_date', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}"
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label for="usage_category" class="form-label">Filter by Location</label>
                            <select name="usage_category" id="usage_category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Locations</option>
                                <option value="remove_main_kitchen" {{ request('usage_category') == 'remove_main_kitchen' ? 'selected' : '' }}>Main Kitchen</option>
                                <option value="remove_banquet_hall_kitchen" {{ request('usage_category') == 'remove_banquet_hall_kitchen' ? 'selected' : '' }}>Banquet Hall Kitchen</option>
                                <option value="remove_banquet_hall" {{ request('usage_category') == 'remove_banquet_hall' ? 'selected' : '' }}>Banquet Hall</option>
                                <option value="remove_restaurant" {{ request('usage_category') == 'remove_restaurant' ? 'selected' : '' }}>Restaurant</option>
                                <option value="remove_rooms" {{ request('usage_category') == 'remove_rooms' ? 'selected' : '' }}>Rooms</option>
                                <option value="remove_garden" {{ request('usage_category') == 'remove_garden' ? 'selected' : '' }}>Garden</option>
                                <option value="remove_other" {{ request('usage_category') == 'remove_other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Usage Summary by Category -->
                @if(isset($usageSummary) && $usageSummary->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <h5>Usage Summary for {{ request('usage_date', now()->toDateString()) }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Location</th>
                                        <th>Total Items Used</th>
                                        <th>Total Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usageSummary as $summary)
                                        <tr>
                                            <td>
                                                @switch($summary->action)
                                                    @case('remove_main_kitchen')
                                                        <span class="badge bg-primary">Main Kitchen</span>
                                                        @break
                                                    @case('remove_banquet_hall_kitchen')
                                                        <span class="badge bg-info">Banquet Hall Kitchen</span>
                                                        @break
                                                    @case('remove_banquet_hall')
                                                        <span class="badge bg-warning">Banquet Hall</span>
                                                        @break
                                                    @case('remove_restaurant')
                                                        <span class="badge bg-success">Restaurant</span>
                                                        @break
                                                    @case('remove_rooms')
                                                        <span class="badge bg-secondary">Rooms</span>
                                                        @break
                                                    @case('remove_garden')
                                                        <span class="badge bg-dark">Garden</span>
                                                        @break
                                                    @case('remove_other')
                                                        <span class="badge bg-danger">Other</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ $summary->item_count }}</td>
                                            <td>{{ number_format($summary->total_quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Detailed Usage Log -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Item</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usageLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('H:i') }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>{{ $log->item->name }}</td>
                                    <td>
                                        @switch($log->action)
                                            @case('add')
                                                <span class="badge bg-success">Stock Added</span>
                                                @break
                                            @case('remove_main_kitchen')
                                                <span class="badge bg-primary">Main Kitchen</span>
                                                @break
                                            @case('remove_banquet_hall_kitchen')
                                                <span class="badge bg-info">Banquet Hall Kitchen</span>
                                                @break
                                            @case('remove_banquet_hall')
                                                <span class="badge bg-warning">Banquet Hall</span>
                                                @break
                                            @case('remove_restaurant')
                                                <span class="badge bg-success">Restaurant</span>
                                                @break
                                            @case('remove_rooms')
                                                <span class="badge bg-secondary">Rooms</span>
                                                @break
                                            @case('remove_garden')
                                                <span class="badge bg-dark">Garden</span>
                                                @break
                                            @case('remove_other')
                                                <span class="badge bg-danger">Other</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ ucfirst($log->action) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $log->quantity }}</td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No stock movements on this date</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($usageLogs) && $usageLogs->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $usageLogs->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Stock Log Details Section (Original) -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">All Stock Activities</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('stock.index') }}" method="GET" class="mb-3">
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    <input type="hidden" name="month" value="{{ $currentMonth }}">
                    <input type="hidden" name="year" value="{{ $currentYear }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="log_date" class="form-label">Select Date</label>
                            <input type="date" id="log_date" name="log_date" class="form-control" 
                                   value="{{ request('log_date', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}"
                                   onchange="this.form.submit()">
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Item</th>
                                <th>Action</th>
                                <th>Quantity</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('H:i') }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>{{ $log->item->name }}</td>
                                    <td>
                                        @if($log->action == 'add')
                                            <span class="badge bg-success">Add</span>
                                        @else
                                            <span class="badge bg-danger">{{ str_replace('remove_', '', ucwords(str_replace('_', ' ', $log->action))) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->quantity }}</td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No stock movements on this date</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($logs) && $logs->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $logs->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Please select a category to view stock details
        </div>
    @endif
</div>

<style>
td {
    padding: 8px !important;
    font-size: 0.9em;
}
td div {
    line-height: 1.2;
}
.text-success {
    color: #28a745 !important;
    font-weight: bold;
}
.text-danger {
    color: #dc3545 !important;
    font-weight: bold;
}
.nav-tabs {
    border-bottom: none;
    padding: 0.5rem 1rem 0;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 0.75rem 1rem;
    margin-right: 0.5rem;
}

.nav-tabs .nav-link:hover {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}

.card {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-danger {
    margin-bottom: 5px;
}

.d-flex.gap-2.flex-wrap .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}
</style>

<script>
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
    return true;
}

function confirmRemoval(location) {
    const quantity = document.getElementById('quantity').value;
    const itemSelect = document.getElementById('item');
    const itemText = itemSelect.options[itemSelect.selectedIndex].text;
    
    if (!quantity || quantity <= 0) {
        alert('Please enter a valid quantity first.');
        document.getElementById('quantity').focus();
        return false;
    }
    
    if (!itemSelect.value) {
        alert('Please select an item first.');
        itemSelect.focus();
        return false;
    }
    
    const description = document.getElementById('description').value.trim();
    if (!description) {
        alert('Please enter a description.');
        document.getElementById('description').focus();
        return false;
    }
    
    return confirm(`Remove ${quantity} units of "${itemText}" for ${location}?`);
}

// Auto-focus functionality
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item');
    const quantityInput = document.getElementById('quantity');
    const descriptionInput = document.getElementById('description');
    
    if (itemSelect && quantityInput) {
        itemSelect.addEventListener('change', function() {
            if (this.value) {
                quantityInput.focus();
            }
        });
    }

    if (quantityInput && descriptionInput) {
        quantityInput.addEventListener('blur', function() {
            if (this.value && this.value > 0) {
                descriptionInput.focus();
            }
        });
    }
});
</script>
@endsection