{{-- resources/views/sales/summary/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Sales Summary</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Sales Summary</h5>
        </div>
        <div class="card-body">
            <form id="summaryForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">View Type</label>
                    <select class="form-select" id="viewType" name="viewType">
                        <option value="daily">Daily View</option>
                        <option value="monthly">Monthly View</option>
                        <option value="yearly">Yearly View</option>
                    </select>
                </div>
                
                <div class="col-md-3" id="dailyDateContainer">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" id="dateFilter" name="date" 
                           value="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-3" id="monthlyDateContainer" style="display:none;">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" id="monthFilter" name="month" 
                           value="{{ date('Y-m') }}">
                </div>
                
                <div class="col-md-3" id="yearlyDateContainer" style="display:none;">
                    <label class="form-label">Year</label>
                    <select class="form-select" id="yearFilter" name="year">
                        @for($i = date('Y'); $i >= date('Y')-5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        Generate Summary
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Sales</h6>
                    <h3 class="mb-0" id="totalSales">Rs. 0.00</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Items Sold</h6>
                    <h3 class="mb-0" id="totalItems">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Average Sale Value</h6>
                    <h3 class="mb-0" id="averageSale">Rs. 0.00</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Categories</h6>
                    <h3 class="mb-0" id="categoriesCount">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Category Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Summary</h5>
            <div>
                <button class="btn btn-sm btn-light me-2" id="exportExcel">
                    <i class="fas fa-file-excel"></i> Export
                </button>
                <button class="btn btn-sm btn-light" id="printSummary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="summaryTable">
                    <thead>
                        <tr>
                            <th>Menu ID</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Total Revenue</th>
                            <th>Average Price</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Summary -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Category Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Items</th>
                            <th>Total Revenue</th>
                            <th>Average Price</th>
                            <th>% of Total Sales</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Ensure jQuery is loaded -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
let categoryChart;
let trendChart;
let summaryTable;
let categoryTable;

// Set up CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    initializeCharts();
    initializeTables();
    setupEventListeners();
    loadData(); // Load initial data
});

function initializeCharts() {
    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Sales Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue',
                data: [],
                borderColor: '#36A2EB',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initializeTables() {
    // Ensure jQuery and DataTables are loaded before initialization
    if ($.fn.DataTable) {
        summaryTable = $('#summaryTable').DataTable({
            pageLength: 10,
            order: [[3, 'desc']], // Order by quantity by default
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                emptyTable: "No data available in table"
            }
        });

        categoryTable = $('#categoryTable').DataTable({
            pageLength: 5,
            order: [[2, 'desc']], // Order by revenue by default
            language: {
                emptyTable: "No data available in table"
            }
        });
    } else {
        console.error('DataTables library not loaded');
    }
}

function setupEventListeners() {
    $('#viewType').change(function() {
        const viewType = $(this).val();
        toggleDateControls(viewType);
        loadData();
    });

    $('#summaryForm').submit(function(e) {
        e.preventDefault();
        loadData();
    });

    $('#printSummary').click(function() {
        window.print();
    });
}

function toggleDateControls(viewType) {
    $('#dailyDateContainer').toggle(viewType === 'daily');
    $('#monthlyDateContainer').toggle(viewType === 'monthly');
    $('#yearlyDateContainer').toggle(viewType === 'yearly');
}

function loadData() {
    const viewType = $('#viewType').val();
    const date = getSelectedDate(viewType);

    $.ajax({
        url: '/sales/summary/data',
        method: 'GET',
        data: { viewType, date },
        success: function(response) {
            console.log('AJAX Response:', response); // Debugging log
            if (response.summary && response.stats) {
                updateDashboard(response);
            } else {
                console.error('Invalid response structure:', response);
                alert('Error: Invalid data structure received from server.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert('Error loading data. Please try again. Details: ' + (xhr.responseText || error));
        }
    });
}

function getSelectedDate(viewType) {
    switch(viewType) {
        case 'daily':
            return $('#dateFilter').val();
        case 'monthly':
            return $('#monthFilter').val();
        case 'yearly':
            return $('#yearFilter').val();
        default:
            return '';
    }
}

function updateDashboard(data) {
    updateStatistics(data.stats);
    updateCharts(data);
    updateTables(data);
}

function updateStatistics(stats) {
    $('#totalSales').text(`Rs. ${stats.total_revenue.toFixed(2)}`);
    $('#totalItems').text(stats.total_items_sold);
    $('#averageSale').text(`Rs. ${stats.average_sale.toFixed(2)}`);
    $('#categoriesCount').text(stats.categories_count);
}

function updateCharts(data) {
    // Update Category Chart
    const categoryData = data.stats.category_distribution;
    categoryChart.data.labels = Object.keys(categoryData);
    categoryChart.data.datasets[0].data = Object.values(categoryData)
        .map(cat => cat.quantity || 0); // Default to 0 if undefined
    categoryChart.update();

    // Update Trend Chart
    const trendData = data.stats.trend_data;
    trendChart.data.labels = Object.keys(trendData);
    trendChart.data.datasets[0].data = Object.values(trendData)
        .map(day => day.total_revenue || 0); // Default to 0 if undefined
    trendChart.update();
}

function updateTables(data) {
    console.log('Updating tables with data:', data); // Debugging log

    // Update Summary Table
    summaryTable.clear();
    if (data.summary && Array.isArray(data.summary)) {
        data.summary.forEach(item => {
            summaryTable.row.add([
                item.menu_id || 'N/A',
                item.menu_name || 'Unknown Item',
                item.category_name || 'Uncategorized',
                item.total_quantity || 0,
                `Rs. ${(item.total_revenue || 0).toFixed(2)}`,
                item.total_quantity > 0 ? `Rs. ${((item.total_revenue || 0) / item.total_quantity).toFixed(2)}` : 'Rs. 0.00'
            ]);
        });
    } else {
        console.warn('No summary data available:', data.summary);
    }
    summaryTable.draw();

    // Update Category Table
    categoryTable.clear();
    if (data.stats && data.stats.category_distribution) {
        const totalRevenue = Object.values(data.stats.category_distribution)
            .reduce((sum, cat) => sum + (cat.revenue || 0), 0);

        Object.entries(data.stats.category_distribution).forEach(([category, stats]) => {
            const percentage = totalRevenue > 0 ? ((stats.revenue || 0) / totalRevenue * 100).toFixed(2) : 0;
            categoryTable.row.add([
                category || 'Unknown',
                stats.quantity || 0,
                `Rs. ${(stats.revenue || 0).toFixed(2)}`,
                `Rs. ${(stats.average_price || 0).toFixed(2)}`,
                `${percentage}%`
            ]);
        });
    } else {
        console.warn('No category distribution data available:', data.stats.category_distribution);
    }
    categoryTable.draw();
}
</script>
@endpush
@endsection