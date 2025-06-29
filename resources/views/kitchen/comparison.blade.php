@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-balance-scale text-primary me-2"></i>
                Kitchen vs Sales Comparison
            </h2>
            <p class="text-muted mb-0">Compare daily sales with main kitchen stock issues</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Date Range Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('kitchen.comparison') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" 
                           id="startDate" 
                           name="start_date" 
                           class="form-control" 
                           value="{{ $startDate }}"
                           max="{{ now()->toDateString() }}"
                           onchange="validateDates()">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" 
                           id="endDate" 
                           name="end_date" 
                           class="form-control" 
                           value="{{ $endDate }}"
                           max="{{ now()->toDateString() }}"
                           onchange="validateDates()">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Update
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group btn-group-sm">
                        <button type="submit" name="start_date" value="{{ now()->toDateString() }}" 
                                onclick="setEndDate('{{ now()->toDateString() }}')" 
                                class="btn btn-outline-secondary">Today</button>
                        <button type="submit" name="start_date" value="{{ now()->subDay()->toDateString() }}" 
                                onclick="setEndDate('{{ now()->subDay()->toDateString() }}')" 
                                class="btn btn-outline-secondary">Yesterday</button>
                        <button type="submit" name="start_date" value="{{ now()->subDays(6)->toDateString() }}" 
                                onclick="setEndDate('{{ now()->toDateString() }}')" 
                                class="btn btn-outline-secondary">Last 7 Days</button>
                        <button type="submit" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" 
                                onclick="setEndDate('{{ now()->toDateString() }}')" 
                                class="btn btn-outline-secondary">This Month</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Sales Items</h6>
                    <h3 class="mb-0">{{ $comparisonData['summary']['total_sales_items'] }}</h3>
                    <small>{{ $comparisonData['summary']['total_sales_count'] }} bills</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Kitchen Issues</h6>
                    <h3 class="mb-0">{{ $comparisonData['summary']['total_kitchen_quantity'] }}</h3>
                    <small>{{ $comparisonData['summary']['total_kitchen_transactions'] }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Matching</h6>
                    <h3 class="mb-0">{{ $comparisonData['summary']['matching_categories'] }}</h3>
                    <small>categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-title">Date Range</h6>
                    <h6 class="mb-0">
                        @if($startDate === $endDate)
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        @endif
                    </h6>
                    <small>{{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} day(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Last Updated</h6>
                    <h6 class="mb-0">{{ now()->format('H:i') }}</h6>
                    <small>{{ now()->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Comparison Content -->
    <div class="row">
        <!-- Daily Sales Column -->
        <div class="col-lg-6">
            <div class="card mb-4 h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Daily Sales
                        @if($startDate !== $endDate)
                            <small class="ms-2">({{ $startDate }} to {{ $endDate }})</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(empty($dailySalesData['by_category']))
                        <div class="text-center p-4">
                            <i class="fas fa-receipt text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-2 text-muted">
                                No paid sales recorded for 
                                @if($startDate === $endDate)
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                @endif
                            </p>
                            <small class="text-muted">
                                Only sales with status 'paid' are included in this comparison.<br>
                                Check if sales exist with different statuses or timestamps.
                            </small>
                        </div>
                    @else
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Total Items:</strong> {{ $dailySalesData['total_items'] }}
                                </div>
                                <div class="col-6">
                                    <strong>Total Sales:</strong> {{ $dailySalesData['total_sales'] }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="sales-list" style="max-height: 500px; overflow-y: auto;">
                            @foreach($dailySalesData['by_category'] as $categoryId => $category)
                                <div class="category-section mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-primary text-white rounded-top">
                                        <span class="fw-bold">{{ $category['name'] }}</span>
                                        <span class="badge bg-light text-dark">{{ $category['total'] }} items</span>
                                    </div>
                                    @foreach($category['items'] as $item)
                                        <div class="d-flex justify-content-between align-items-center p-2 border-start border-end border-bottom">
                                            <div>
                                                <span class="fw-medium">{{ $item['name'] }}</span>
                                                <small class="text-muted d-block">
                                                    by {{ $item['user'] }}
                                                    @if(isset($item['sales_count']) && $item['sales_count'] > 1)
                                                        <span class="badge bg-info badge-sm ms-1">{{ $item['sales_count'] }} sales</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-secondary">{{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Kitchen Issues Column -->
        <div class="col-lg-6">
            <div class="card mb-4 h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-utensils me-2"></i>
                        Main Kitchen Issues
                        @if($startDate !== $endDate)
                            <small class="ms-2">({{ $startDate }} to {{ $endDate }})</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(empty($mainKitchenData['by_category']))
                        <div class="text-center p-4">
                            <i class="fas fa-box-open text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0 text-muted">
                                No kitchen issues recorded for 
                                @if($startDate === $endDate)
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Total Quantity:</strong> {{ $mainKitchenData['total_quantity'] }}
                                </div>
                                <div class="col-6">
                                    <strong>Transactions:</strong> {{ $mainKitchenData['total_transactions'] }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="kitchen-list" style="max-height: 500px; overflow-y: auto;">
                            @foreach($mainKitchenData['by_category'] as $categoryId => $category)
                                <div class="category-section mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-success text-white rounded-top">
                                        <span class="fw-bold">{{ $category['name'] }}</span>
                                        <span class="badge bg-light text-dark">{{ $category['total_quantity'] }} units</span>
                                    </div>
                                    @foreach($category['items'] as $item)
                                        <div class="d-flex justify-content-between align-items-center p-2 border-start border-end border-bottom">
                                            <div>
                                                <span class="fw-medium">{{ $item['name'] }}</span>
                                                <small class="text-muted d-block">
                                                    {{ $item['time'] }} by {{ $item['user'] }}
                                                    @if($item['description'])
                                                        <br><em>{{ $item['description'] }}</em>
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-danger">{{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison Analysis -->
    @if(!empty($comparisonData['matches']) || !empty($comparisonData['sales_only']) || !empty($comparisonData['kitchen_only']))
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-analytics me-2"></i>
                Detailed Comparison Analysis
            </h5>
        </div>
        <div class="card-body">
            <!-- Tabs for different views -->
            <ul class="nav nav-tabs mb-3" id="comparisonTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches" type="button" role="tab">
                        Matching Categories ({{ count($comparisonData['matches']) }})
                    </button>
                </li>
                @if(!empty($comparisonData['sales_only']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sales-only-tab" data-bs-toggle="tab" data-bs-target="#sales-only" type="button" role="tab">
                        Sales Only ({{ count($comparisonData['sales_only']) }})
                    </button>
                </li>
                @endif
                @if(!empty($comparisonData['kitchen_only']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kitchen-only-tab" data-bs-toggle="tab" data-bs-target="#kitchen-only" type="button" role="tab">
                        Kitchen Only ({{ count($comparisonData['kitchen_only']) }})
                    </button>
                </li>
                @endif
            </ul>

            <div class="tab-content" id="comparisonTabContent">
                <!-- Matching Categories -->
                <div class="tab-pane fade show active" id="matches" role="tabpanel">
                    @if(!empty($comparisonData['matches']))
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Category</th>
                                        <th>Sales Total</th>
                                        <th>Kitchen Total</th>
                                        <th>Difference</th>
                                        <th>Status</th>
                                        <th>Efficiency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparisonData['matches'] as $match)
                                        @php
                                            $efficiency = $match['sales_total'] > 0 ? round(($match['kitchen_total'] / $match['sales_total']) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">{{ $match['category_name'] }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $match['sales_total'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $match['kitchen_total'] }}</span>
                                            </td>
                                            <td>
                                                @if($match['difference'] > 0)
                                                    <span class="badge bg-warning text-dark">+{{ $match['difference'] }}</span>
                                                @elseif($match['difference'] < 0)
                                                    <span class="badge bg-danger">{{ $match['difference'] }}</span>
                                                @else
                                                    <span class="badge bg-success">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($match['difference'] > 0)
                                                    <i class="fas fa-arrow-up text-warning"></i> Over-issued
                                                @elseif($match['difference'] < 0)
                                                    <i class="fas fa-arrow-down text-danger"></i> Under-issued
                                                @else
                                                    <i class="fas fa-check text-success"></i> Balanced
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($efficiency > 120) bg-warning
                                                    @elseif($efficiency < 80) bg-danger  
                                                    @else bg-success
                                                    @endif">
                                                    {{ $efficiency }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No matching categories found between sales and kitchen issues.
                        </div>
                    @endif
                </div>

                <!-- Sales Only -->
                @if(!empty($comparisonData['sales_only']))
                <div class="tab-pane fade" id="sales-only" role="tabpanel">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        These categories have sales but no corresponding kitchen issues.
                    </div>
                    @foreach($comparisonData['sales_only'] as $category)
                        <div class="card mb-2">
                            <div class="card-header bg-warning text-dark">
                                <strong>{{ $category['name'] }}</strong> - {{ $category['total'] }} items sold
                            </div>
                            <div class="card-body">
                                @foreach($category['items'] as $item)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $item['name'] }} ({{ $item['quantity'] }})</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Kitchen Only -->
                @if(!empty($comparisonData['kitchen_only']))
                <div class="tab-pane fade" id="kitchen-only" role="tabpanel">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        These categories have kitchen issues but no corresponding sales.
                    </div>
                    @foreach($comparisonData['kitchen_only'] as $category)
                        <div class="card mb-2">
                            <div class="card-header bg-info text-white">
                                <strong>{{ $category['name'] }}</strong> - {{ $category['total_quantity'] }} units issued
                            </div>
                            <div class="card-body">
                                @foreach($category['items'] as $item)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $item['name'] }} ({{ $item['quantity'] }})</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-tools me-2"></i>
                Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <a href="{{ route('stock.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-warehouse me-2"></i>
                        Manage Inventory
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('cashier') }}" class="btn btn-outline-success w-100 mb-2">
                        <i class="fas fa-cash-register me-2"></i>
                        Cashier System
                    </a>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-info w-100 mb-2" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh Data
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-warning w-100 mb-2" onclick="setDateRange('week')">
                        <i class="fas fa-calendar-week me-2"></i>
                        This Week
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-section {
    border-radius: 0.375rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sales-list, .kitchen-list {
    scrollbar-width: thin;
    scrollbar-color: #6c757d #f8f9fa;
}

.sales-list::-webkit-scrollbar, .kitchen-list::-webkit-scrollbar {
    width: 6px;
}

.sales-list::-webkit-scrollbar-track, .kitchen-list::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.sales-list::-webkit-scrollbar-thumb, .kitchen-list::-webkit-scrollbar-thumb {
    background: #6c757d;
    border-radius: 3px;
}

.card {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.75em;
}

.badge-sm {
    font-size: 0.65em;
    padding: 0.2em 0.4em;
}

@media print {
    .btn, .nav-tabs, #exportBtn {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .col-lg-6 {
        width: 50% !important;
        float: left;
    }
}

.opacity-75 {
    opacity: 0.75;
}

.text-decoration-none:hover {
    text-decoration: none !important;
}

/* Date input styling */
input[type="date"] {
    min-width: 150px;
}

/* Button group responsive */
@media (max-width: 768px) {
    .btn-group-sm .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>

<script>
// Add debugging for sales data
console.log('Page loaded with data:', {
    startDate: '{{ $startDate }}',
    endDate: '{{ $endDate }}',
    salesCategories: {{ count($dailySalesData['by_category']) }},
    kitchenCategories: {{ count($mainKitchenData['by_category']) }},
    totalSalesItems: {{ $dailySalesData['total_items'] }},
    totalKitchenQuantity: {{ $mainKitchenData['total_quantity'] }}
});

$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Export functionality
    $('#exportBtn').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        window.location.href = `/kitchen/comparison/export?start_date=${startDate}&end_date=${endDate}&format=csv`;
    });
    
    // Initialize tooltips if Bootstrap 5 is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Date validation function
function validateDates() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (start > end) {
            alert('Start date cannot be after end date');
            document.getElementById('endDate').value = startDate;
        }
    }
}

// Helper function for quick date buttons
function setEndDate(date) {
    document.querySelector('input[name="end_date"]').value = date;
}

// Set date range helper
function setDateRange(type) {
    const today = new Date();
    let startDate, endDate;
    
    switch(type) {
        case 'week':
            const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate = firstDayOfWeek.toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        default:
            return;
    }
    
    document.getElementById('startDate').value = startDate;
    document.getElementById('endDate').value = endDate;
    document.querySelector('form').submit();
}

function refreshData() {
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
    
    // Show loading state
    const originalText = $('#exportBtn').html();
    $('#exportBtn').html('<i class="fas fa-spinner fa-spin me-1"></i> Loading...');
    $('#exportBtn').prop('disabled', true);
    
    // Make AJAX request to get fresh data
    $.ajax({
        url: '/kitchen/comparison/data',
        type: 'GET',
        data: { 
            start_date: startDate,
            end_date: endDate
        },
        success: function(response) {
            if (response.success) {
                // Reload the page with current dates
                window.location.reload();
            } else {
                alert('Error refreshing data. Please try again.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Refresh error:', error);
            alert('Error refreshing data. Please try again.');
        },
        complete: function() {
            // Restore button state
            $('#exportBtn').html(originalText);
            $('#exportBtn').prop('disabled', false);
        }
    });
}

// Add smooth scroll

// Add smooth scrolling for internal links
$('a[href^="#"]').on('click', function(event) {
    var target = $(this.getAttribute('href'));
    if (target.length) {
        event.preventDefault();
        $('html, body').stop().animate({
            scrollTop: target.offset().top - 100
        }, 1000);
    }
});

// Add loading states for form submissions
$('form').on('submit', function() {
    const submitBtn = $(this).find('button[type="submit"]');
    if (submitBtn.length) {
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Loading...');
        submitBtn.prop('disabled', true);
    }
});
</script>
@endsection