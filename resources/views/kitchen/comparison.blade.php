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
            <button class="btn btn-outline-secondary" onclick="printComparison()">
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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Daily Sales
                    </h5>
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light" onclick="toggleDropdown('salesFilterDrop')">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <div id="salesFilterDrop" class="position-absolute end-0 bg-white border rounded shadow-sm p-2" style="display:none; z-index:100; min-width:200px; max-height:300px; overflow-y:auto; top:100%; color:#000;">
                            <label class="d-block small fw-bold border-bottom pb-1 mb-1">
                                <input type="checkbox" id="salesAllCb" checked onchange="toggleAllSalesCats(this)"> All Categories
                            </label>
                            @foreach($dailySalesData['by_category'] as $catId => $cat)
                                <label class="d-block small">
                                    <input type="checkbox" class="salesCatCb" value="{{ $catId }}" checked onchange="filterSalesCategories()"> {{ $cat['name'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(empty($dailySalesData['by_category']))
                        <div class="text-center p-4">
                            <i class="fas fa-receipt text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-2 text-muted">No paid sales recorded</p>
                        </div>
                    @else
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="row">
                                <div class="col-6"><strong>Total Items:</strong> <span id="salesTotalItems">{{ $dailySalesData['total_items'] }}</span></div>
                                <div class="col-6"><strong>Total Sales:</strong> {{ $dailySalesData['total_sales'] }}</div>
                            </div>
                        </div>
                        
                        <div class="sales-list" style="max-height: 600px; overflow-y: auto;">
                            @foreach($dailySalesData['by_category'] as $categoryId => $category)
                                <div class="category-section mb-3 sales-cat-section" data-cat-id="{{ $categoryId }}">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-primary text-white rounded-top">
                                        <span class="fw-bold">{{ $category['name'] }}</span>
                                        <span class="badge bg-light text-dark">{{ $category['total'] }} items</span>
                                    </div>
                                    @if(!empty($category['category_summary']))
                                        <div class="p-1 border-start border-end" style="background:#eef; font-size:0.7rem; color:#555; font-style:italic;">
                                            {{ $category['category_summary'] }}
                                        </div>
                                    @endif
                                    @foreach($category['items'] as $item)
                                        <div class="d-flex justify-content-between align-items-center p-2 border-start border-end border-bottom">
                                            <div style="flex:1; min-width:0;">
                                                <span class="fw-medium">{{ $item['name'] }}</span>
                                                @if(!empty($item['item_summary']))
                                                    <small class="text-muted d-block" style="font-size:0.65rem; font-style:italic;">{{ $item['item_summary'] }}</small>
                                                @endif
                                            </div>
                                            <span class="badge bg-secondary ms-1">{{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        @if(!empty($grandIngredientSummary))
                            <div class="mt-3 p-2 border rounded" style="background:#f0f4ff; border-color:#b0c4de !important;">
                                <div class="fw-bold mb-1" style="font-size:0.75rem; border-bottom:1px solid #999; padding-bottom:2px;">Total Ingredients Summary</div>
                                <div style="font-size:0.65rem; line-height:1.4; color:#333;">
                                    @foreach(preg_split('/\s{2,}/', $grandIngredientSummary) as $ing)
                                        @if(preg_match('/^(.+?)\s+([\d,.]+)$/', trim($ing), $m))
                                            <span class="d-inline-block me-2 mb-1">{{ $m[1] }} <strong>{{ $m[2] }}</strong></span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Inventory Issues Column -->
        <div class="col-lg-6">
            <div class="card mb-4 h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-utensils me-2"></i>
                        <span id="issuesTitle">Inventory Issues</span>
                    </h5>
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light" onclick="toggleDropdown('issuesFilterDrop')">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <div id="issuesFilterDrop" class="position-absolute end-0 bg-white border rounded shadow-sm p-2" style="display:none; z-index:100; min-width:220px; max-height:300px; overflow-y:auto; top:100%; color:#000;">
                            <label class="d-block small fw-bold border-bottom pb-1 mb-1">
                                <input type="checkbox" id="issuesAllCb" onchange="toggleAllIssueActions(this)"> All Actions
                            </label>
                            @foreach($inventoryIssues as $action => $actionData)
                                <label class="d-block small">
                                    <input type="checkbox" class="issuesActionCb" value="{{ $action }}" {{ $action === 'remove_main_kitchen' ? 'checked' : '' }} onchange="filterIssueActions()"> {{ $actionData['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="row">
                            <div class="col-4"><strong>Qty:</strong> <span id="issuesTotalQty">{{ number_format($mainKitchenData['total_quantity'], 1) }}</span></div>
                            <div class="col-4"><strong>Txns:</strong> <span id="issuesTotalTxns">{{ $mainKitchenData['total_transactions'] }}</span></div>
                            <div class="col-4"><strong class="text-danger">Cost:</strong> <span id="issuesTotalCost" class="text-danger">Rs {{ number_format($mainKitchenData['total_cost'] ?? 0, 0) }}</span></div>
                        </div>
                    </div>
                    <div id="issuesContent" class="kitchen-list" style="max-height: 600px; overflow-y: auto;">
                        {{-- Rendered by JS --}}
                    </div>
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
    // ===== Inventory Issues Data (from server) =====
    const inventoryIssuesData = @json($inventoryIssues ?? []);

    // ===== Dropdown Toggle =====
    function toggleDropdown(id) {
        const el = document.getElementById(id);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
    document.addEventListener('click', function(e) {
        ['salesFilterDrop','issuesFilterDrop'].forEach(id => {
            const dd = document.getElementById(id);
            if (dd && !dd.contains(e.target) && !e.target.closest('[onclick*="'+id+'"]')) {
                dd.style.display = 'none';
            }
        });
    });

    // ===== Sales Category Filter =====
    function toggleAllSalesCats(cb) {
        document.querySelectorAll('.salesCatCb').forEach(c => c.checked = cb.checked);
        filterSalesCategories();
    }
    function filterSalesCategories() {
        const checked = [...document.querySelectorAll('.salesCatCb:checked')].map(c => c.value);
        let total = 0;
        document.querySelectorAll('.sales-cat-section').forEach(sec => {
            if (checked.includes(sec.dataset.catId)) {
                sec.style.display = '';
                const badge = sec.querySelector('.badge');
                if (badge) total += parseInt(badge.textContent) || 0;
            } else {
                sec.style.display = 'none';
            }
        });
        const el = document.getElementById('salesTotalItems');
        if (el) el.textContent = total;
        document.getElementById('salesAllCb').checked = checked.length === document.querySelectorAll('.salesCatCb').length;
    }

    // ===== Issues Action Filter =====
    function toggleAllIssueActions(cb) {
        document.querySelectorAll('.issuesActionCb').forEach(c => c.checked = cb.checked);
        filterIssueActions();
    }
    function filterIssueActions() {
        const selected = [...document.querySelectorAll('.issuesActionCb:checked')].map(c => c.value);
        document.getElementById('issuesAllCb').checked = selected.length === document.querySelectorAll('.issuesActionCb').length;

        // Merge selected actions
        const merged = {};
        let totalQty = 0, totalTxns = 0, totalCost = 0;
        const labels = [];
        selected.forEach(action => {
            const ad = inventoryIssuesData[action];
            if (!ad) return;
            labels.push(ad.label);
            totalQty += ad.total_quantity;
            totalTxns += ad.total_transactions;
            totalCost += ad.total_cost;
            Object.entries(ad.by_category).forEach(([catId, cat]) => {
                if (!merged[catId]) merged[catId] = { name: cat.name, items: {}, total_quantity: 0, total_cost: 0 };
                merged[catId].total_quantity += cat.total_quantity;
                merged[catId].total_cost += (cat.total_cost || 0);
                cat.items.forEach(item => {
                    if (!merged[catId].items[item.name]) {
                        merged[catId].items[item.name] = { ...item };
                    } else {
                        merged[catId].items[item.name].quantity += item.quantity;
                        merged[catId].items[item.name].total_cost += item.total_cost;
                    }
                });
            });
        });

        // Update title
        document.getElementById('issuesTitle').textContent = (labels.length ? labels.join(', ') : 'Inventory') + ' Issues';
        document.getElementById('issuesTotalQty').textContent = totalQty.toFixed(1);
        document.getElementById('issuesTotalTxns').textContent = totalTxns;
        document.getElementById('issuesTotalCost').textContent = 'Rs ' + Math.round(totalCost).toLocaleString();

        // Render
        const container = document.getElementById('issuesContent');
        if (Object.keys(merged).length === 0) {
            container.innerHTML = '<div class="text-center p-4 text-muted">No issues for selected filters</div>';
            return;
        }
        let html = '';
        Object.entries(merged).forEach(([catId, cat]) => {
            html += `<div class="category-section mb-3">
                <div class="d-flex justify-content-between align-items-center p-2 bg-success text-white rounded-top">
                    <span class="fw-bold">${cat.name}</span>
                    <span><span class="badge bg-light text-dark">${cat.total_quantity.toFixed(1)} qty</span>`;
            if (cat.total_cost > 0) html += ` <small class="ms-1">Rs ${Math.round(cat.total_cost).toLocaleString()}</small>`;
            html += `</span></div>`;
            Object.values(cat.items).forEach(item => {
                html += `<div class="d-flex justify-content-between align-items-center p-2 border-start border-end border-bottom">
                    <div style="flex:1;min-width:0;">
                        <span class="fw-medium">${item.name}</span>`;
                if (item.cost_per_unit > 0) html += `<small class="text-muted d-block" style="font-size:0.65rem;">@Rs ${item.cost_per_unit.toFixed(2)} = Rs ${item.total_cost.toFixed(2)}</small>`;
                html += `</div><span class="badge bg-danger ms-1">${item.quantity.toFixed(1)}</span></div>`;
            });
            html += '</div>';
        });
        container.innerHTML = html;
    }

    // ===== Print Function =====
    function printComparison() {
        const startDate = document.getElementById('startDate').value || '{{ $startDate }}';
        const endDate = document.getElementById('endDate').value || '{{ $endDate }}';
        const salesCats = [...document.querySelectorAll('.salesCatCb:checked')].map(cb => cb.value).join(',');
        const issueActions = [...document.querySelectorAll('.issuesActionCb:checked')].map(cb => cb.value).join(',');
        window.open(`/kitchen/comparison/print?start_date=${startDate}&end_date=${endDate}&sales_categories=${encodeURIComponent(salesCats)}&issue_actions=${encodeURIComponent(issueActions)}`, '_blank');
    }

    // ===== Consumption Loader =====
    function loadConsumption() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const consumptionData = document.getElementById('consumptionData');
        consumptionData.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"></div><p class="mt-2 mb-0">Loading consumption data...</p></div>';
        
        fetch(`/recipes/consumption?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.consumption && data.consumption.length > 0) {
                    let html = `<div class="row mb-3">
                        <div class="col-md-4"><div class="text-center p-3 bg-light rounded"><h6 class="text-muted">Total Cost</h6><h4 class="mb-0 text-danger">Rs ${parseFloat(data.total_cost).toLocaleString('en-US', {minimumFractionDigits: 2})}</h4></div></div>
                        <div class="col-md-4"><div class="text-center p-3 bg-light rounded"><h6 class="text-muted">Items Consumed</h6><h4 class="mb-0 text-info">${data.consumption.length}</h4></div></div>
                        <div class="col-md-4"><div class="text-center p-3 bg-light rounded"><h6 class="text-muted">Date Range</h6><h6 class="mb-0 text-primary">${startDate === endDate ? new Date(startDate).toLocaleDateString() : new Date(startDate).toLocaleDateString() + ' - ' + new Date(endDate).toLocaleDateString()}</h6></div></div>
                    </div>
                    <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-dark"><tr><th>Ingredient</th><th>Category</th><th>Total Consumed</th><th>Cost/Unit</th><th>Total Cost</th><th>Usage %</th></tr></thead><tbody>`;
                    data.consumption.forEach(item => {
                        const totalCost = parseFloat(item.total_cost);
                        const pct = data.total_cost > 0 ? ((totalCost / data.total_cost) * 100).toFixed(1) : 0;
                        html += `<tr><td><strong>${item.item_name}</strong></td><td><span class="badge bg-secondary">${item.category_name || 'Uncategorized'}</span></td><td>${parseFloat(item.total_consumed).toFixed(2)} ${item.kitchen_unit}</td><td>Rs ${parseFloat(item.kitchen_cost_per_unit || 0).toFixed(2)}</td><td><strong>Rs ${totalCost.toFixed(2)}</strong></td><td><div class="progress" style="height:15px;"><div class="progress-bar bg-warning" style="width:${pct}%">${pct}%</div></div></td></tr>`;
                    });
                    html += '</tbody></table></div>';
                    consumptionData.innerHTML = html;
                } else {
                    const dateText = startDate === endDate ? new Date(startDate).toLocaleDateString() : `${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}`;
                    consumptionData.innerHTML = `<div class="text-center p-4"><i class="fas fa-utensils text-muted" style="font-size:3rem;"></i><p class="mt-3 mb-2 text-muted">No kitchen consumption recorded for ${dateText}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error loading consumption data:', error);
                consumptionData.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-triangle text-danger" style="font-size:2rem;"></i><p class="mt-2 mb-0 text-danger">Error loading consumption data</p></div>';
            });
    }

    // ===== Date Validation =====
    function validateDates() {
        const s = document.getElementById('startDate').value;
        const e = document.getElementById('endDate').value;
        if (s && e && new Date(s) > new Date(e)) {
            alert('Start date cannot be after end date');
            document.getElementById('endDate').value = s;
        }
    }

    function setEndDate(date) {
        document.querySelector('input[name="end_date"]').value = date;
    }

    // ===== Init =====
    document.addEventListener('DOMContentLoaded', function() {
        filterIssueActions();
        loadConsumption();
    });

    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $('#exportBtn').click(function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            window.location.href = `/kitchen/comparison/export?start_date=${startDate}&end_date=${endDate}&format=csv`;
        });
    });
    </script>
    @endsection