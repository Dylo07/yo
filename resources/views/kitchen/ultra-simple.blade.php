@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-utensils text-primary me-2"></i>
        Kitchen Inventory Management
    </h2>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Kitchen Items</h6>
                    <h3>{{ $stats['total_items'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Low Stock</h6>
                    <h3>{{ $stats['low_stock_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>Out of Stock</h6>
                    <h3>{{ $stats['out_of_stock_count'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Value</h6>
                    <h3>Rs {{ number_format($stats['total_value'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Add New Kitchen Item -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Add Item to Kitchen</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('kitchen.add') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Item *</label>
                            <select class="form-select" name="item_id" required>
                                <option value="">Choose an item...</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }} 
                                        @if($item->group_name)
                                            ({{ $item->group_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Unit *</label>
                                    <select class="form-select" name="kitchen_unit" required>
                                        <option value="pieces">Pieces</option>
                                        <option value="kg">Kilograms</option>
                                        <option value="g">Grams</option>
                                        <option value="liters">Liters</option>
                                        <option value="ml">Milliliters</option>
                                        <option value="packets">Packets</option>
                                        <option value="bottles">Bottles</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Stock *</label>
                                    <input type="number" class="form-control" name="kitchen_current_stock" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Min Stock *</label>
                                    <input type="number" class="form-control" name="kitchen_minimum_stock" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Cost/Unit (Rs) *</label>
                                    <input type="number" class="form-control" name="kitchen_cost_per_unit" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="kitchen_description" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus me-1"></i> Add to Kitchen
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kitchen Items List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kitchen Items</h5>
                </div>
                <div class="card-body">
                    @if($kitchenItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Stock</th>
                                        <th>Min</th>
                                        <th>Unit</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kitchenItems as $item)
                                    @php
                                        $isLowStock = $item->kitchen_current_stock <= $item->kitchen_minimum_stock;
                                        $isOutOfStock = $item->kitchen_current_stock <= 0;
                                    @endphp
                                    <tr class="{{ $isLowStock ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $item->name }}</strong>
                                            @if($item->group_name)
                                                <br><small class="text-muted">{{ $item->group_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $isOutOfStock ? 'text-danger' : ($isLowStock ? 'text-warning' : 'text-success') }}">
                                                {{ $item->kitchen_current_stock }}
                                            </span>
                                        </td>
                                        <td>{{ $item->kitchen_minimum_stock }}</td>
                                        <td>{{ $item->kitchen_unit }}</td>
                                        <td>Rs {{ number_format($item->kitchen_cost_per_unit, 2) }}</td>
                                        <td>
                                            @if($isOutOfStock)
                                                <span class="badge bg-danger">Out</span>
                                            @elseif($isLowStock)
                                                <span class="badge bg-warning">Low</span>
                                            @else
                                                <span class="badge bg-success">OK</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="showStockModal({{ $item->id }}, '{{ $item->name }}', {{ $item->kitchen_current_stock }})">
                                                    <i class="fas fa-plus-circle"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-sm" 
                                                        onclick="showEditModal({{ $item->id }}, '{{ $item->kitchen_unit }}', {{ $item->kitchen_minimum_stock }}, {{ $item->kitchen_cost_per_unit }}, '{{ addslashes($item->kitchen_description ?? '') }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="removeFromKitchen({{ $item->id }}, '{{ $item->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $kitchenItems->links() }}
                    @else
                        <div class="text-center p-4">
                            <i class="fas fa-boxes text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">No kitchen items found. Add items using the form on the left.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm" action="{{ route('kitchen.stock.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="stock_item_id" name="item_id">
                    
                    <div class="mb-3">
                        <strong id="stock_item_name"></strong>
                        <p class="text-muted">Current Stock: <span id="current_stock"></span></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action *</label>
                        <select class="form-select" name="action" required>
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock Level</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity *</label>
                        <input type="number" class="form-control" name="quantity" min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Reason for stock change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kitchen Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="{{ route('kitchen.item.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="edit_item_id" name="item_id">
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Unit *</label>
                                <select class="form-select" id="edit_kitchen_unit" name="kitchen_unit" required>
                                    <option value="pieces">Pieces</option>
                                    <option value="kg">Kilograms</option>
                                    <option value="g">Grams</option>
                                    <option value="liters">Liters</option>
                                    <option value="ml">Milliliters</option>
                                    <option value="packets">Packets</option>
                                    <option value="bottles">Bottles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Stock *</label>
                                <input type="number" class="form-control" id="edit_minimum_stock" 
                                       name="kitchen_minimum_stock" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cost per Unit (Rs) *</label>
                        <input type="number" class="form-control" id="edit_cost_per_unit" 
                               name="kitchen_cost_per_unit" min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" 
                                  name="kitchen_description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Confirmation Form (Hidden) -->
<form id="removeForm" action="{{ route('kitchen.remove') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" id="remove_item_id" name="item_id">
</form>

<script>
// Show stock update modal
function showStockModal(itemId, itemName, currentStock) {
    document.getElementById('stock_item_id').value = itemId;
    document.getElementById('stock_item_name').textContent = itemName;
    document.getElementById('current_stock').textContent = currentStock;
    
    const modal = new bootstrap.Modal(document.getElementById('stockModal'));
    modal.show();
}

// Show edit modal
function showEditModal(itemId, unit, minStock, costPerUnit, description) {
    document.getElementById('edit_item_id').value = itemId;
    document.getElementById('edit_kitchen_unit').value = unit;
    document.getElementById('edit_minimum_stock').value = minStock;
    document.getElementById('edit_cost_per_unit').value = costPerUnit;
    document.getElementById('edit_description').value = description;
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

// Remove from kitchen
function removeFromKitchen(itemId, itemName) {
    if (confirm(`Are you sure you want to remove "${itemName}" from kitchen inventory?`)) {
        document.getElementById('remove_item_id').value = itemId;
        document.getElementById('removeForm').submit();
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<style>
.table-warning {
    --bs-table-bg: #fff3cd;
}

.card {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endsection