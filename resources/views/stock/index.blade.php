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
            <ul class="nav nav-tabs border-bottom-0" id="stockPageTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stockPane" type="button" role="tab">
                        Stock Management
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-info fw-bold" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboardPane" type="button" role="tab">
                        <i class="fas fa-chart-line me-1"></i>Monthly Stock Movement Dashboard
                    </button>
                </li>
                <li class="nav-item">
                    <a href="{{ route('categories-products.index') }}" class="nav-link">
                        Category & Product Management
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="tab-content" id="stockPageTabContent">
    <!-- Stock Management Tab -->
    <div class="tab-pane fade show active" id="stockPane" role="tabpanel">

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
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 200px; position: sticky; left: 0; background: #f8f9fa; z-index: 20;">Item</th>
                        @for($i = 1; $i <= 31; $i++)
                            <th class="text-center" style="min-width: 60px;">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedGroup->items as $item)
                        @php
                            $lastKnownStock = '-';
                            $prevDateObj = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->subDay();
                            $prevDateString = $prevDateObj->toDateString();
                            if (isset($inventoryData[$item->id][$prevDateString])) {
                                $lastKnownStock = $inventoryData[$item->id][$prevDateString];
                            }
                        @endphp
                        <tr>
                            <td style="position: sticky; left: 0; background: white; z-index: 10; border-right: 2px solid #dee2e6;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $item->name }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2 rounded-circle" 
                                            onclick="showItemTrend({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                            title="View Trend Graph"
                                            style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                </div>
                            </td>
                            @for($i = 1; $i <= 31; $i++)
                                @php
                                    $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
                                    $isFuture = $date > now()->toDateString();
                                    if (isset($inventoryData[$item->id][$date])) {
                                        $displayStock = $inventoryData[$item->id][$date];
                                        $lastKnownStock = $displayStock;
                                    } else {
                                        $displayStock = $isFuture ? '-' : $lastKnownStock;
                                    }
                                    $logData = $logsGrouped[$item->id][$date] ?? ['add' => 0, 'remove' => 0];
                                    $additions = $logData['add'];
                                    $removals = $logData['remove'];
                                @endphp
                                <td class="text-center p-1">
                                    @if(!$isFuture)
                                        <div class="fw-bold" style="font-size: 0.95rem;">{{ $displayStock }}</div>
                                        @if($additions > 0)
                                            <div class="text-success small" style="font-size: 0.75rem; line-height: 1;">+{{ $additions }}</div>
                                        @endif
                                        @if($removals > 0)
                                            <div class="text-danger small" style="font-size: 0.75rem; line-height: 1;">-{{ $removals }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
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
                        <label for="item_stock" class="form-label">Item</label>
                        <select name="item_id" id="item_stock" class="form-select" required>
                            <option value="">Select an item</option>
                            @foreach($selectedGroup->items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_stock" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity_stock" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description_stock" class="form-label">Description</label>
                        <input type="text" name="description" id="description_stock" class="form-control" placeholder="Enter a description" required>
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
    @else
        <div class="alert alert-info">Please select a category to view stock details and update stock</div>
    @endif
    </div><!-- End Stock Management Tab -->

    <!-- Dashboard Tab -->
    <div class="tab-pane fade" id="dashboardPane" role="tabpanel">
    <!-- Monthly Stock Movement Dashboard -->
    @if(isset($demandData) && count($demandData) > 0)
    <div class="card mt-4 stock-dashboard">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                <div>
                    <h3 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Stock Movement Dashboard</h3>
                    <small class="opacity-75">
                        @if($demandLimit == 'all' || $demandLimit == 0)
                            All Items
                        @else
                            Top {{ $demandLimit }} Most Active Items
                        @endif
                        @if($dashboardCategory)
                            - {{ $groups->find($dashboardCategory)->name ?? 'Selected Category' }}
                        @else
                            - All Categories
                        @endif
                    </small>
                </div>
                <div class="dashboard-stats d-flex gap-2">
                    @php
                        $totalIn = array_sum(array_column($demandData, 'additions'));
                        $totalOut = array_sum(array_column($demandData, 'removals'));
                        $netFlow = $totalIn - $totalOut;
                    @endphp
                    <div class="stat-badge bg-success-subtle text-success rounded px-3 py-2">
                        <small>Total In</small>
                        <div class="fw-bold">+{{ number_format($totalIn, 1) }}</div>
                    </div>
                    <div class="stat-badge bg-danger-subtle text-danger rounded px-3 py-2">
                        <small>Total Out</small>
                        <div class="fw-bold">-{{ number_format($totalOut, 1) }}</div>
                    </div>
                    <div class="stat-badge {{ $netFlow >= 0 ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} rounded px-3 py-2">
                        <small>Net Flow</small>
                        <div class="fw-bold">{{ $netFlow >= 0 ? '+' : '' }}{{ number_format($netFlow, 1) }}</div>
                    </div>
                </div>
            </div>
            <!-- Dashboard Filters -->
            <form action="{{ route('stock.index') }}" method="GET" class="row g-2 align-items-end mt-2">
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <input type="hidden" name="year" value="{{ $currentYear }}">
                <div class="col-auto">
                    <label class="form-label text-white small mb-1">Start Date</label>
                    <input type="date" name="dashboard_start" class="form-control form-control-sm" value="{{ $dashboardStartDate }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <label class="form-label text-white small mb-1">End Date</label>
                    <input type="date" name="dashboard_end" class="form-control form-control-sm" value="{{ $dashboardEndDate }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <label class="form-label text-white small mb-1">Category</label>
                    <select name="dashboard_category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ $dashboardCategory == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label text-white small mb-1">Show</label>
                    <select name="demand_limit" class="form-select form-select-sm">
                        <option value="10" {{ $demandLimit == 10 ? 'selected' : '' }}>Top 10</option>
                        <option value="25" {{ $demandLimit == 25 ? 'selected' : '' }}>Top 25</option>
                        <option value="50" {{ $demandLimit == 50 ? 'selected' : '' }}>Top 50</option>
                        <option value="all" {{ $demandLimit == 'all' || $demandLimit == 0 ? 'selected' : '' }}>All Items</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-filter me-1"></i>Apply</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('stock.index', ['category_id' => request('category_id'), 'month' => $currentMonth, 'year' => $currentYear]) }}" class="btn btn-outline-light btn-sm"><i class="fas fa-undo me-1"></i>Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <!-- Date Range Display -->
            <div class="alert alert-light border mb-3 py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <span>
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <strong>Period:</strong> {{ \Carbon\Carbon::parse($dashboardStartDate)->format('M d, Y') }} 
                        - {{ \Carbon\Carbon::parse($dashboardEndDate)->format('M d, Y') }}
                        <span class="text-muted ms-2">({{ \Carbon\Carbon::parse($dashboardStartDate)->diffInDays(\Carbon\Carbon::parse($dashboardEndDate)) + 1 }} days)</span>
                    </span>
                    @if($dashboardCategory)
                        <span class="badge bg-primary">{{ $groups->find($dashboardCategory)->name ?? 'Category' }}</span>
                    @else
                        <span class="badge bg-secondary">All Categories</span>
                    @endif
                </div>
            </div>
            
            <div class="row">
                <!-- Main Chart -->
                <div class="col-lg-8">
                    <div class="chart-container" style="height: 400px; position: relative;">
                        <canvas id="monthlyDemandChart"></canvas>
                    </div>
                </div>
                <!-- Location Breakdown Pie Chart -->
                <div class="col-lg-4">
                    <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt me-1"></i>Usage by Location</h6>
                    <div style="height: 250px; position: relative;">
                        <canvas id="locationBreakdownChart"></canvas>
                    </div>
                    <div class="location-legend mt-3">
                        @php
                            $locationTotals = [
                                'Main Kitchen' => 0,
                                'Banquet Kitchen' => 0,
                                'Banquet Hall' => 0,
                                'Restaurant' => 0,
                                'Rooms' => 0,
                                'Garden' => 0,
                                'Other' => 0
                            ];
                            foreach ($demandData as $item) {
                                $locationTotals['Main Kitchen'] += $item['locationBreakdown']['main_kitchen'];
                                $locationTotals['Banquet Kitchen'] += $item['locationBreakdown']['banquet_hall_kitchen'];
                                $locationTotals['Banquet Hall'] += $item['locationBreakdown']['banquet_hall'];
                                $locationTotals['Restaurant'] += $item['locationBreakdown']['restaurant'];
                                $locationTotals['Rooms'] += $item['locationBreakdown']['rooms'];
                                $locationTotals['Garden'] += $item['locationBreakdown']['garden'];
                                $locationTotals['Other'] += $item['locationBreakdown']['other'];
                            }
                            $grandTotal = array_sum($locationTotals);
                        @endphp
                    </div>
                </div>
            </div>

            <!-- Detailed Items Table -->
            <div class="mt-4">
                <h6 class="text-muted mb-3"><i class="fas fa-list-alt me-1"></i>Detailed Item Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Added</th>
                                <th class="text-center">Removed</th>
                                <th class="text-center">Net Change</th>
                                <th>Top Usage Location</th>
                                <th class="text-center">Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandData as $item)
                            @php
                                $maxLocation = '';
                                $maxValue = 0;
                                $locationNames = [
                                    'main_kitchen' => 'Main Kitchen',
                                    'banquet_hall_kitchen' => 'Banquet Kitchen',
                                    'banquet_hall' => 'Banquet Hall',
                                    'restaurant' => 'Restaurant',
                                    'rooms' => 'Rooms',
                                    'garden' => 'Garden',
                                    'other' => 'Other'
                                ];
                                foreach ($item['locationBreakdown'] as $loc => $val) {
                                    if ($val > $maxValue) {
                                        $maxValue = $val;
                                        $maxLocation = $locationNames[$loc];
                                    }
                                }
                                $activityPercent = $grandTotal > 0 ? ($item['removals'] / $grandTotal) * 100 : 0;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                    <small class="text-muted d-block">{{ $item['unit'] }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success">+{{ number_format($item['additions'], 1) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger">-{{ number_format($item['removals'], 1) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item['netChange'] >= 0)
                                        <span class="text-success fw-bold"><i class="fas fa-arrow-up"></i> {{ number_format($item['netChange'], 1) }}</span>
                                    @else
                                        <span class="text-danger fw-bold"><i class="fas fa-arrow-down"></i> {{ number_format(abs($item['netChange']), 1) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($maxLocation)
                                        <span class="badge bg-primary-subtle text-primary">{{ $maxLocation }}</span>
                                        <small class="text-muted">({{ number_format($maxValue, 1) }})</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center" style="width: 120px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ min($activityPercent * 3, 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($activityPercent, 1) }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Cost Analysis Dashboard -->
    @if(isset($demandData) && count($demandData) > 0)
    @php
        $itemsWithCost = array_filter($demandData, function($item) {
            return ($item['cost_per_unit'] ?? 0) > 0;
        });
        $totalCostIn = array_sum(array_map(function($item) { return $item['cost_additions'] ?? 0; }, $demandData));
        $totalCostOut = array_sum(array_map(function($item) { return $item['cost_removals'] ?? 0; }, $demandData));
        $netCostFlow = $totalCostIn - $totalCostOut;
        $itemsWithoutCost = count($demandData) - count($itemsWithCost);
    @endphp
    @if(count($itemsWithCost) > 0)
    <div class="card mt-4 cost-dashboard">
        <div class="card-header bg-gradient-warning text-dark">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                <div>
                    <h3 class="card-title mb-0"><i class="fas fa-rupee-sign me-2"></i>Cost Analysis Dashboard</h3>
                    <small class="opacity-75">
                        Financial overview based on item costs
                        @if($itemsWithoutCost > 0)
                            <span class="badge bg-secondary ms-2">{{ $itemsWithoutCost }} items without price</span>
                        @endif
                    </small>
                </div>
                <div class="dashboard-stats d-flex gap-2">
                    <div class="stat-badge bg-success-subtle text-success rounded px-3 py-2">
                        <small>Cost In</small>
                        <div class="fw-bold">Rs {{ number_format($totalCostIn, 2) }}</div>
                    </div>
                    <div class="stat-badge bg-danger-subtle text-danger rounded px-3 py-2">
                        <small>Cost Out</small>
                        <div class="fw-bold">Rs {{ number_format($totalCostOut, 2) }}</div>
                    </div>
                    <div class="stat-badge {{ $netCostFlow >= 0 ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} rounded px-3 py-2">
                        <small>Net Cost</small>
                        <div class="fw-bold">{{ $netCostFlow >= 0 ? '+' : '' }}Rs {{ number_format($netCostFlow, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Cost Summary by Location -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h6 class="text-muted mb-3"><i class="fas fa-chart-bar me-1"></i>Cost by Location (Usage)</h6>
                    <div class="chart-container" style="height: 300px; position: relative;">
                        <canvas id="costByLocationChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-muted mb-3"><i class="fas fa-chart-pie me-1"></i>Cost Distribution</h6>
                    <div style="height: 250px; position: relative;">
                        <canvas id="costDistributionChart"></canvas>
                    </div>
                    @php
                        $locationCosts = [
                            'Main Kitchen' => 0,
                            'Banquet Kitchen' => 0,
                            'Banquet Hall' => 0,
                            'Restaurant' => 0,
                            'Rooms' => 0,
                            'Garden' => 0,
                            'Other' => 0
                        ];
                        foreach ($demandData as $item) {
                            $cost = $item['cost_per_unit'] ?? 0;
                            $lb = $item['locationBreakdown'] ?? [];
                            $locationCosts['Main Kitchen'] += ($lb['main_kitchen'] ?? 0) * $cost;
                            $locationCosts['Banquet Kitchen'] += ($lb['banquet_hall_kitchen'] ?? 0) * $cost;
                            $locationCosts['Banquet Hall'] += ($lb['banquet_hall'] ?? 0) * $cost;
                            $locationCosts['Restaurant'] += ($lb['restaurant'] ?? 0) * $cost;
                            $locationCosts['Rooms'] += ($lb['rooms'] ?? 0) * $cost;
                            $locationCosts['Garden'] += ($lb['garden'] ?? 0) * $cost;
                            $locationCosts['Other'] += ($lb['other'] ?? 0) * $cost;
                        }
                        $totalLocationCost = array_sum($locationCosts);
                    @endphp
                    <div class="mt-3">
                        @foreach($locationCosts as $location => $cost)
                            @if($cost > 0)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>{{ $location }}</small>
                                <span class="badge bg-primary-subtle text-primary">Rs {{ number_format($cost, 2) }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Top Cost Items Table -->
            <div class="mt-4">
                <h6 class="text-muted mb-3"><i class="fas fa-money-bill-wave me-1"></i>Top Items by Cost (Usage)</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-center">Qty Added</th>
                                <th class="text-center">Qty Used</th>
                                <th class="text-end">Cost Added</th>
                                <th class="text-end">Cost Used</th>
                                <th class="text-end">Net Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sortedByCost = $demandData;
                                usort($sortedByCost, function($a, $b) {
                                    return ($b['cost_removals'] ?? 0) <=> ($a['cost_removals'] ?? 0);
                                });
                                $topCostItems = array_slice($sortedByCost, 0, 15);
                            @endphp
                            @foreach($topCostItems as $item)
                                @if(($item['cost_per_unit'] ?? 0) > 0)
                                <tr>
                                    <td>
                                        <strong>{{ $item['name'] ?? 'Unknown' }}</strong>
                                        <small class="text-muted d-block">{{ $item['category'] ?? '' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted">Rs {{ number_format($item['cost_per_unit'] ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success">{{ number_format($item['additions'] ?? 0, 1) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-subtle text-danger">{{ number_format($item['removals'] ?? 0, 1) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success">Rs {{ number_format($item['cost_additions'] ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger">Rs {{ number_format($item['cost_removals'] ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @php $netCost = ($item['cost_net'] ?? 0); @endphp
                                        <span class="fw-bold {{ $netCost >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netCost >= 0 ? '+' : '' }}Rs {{ number_format($netCost, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Totals:</td>
                                <td class="text-end text-success">Rs {{ number_format($totalCostIn, 2) }}</td>
                                <td class="text-end text-danger">Rs {{ number_format($totalCostOut, 2) }}</td>
                                <td class="text-end {{ $netCostFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $netCostFlow >= 0 ? '+' : '' }}Rs {{ number_format($netCostFlow, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($itemsWithoutCost > 0)
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ $itemsWithoutCost }} items</strong> do not have a cost assigned. 
                Visit <a href="{{ route('kitchen.inventory') }}" class="alert-link">/kitchen/inventory</a> to assign costs.
            </div>
            @endif
        </div>
    </div>
    @endif
    @endif

    @if($selectedGroup)
        <!-- Stock Table for Selected Category -->
        <h4>{{ $selectedGroup->name }}</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 200px; position: sticky; left: 0; background: #f8f9fa; z-index: 20;">Item</th>
                        @for($i = 1; $i <= 31; $i++)
                            <th class="text-center" style="min-width: 60px;">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedGroup->items as $item)
                        @php
                            // Determine initial last known stock from previous month if available
                            $lastKnownStock = '-';
                            $prevMonthDate = sprintf('%04d-%02d-%02d', 
                                $currentMonth == 1 ? $currentYear - 1 : $currentYear, 
                                $currentMonth == 1 ? 12 : $currentMonth - 1, 
                                \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->subDay()->day
                            );
                            
                            // Check if we have the previous month's data in our pre-loaded set
                            // (The controller loads current month + last day of prev month)
                            // We need to construct the date string correctly for the check
                            $prevDateObj = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->subDay();
                            $prevDateString = $prevDateObj->toDateString();
                            
                            if (isset($inventoryData[$item->id][$prevDateString])) {
                                $lastKnownStock = $inventoryData[$item->id][$prevDateString];
                            }
                        @endphp
                        <tr>
                            <td style="position: sticky; left: 0; background: white; z-index: 10; border-right: 2px solid #dee2e6;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $item->name }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2 rounded-circle" 
                                            onclick="showItemTrend({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                            title="View Trend Graph"
                                            style="width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                </div>
                            </td>
                            @for($i = 1; $i <= 31; $i++)
                                @php
                                    $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
                                    $isFuture = $date > now()->toDateString();
                                    
                                    // 1. Determine Stock Level (O(1) Lookup)
                                    if (isset($inventoryData[$item->id][$date])) {
                                        $displayStock = $inventoryData[$item->id][$date];
                                        $lastKnownStock = $displayStock; // Update tracker
                                    } else {
                                        // Use carried over value if not in future
                                        $displayStock = $isFuture ? '-' : $lastKnownStock;
                                    }
                                    
                                    // 2. Determine Logs (O(1) Lookup)
                                    $logData = $logsGrouped[$item->id][$date] ?? ['add' => 0, 'remove' => 0];
                                    $additions = $logData['add'];
                                    $removals = $logData['remove'];
                                @endphp
                                <td class="text-center p-1">
                                    @if(!$isFuture)
                                        <div class="fw-bold" style="font-size: 0.95rem;">{{ $displayStock }}</div>
                                        @if($additions > 0)
                                            <div class="text-success small" style="font-size: 0.75rem; line-height: 1;">+{{ $additions }}</div>
                                        @endif
                                        @if($removals > 0)
                                            <div class="text-danger small" style="font-size: 0.75rem; line-height: 1;">-{{ $removals }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
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
    </div><!-- End Dashboard Tab -->
    </div><!-- End Tab Content -->
</div>

<!-- Trend Graph Modal -->
<div class="modal fade" id="trendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trendModalTitle">Item Trend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="chartLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading chart data...</p>
                </div>
                <div id="chartContainer" style="display: none; height: 400px;">
                    <canvas id="itemTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
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

/* Stock Dashboard Styles */
.stock-dashboard .card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border-bottom: none;
}

.stock-dashboard .stat-badge {
    min-width: 90px;
    text-align: center;
    backdrop-filter: blur(10px);
}

.stock-dashboard .stat-badge small {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-dashboard .stat-badge .fw-bold {
    font-size: 1.1rem;
}

.bg-success-subtle {
    background-color: rgba(40, 167, 69, 0.15) !important;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.15) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.15) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.15) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.15) !important;
}

.stock-dashboard .table th {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.stock-dashboard .table td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

.stock-dashboard .badge {
    font-weight: 500;
    padding: 0.4em 0.8em;
}

.stock-dashboard .progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.stock-dashboard .progress-bar {
    border-radius: 10px;
}

.chart-container {
    background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
    border-radius: 8px;
    padding: 15px;
}

@media (max-width: 991px) {
    .stock-dashboard .dashboard-stats {
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .stock-dashboard .stat-badge {
        flex: 1;
        min-width: 80px;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let trendChart = null;

function showItemTrend(itemId, itemName) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('trendModal'));
    modal.show();
    
    // Update title
    document.getElementById('trendModalTitle').innerText = 'Stock Trend: ' + itemName;
    
    // Reset view
    document.getElementById('chartLoading').style.display = 'block';
    document.getElementById('chartContainer').style.display = 'none';
    
    // Destroy previous chart if exists
    if (trendChart) {
        trendChart.destroy();
    }
    
    // Fetch data
    const month = '{{ $currentMonth }}';
    const year = '{{ $currentYear }}';
    
    fetch(`/stock/item-history/${itemId}?month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('chartLoading').style.display = 'none';
            document.getElementById('chartContainer').style.display = 'block';
            
            const ctx = document.getElementById('itemTrendChart').getContext('2d');
            
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Stock Level',
                            data: data.stockLevels,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderWidth: 2,
                            yAxisID: 'y',
                            fill: true,
                            tension: 0.1
                        },
                        {
                            label: 'Daily Usage',
                            data: data.dailyUsage,
                            borderColor: '#dc3545',
                            backgroundColor: '#dc3545',
                            type: 'bar',
                            yAxisID: 'y1',
                            barThickness: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Stock Level'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Usage'
                            },
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching trend data:', error);
            document.getElementById('chartLoading').innerHTML = '<p class="text-danger">Error loading data</p>';
        });
}

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

    // Initialize Monthly Demand Chart if data exists
    @if(isset($demandData) && count($demandData) > 0)
        // Main Bar Chart - Stock Added vs Removed
        const demandCtx = document.getElementById('monthlyDemandChart');
        if (demandCtx) {
            const demandData = {!! json_encode($demandData) !!};
            
            new Chart(demandCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: demandData.map(item => item.name),
                    datasets: [
                        {
                            label: 'Stock Added',
                            data: demandData.map(item => item.additions),
                            backgroundColor: 'rgba(40, 167, 69, 0.85)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2,
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Stock Removed',
                            data: demandData.map(item => item.removals),
                            backgroundColor: 'rgba(220, 53, 69, 0.85)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 2,
                            borderRadius: 4,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { size: 12, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                afterBody: function(context) {
                                    const idx = context[0].dataIndex;
                                    const item = demandData[idx];
                                    const net = item.netChange;
                                    return [
                                        '',
                                        'Net Change: ' + (net >= 0 ? '+' : '') + net.toFixed(1),
                                        'Unit: ' + item.unit
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            title: {
                                display: true,
                                text: 'Quantity',
                                font: { size: 12, weight: 'bold' }
                            },
                            ticks: { font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        // Location Breakdown Doughnut Chart
        const locationCtx = document.getElementById('locationBreakdownChart');
        if (locationCtx) {
            const locationData = {
                'Main Kitchen': 0,
                'Banquet Kitchen': 0,
                'Banquet Hall': 0,
                'Restaurant': 0,
                'Rooms': 0,
                'Garden': 0,
                'Other': 0
            };
            
            const demandItems = {!! json_encode($demandData) !!};
            demandItems.forEach(item => {
                locationData['Main Kitchen'] += item.locationBreakdown.main_kitchen;
                locationData['Banquet Kitchen'] += item.locationBreakdown.banquet_hall_kitchen;
                locationData['Banquet Hall'] += item.locationBreakdown.banquet_hall;
                locationData['Restaurant'] += item.locationBreakdown.restaurant;
                locationData['Rooms'] += item.locationBreakdown.rooms;
                locationData['Garden'] += item.locationBreakdown.garden;
                locationData['Other'] += item.locationBreakdown.other;
            });

            // Filter out zero values
            const filteredLabels = [];
            const filteredData = [];
            const colors = {
                'Main Kitchen': '#0d6efd',
                'Banquet Kitchen': '#6610f2',
                'Banquet Hall': '#6f42c1',
                'Restaurant': '#20c997',
                'Rooms': '#fd7e14',
                'Garden': '#198754',
                'Other': '#6c757d'
            };
            const filteredColors = [];

            Object.entries(locationData).forEach(([label, value]) => {
                if (value > 0) {
                    filteredLabels.push(label);
                    filteredData.push(value);
                    filteredColors.push(colors[label]);
                }
            });

            new Chart(locationCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: filteredLabels,
                    datasets: [{
                        data: filteredData,
                        backgroundColor: filteredColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 12,
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toFixed(1) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Cost Analysis Charts
        const costLocationCtx = document.getElementById('costByLocationChart');
        const costDistCtx = document.getElementById('costDistributionChart');
        
        if (costLocationCtx) {
            const demandItems = {!! json_encode($demandData) !!};
            const locationCostData = {
                'Main Kitchen': 0,
                'Banquet Kitchen': 0,
                'Banquet Hall': 0,
                'Restaurant': 0,
                'Rooms': 0,
                'Garden': 0,
                'Other': 0
            };
            
            demandItems.forEach(item => {
                const cost = item.cost_per_unit || 0;
                const lb = item.locationBreakdown || {};
                locationCostData['Main Kitchen'] += (lb.main_kitchen || 0) * cost;
                locationCostData['Banquet Kitchen'] += (lb.banquet_hall_kitchen || 0) * cost;
                locationCostData['Banquet Hall'] += (lb.banquet_hall || 0) * cost;
                locationCostData['Restaurant'] += (lb.restaurant || 0) * cost;
                locationCostData['Rooms'] += (lb.rooms || 0) * cost;
                locationCostData['Garden'] += (lb.garden || 0) * cost;
                locationCostData['Other'] += (lb.other || 0) * cost;
            });

            const costLabels = Object.keys(locationCostData).filter(k => locationCostData[k] > 0);
            const costValues = costLabels.map(k => locationCostData[k]);
            const costColors = ['#0d6efd', '#6610f2', '#6f42c1', '#20c997', '#fd7e14', '#198754', '#6c757d'];

            new Chart(costLocationCtx, {
                type: 'bar',
                data: {
                    labels: costLabels,
                    datasets: [{
                        label: 'Cost (Rs)',
                        data: costValues,
                        backgroundColor: costColors.slice(0, costLabels.length),
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        if (costDistCtx) {
            const demandItems = {!! json_encode($demandData) !!};
            const topCostItems = demandItems
                .filter(item => (item.cost_per_unit || 0) > 0)
                .sort((a, b) => (b.cost_removals || 0) - (a.cost_removals || 0))
                .slice(0, 5);
            
            const pieLabels = topCostItems.map(item => item.name || 'Unknown');
            const pieData = topCostItems.map(item => item.cost_removals || 0);
            const pieColors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0dcaf0'];

            new Chart(costDistCtx, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 8, font: { size: 9 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rs ' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }
    @endif
});
</script>
@endsection