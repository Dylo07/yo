@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-fire text-warning me-2"></i>LP Gas Management</h2>
            <p class="text-muted mb-0">Track gas cylinder stock, purchases, and usage</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCylinderModal">
                <i class="fas fa-plus me-1"></i> Add Cylinder Type
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                <i class="fas fa-shopping-cart me-1"></i> Record Purchase
            </button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#issueModal">
                <i class="fas fa-sign-out-alt me-1"></i> Issue to Kitchen
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Stock</p>
                            <h3 class="mb-0">{{ $totalStock }}</h3>
                            <small class="text-muted">Cylinders</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-gas-pump text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Stock Value</p>
                            <h3 class="mb-0">Rs. {{ number_format($totalStockValue, 2) }}</h3>
                            <small class="text-muted">Current worth</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-coins text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Monthly Usage</p>
                            <h3 class="mb-0">{{ $monthlyIssues }}</h3>
                            <small class="text-muted">Cylinders issued</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Today's Activity</p>
                            <h3 class="mb-0">{{ $todayIssues }}</h3>
                            <small class="text-muted">Issued today</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-day text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    @if($lowStockCylinders->count() > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h5 class="mb-1">Low Stock Alert!</h5>
                <p class="mb-0">
                    {{ $lowStockCylinders->count() }} cylinder type(s) running low: 
                    <strong>{{ $lowStockCylinders->pluck('name')->implode(', ') }}</strong>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Cylinder Stock Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Gas Cylinder Inventory</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cylinder Type</th>
                            <th>Weight (kg)</th>
                            <th>Price per Unit</th>
                            <th>Current Stock</th>
                            <th>Min. Stock</th>
                            <th>Stock Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cylinders as $cylinder)
                        <tr>
                            <td><strong>{{ $cylinder->name }}</strong></td>
                            <td>{{ $cylinder->weight_kg }} kg</td>
                            <td>Rs. {{ number_format($cylinder->price, 2) }}</td>
                            <td>
                                <span class="badge {{ $cylinder->isLowStock() ? 'bg-danger' : 'bg-success' }} fs-6">
                                    {{ $cylinder->current_stock }}
                                </span>
                            </td>
                            <td>{{ $cylinder->minimum_stock }}</td>
                            <td>Rs. {{ number_format($cylinder->current_stock * $cylinder->price, 2) }}</td>
                            <td>
                                @if($cylinder->isLowStock())
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Adequate</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editCylinder({{ $cylinder->id }}, '{{ $cylinder->name }}', {{ $cylinder->weight_kg }}, {{ $cylinder->price }}, {{ $cylinder->minimum_stock }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No cylinder types added yet. Click "Add Cylinder Type" to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Usage Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Usage Trends</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="loadChart('month')">Last 30 Days</button>
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('year')">Last 12 Months</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <canvas id="usageChart" height="80"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2 text-success"></i>Recent Purchases</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Cylinder</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Dealer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                                    <td>{{ $purchase->gasCylinder->name }}</td>
                                    <td><span class="badge bg-success">{{ $purchase->quantity }}</span></td>
                                    <td>Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                    <td>{{ $purchase->dealer_name ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No purchases recorded yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2 text-warning"></i>Recent Issues</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Cylinder</th>
                                    <th>Qty</th>
                                    <th>Issued To</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIssues as $issue)
                                <tr>
                                    <td>{{ $issue->issue_date->format('d M Y') }}</td>
                                    <td>{{ $issue->gasCylinder->name }}</td>
                                    <td><span class="badge bg-warning">{{ $issue->quantity }}</span></td>
                                    <td>{{ $issue->issued_to }}</td>
                                    <td>{{ $issue->user->name ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No issues recorded yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Cylinder Type Modal -->
<div class="modal fade" id="addCylinderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gas.cylinder.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Cylinder Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cylinder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., 12.5kg Cylinder" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="weight_kg" placeholder="12.5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price per Cylinder <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="price" placeholder="2500.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock Alert <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="minimum_stock" value="5" required>
                        <small class="text-muted">Alert when stock falls below this quantity</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Cylinder Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Cylinder Modal -->
<div class="modal fade" id="editCylinderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCylinderForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Cylinder Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cylinder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="weight_kg" id="edit_weight" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price per Cylinder <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="price" id="edit_price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock Alert <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="minimum_stock" id="edit_min_stock" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Purchase Modal -->
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gas.purchase.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Record Gas Purchase</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cylinder Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="gas_cylinder_id" id="purchase_cylinder_id" required onchange="updatePurchasePrice()">
                            <option value="">Select cylinder type</option>
                            @foreach($cylinders as $cylinder)
                                <option value="{{ $cylinder->id }}" data-price="{{ $cylinder->price }}">
                                    {{ $cylinder->name }} ({{ $cylinder->weight_kg }}kg) - Rs. {{ number_format($cylinder->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" id="purchase_quantity" min="1" required onchange="calculateTotal()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price per Unit <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="price_per_unit" id="purchase_price" required onchange="calculateTotal()">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control bg-light" id="purchase_total" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dealer Name</label>
                        <input type="text" class="form-control" name="dealer_name" placeholder="Dealer name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" name="invoice_number" placeholder="Invoice #">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="purchase_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="update_price" id="update_price" value="1">
                        <label class="form-check-label" for="update_price">
                            Update cylinder price to match this purchase
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gas.issue.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2"></i>Issue Gas to Kitchen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cylinder Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="gas_cylinder_id" id="issue_cylinder_id" required onchange="updateAvailableStock()">
                            <option value="">Select cylinder type</option>
                            @foreach($cylinders as $cylinder)
                                <option value="{{ $cylinder->id }}" data-stock="{{ $cylinder->current_stock }}">
                                    {{ $cylinder->name }} (Available: {{ $cylinder->current_stock }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Available stock: <span id="available_stock">-</span></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issued To <span class="text-danger">*</span></label>
                        <select class="form-select" name="issued_to" required>
                            <option value="Kitchen">Kitchen</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Banquet">Banquet</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="issue_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Issue Gas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let usageChart = null;

// Edit cylinder
function editCylinder(id, name, weight, price, minStock) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_weight').value = weight;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_min_stock').value = minStock;
    document.getElementById('editCylinderForm').action = `/gas/cylinder/${id}`;
    new bootstrap.Modal(document.getElementById('editCylinderModal')).show();
}

// Update purchase price when cylinder is selected
function updatePurchasePrice() {
    const select = document.getElementById('purchase_cylinder_id');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    if (price) {
        document.getElementById('purchase_price').value = price;
        calculateTotal();
    }
}

// Calculate total purchase amount
function calculateTotal() {
    const qty = parseFloat(document.getElementById('purchase_quantity').value) || 0;
    const price = parseFloat(document.getElementById('purchase_price').value) || 0;
    const total = qty * price;
    document.getElementById('purchase_total').value = 'Rs. ' + total.toFixed(2);
}

// Update available stock when cylinder is selected for issue
function updateAvailableStock() {
    const select = document.getElementById('issue_cylinder_id');
    const selectedOption = select.options[select.selectedIndex];
    const stock = selectedOption.getAttribute('data-stock');
    document.getElementById('available_stock').textContent = stock || '-';
}

// Load usage chart
async function loadChart(period) {
    try {
        const response = await fetch(`/gas/stats?period=${period}`);
        const result = await response.json();
        
        if (result.success) {
            const labels = result.data.map(d => d.label);
            const purchases = result.data.map(d => d.purchases);
            const issues = result.data.map(d => d.issues);
            
            if (usageChart) {
                usageChart.destroy();
            }
            
            const ctx = document.getElementById('usageChart').getContext('2d');
            usageChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Purchases',
                            data: purchases,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Issues',
                            data: issues,
                            borderColor: 'rgb(255, 159, 64)',
                            backgroundColor: 'rgba(255, 159, 64, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading chart:', error);
    }
}

// Load chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadChart('month');
});
</script>
@endpush
