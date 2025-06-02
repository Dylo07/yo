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
            <form id="stockUpdateForm" class="mb-4">
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
                    <button type="button" name="action" value="add" class="btn btn-success">Add Stock</button>
                    <button type="button" name="action" value="remove_main_kitchen" class="btn btn-danger">Main Kitchen</button>
                    <button type="button" name="action" value="remove_banquet_hall_kitchen" class="btn btn-danger">Banquet Hall Kitchen</button>
                    <button type="button" name="action" value="remove_banquet_hall" class="btn btn-danger">Banquet Hall</button>
                    <button type="button" name="action" value="remove_restaurant" class="btn btn-danger">Restaurant</button>
                    <button type="button" name="action" value="remove_rooms" class="btn btn-danger">Rooms</button>
                    <button type="button" name="action" value="remove_garden" class="btn btn-danger">Garden</button>
                    <button type="button" name="action" value="remove_other" class="btn btn-danger">Other</button>
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
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label for="usage_date" class="form-label">Select Date</label>
                        <input type="date" id="usage_date" name="usage_date" class="form-control" 
                               value="{{ request('usage_date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-3">
                        <label for="usage_category" class="form-label">Filter by Location</label>
                        <select name="usage_category" id="usage_category" class="form-select">
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
            </div>

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
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label for="log_date" class="form-label">Select Date</label>
                        <input type="date" id="log_date" name="log_date" class="form-control" 
                               value="{{ request('log_date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}">
                    </div>
                </div>
            </div>

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