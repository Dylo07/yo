{{-- resources/views/damage-items/index.blade.php --}}
@extends('layouts.app')

@section('styles')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        margin: 20px;
        overflow: hidden;
    }
    
    .header-section {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .header-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .header-content {
        position: relative;
        z-index: 2;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(79, 172, 254, 0.3);
    }
    
    .stats-card.danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }
    
    .stats-card.danger:hover {
        box-shadow: 0 15px 30px rgba(255, 107, 107, 0.3);
    }
    
    .stats-card.warning {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
    }
    
    .stats-card.warning:hover {
        box-shadow: 0 15px 30px rgba(254, 202, 87, 0.3);
    }
    
    .stats-card.success {
        background: linear-gradient(135deg, #48c9b0 0%, #1dd1a1 100%);
    }
    
    .stats-card.success:hover {
        box-shadow: 0 15px 30px rgba(72, 201, 176, 0.3);
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .form-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        color: white;
    }
    
    .form-control {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        background: rgba(255,255,255,0.9);
    }
    
    .form-control:focus {
        box-shadow: 0 5px 25px rgba(102, 126, 234, 0.3);
        transform: translateY(-2px);
        background: white;
        border-color: #4f46e5;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
    }
    
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        font-weight: 600;
    }
    
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: scale(1.01);
    }
    
    .badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85em;
    }
    
    .badge.bg-warning {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%) !important;
        color: #2c2c2c;
    }
    
    .badge.bg-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
        color: white;
    }
    
    .month-selector {
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(255,255,255,0.2);
        border-radius: 15px;
        color: white;
        padding: 10px 20px;
        backdrop-filter: blur(10px);
    }
    
    .month-selector:focus {
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        color: white;
        box-shadow: 0 0 20px rgba(255,255,255,0.3);
    }
    
    .alert {
        border: none;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #48c9b0 0%, #1dd1a1 100%);
        color: white;
    }
    
    .icon-large {
        font-size: 2.5rem;
        margin-bottom: 10px;
        opacity: 0.8;
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .no-data {
        text-align: center;
        padding: 50px;
        color: #666;
    }
    
    .no-data i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="main-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-3"></i>
                        Damaged/Missing Items Management
                    </h2>
                    <p class="mb-0 mt-2 opacity-75">Track and manage damaged or missing inventory items</p>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" class="d-inline">
                        <input type="month" 
                               name="month" 
                               class="month-selector" 
                               value="{{ request('month', date('Y-m')) }}"
                               onchange="this.form.submit()">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line icon-large"></i>
                        <h4 class="mb-0">{{ $items->count() }}</h4>
                        <p class="mb-0">Total Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card danger h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle icon-large"></i>
                        <h4 class="mb-0">{{ $items->where('type', 'damaged')->count() }}</h4>
                        <p class="mb-0">Damaged Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card warning h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-question-circle icon-large"></i>
                        <h4 class="mb-0">{{ $items->where('type', 'missing')->count() }}</h4>
                        <p class="mb-0">Missing Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign icon-large pulse"></i>
                        <h4 class="mb-0">Rs. {{ number_format($totalCost, 2) }}</h4>
                        <p class="mb-0">Total Cost</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Chart -->
        <div class="chart-container">
            <h5 class="mb-4">
                <i class="fas fa-chart-bar me-2"></i>
                Yearly Damage/Missing Items Overview - {{ Carbon\Carbon::parse(request('month', date('Y-m')))->format('Y') }}
            </h5>
            <canvas id="yearlyChart" height="100"></canvas>
        </div>

        <!-- Add New Item Form -->
        <div class="form-section">
            <h5 class="mb-4">
                <i class="fas fa-plus-circle me-2"></i>
                Add New Damaged/Missing Item
            </h5>
            <form action="{{ route('damage-items.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="item_name" class="form-control" required placeholder="Enter item name" value="{{ old('item_name') }}">
                        @error('item_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="Qty" value="{{ old('quantity') }}">
                        @error('quantity')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" name="unit_price" class="form-control" step="0.01" min="0" required placeholder="0.00" value="{{ old('unit_price') }}">
                        </div>
                        @error('unit_price')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="damaged" {{ old('type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            <option value="missing" {{ old('type') == 'missing' ? 'selected' : '' }}>Missing</option>
                        </select>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="reported_date" class="form-control" required value="{{ old('reported_date', date('Y-m-d')) }}">
                        @error('reported_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i>Add
                        </button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any additional notes here...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <!-- Items Table -->
        <div class="table-container">
            <h6 class="text-muted mb-3">
                <i class="fas fa-calendar me-2"></i>
                Items for {{ Carbon\Carbon::parse(request('month', date('Y-m')))->format('F Y') }}
            </h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-alt me-1"></i>Date</th>
                            <th><i class="fas fa-box me-1"></i>Item Name</th>
                            <th><i class="fas fa-tag me-1"></i>Type</th>
                            <th><i class="fas fa-sort-numeric-up me-1"></i>Quantity</th>
                            <th><i class="fas fa-money-bill me-1"></i>Unit Price</th>
                            <th><i class="fas fa-calculator me-1"></i>Total Cost</th>
                            <th><i class="fas fa-sticky-note me-1"></i>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->reported_date->format('Y-m-d') }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>
                                <span class="badge {{ $item->type === 'damaged' ? 'bg-warning' : 'bg-danger' }}">
                                    <i class="fas {{ $item->type === 'damaged' ? 'fa-exclamation-triangle' : 'fa-question-circle' }} me-1"></i>
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td>Rs. {{ number_format($item->total_cost, 2) }}</td>
                            <td>{{ $item->notes ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <div>No items recorded for this month</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Total Cost:</td>
                            <td colspan="2" class="fw-bold">Rs. {{ number_format($totalCost, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Yearly chart data from Laravel
const yearlyData = @json($yearlyData ?? []);

// Initialize chart
function initChart() {
    const ctx = document.getElementById('yearlyChart').getContext('2d');
    
    const labels = yearlyData.map(item => item.month);
    const damagedData = yearlyData.map(item => item.damaged_cost);
    const missingData = yearlyData.map(item => item.missing_cost);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Damaged Items (Rs.)',
                    data: damagedData,
                    backgroundColor: 'rgba(255, 107, 107, 0.8)',
                    borderColor: 'rgba(255, 107, 107, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Missing Items (Rs.)',
                    data: missingData,
                    backgroundColor: 'rgba(254, 202, 87, 0.8)',
                    borderColor: 'rgba(254, 202, 87, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 14,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.3)',
                    borderWidth: 1,
                    cornerRadius: 10,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rs. ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        },
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Initialize chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});
</script>
@endpush
@endsection