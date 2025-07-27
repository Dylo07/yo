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

    
             

 
<!-- UPDATED Daily Kitchen Consumption Section -->
<div class="card mt-4">
    <div class="card-header bg-warning text-dark">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Daily Kitchen Consumption
                @if($startDate !== $endDate)
                    <small class="ms-2">({{ $startDate }} to {{ $endDate }})</small>
                @else
                    <small class="ms-2">({{ $startDate }})</small>
                @endif
            </h5>
            <div>
                <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-dark ms-2">
                    <i class="fas fa-cog me-1"></i> Manage Recipes
                </a>
                <a href="{{ route('kitchen.index') }}" class="btn btn-sm btn-outline-dark ms-2">
                    <i class="fas fa-utensils me-1"></i> Manage Inventory
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="consumptionData">
            <div class="text-center p-4">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2 mb-0">Loading consumption data...</p>
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
// UPDATED JavaScript to use date range for kitchen consumption
document.addEventListener('DOMContentLoaded', function() {
    loadConsumption();
});

function loadConsumption() {
    // Get start and end dates from the main filter
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const consumptionData = document.getElementById('consumptionData');
    
    // Show loading
    consumptionData.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2 mb-0">Loading consumption data...</p>
        </div>
    `;
    
    // Use date range instead of single date
    fetch(`/recipes/consumption?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.consumption && data.consumption.length > 0) {
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Total Cost</h6>
                                <h4 class="mb-0 text-danger">Rs ${parseFloat(data.total_cost).toLocaleString('en-US', {minimumFractionDigits: 2})}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Items Consumed</h6>
                                <h4 class="mb-0 text-info">${data.consumption.length}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Date Range</h6>
                                <h6 class="mb-0 text-primary">
                                    ${startDate === endDate ? 
                                        new Date(startDate).toLocaleDateString() : 
                                        new Date(startDate).toLocaleDateString() + ' - ' + new Date(endDate).toLocaleDateString()
                                    }
                                </h6>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ingredient</th>
                                    <th>Category</th>
                                    <th>Total Consumed</th>
                                    <th>Cost/Unit</th>
                                    <th>Total Cost</th>
                                    <th>Usage %</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.consumption.forEach(item => {
                    const totalCost = parseFloat(item.total_cost);
                    const usagePercentage = data.total_cost > 0 ? ((totalCost / data.total_cost) * 100).toFixed(1) : 0;
                    
                    html += `
                        <tr>
                            <td><strong>${item.item_name}</strong></td>
                            <td><span class="badge bg-secondary">${item.category_name || 'Uncategorized'}</span></td>
                            <td>${parseFloat(item.total_consumed).toFixed(2)} ${item.kitchen_unit}</td>
                            <td>Rs ${parseFloat(item.kitchen_cost_per_unit || 0).toFixed(2)}</td>
                            <td><strong>Rs ${totalCost.toFixed(2)}</strong></td>
                            <td>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: ${usagePercentage}%" 
                                         aria-valuenow="${usagePercentage}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        ${usagePercentage}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                consumptionData.innerHTML = html;
            } else {
                const dateText = startDate === endDate ? 
                    new Date(startDate).toLocaleDateString() : 
                    `${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}`;
                    
                consumptionData.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-utensils text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-2 text-muted">No kitchen consumption recorded for ${dateText}</p>
                        <small class="text-muted">Consumption is tracked automatically when sales are completed and recipes are defined.</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading consumption data:', error);
            consumptionData.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0 text-danger">Error loading consumption data</p>
                    <div class="alert alert-warning mt-2">
                        <small>Make sure the consumption route is properly configured and the database tables exist.</small>
                    </div>
                </div>
            `;
        });
}

// Add event listeners to reload consumption data when date filters change
document.addEventListener('DOMContentLoaded', function() {
    // Listen for form submission (date filter changes)
    const dateForm = document.querySelector('form[action*="kitchen.comparison"]');
    if (dateForm) {
        dateForm.addEventListener('submit', function() {
            // Delay loading to allow page data to update
            setTimeout(function() {
                loadConsumption();
            }, 1000);
        });
    }
    
    // Listen for date input changes
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            // Small delay to ensure date is set
            setTimeout(loadConsumption, 500);
        });
        
        endDateInput.addEventListener('change', function() {
            setTimeout(loadConsumption, 500);
        });
    }
    
    // Initial load
    loadConsumption();
});

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