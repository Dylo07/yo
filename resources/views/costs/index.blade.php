@extends('layouts.app')

@section('styles')
<style>
/* ── Modern Expense Dashboard Theme ── */
:root {
    --exp-bg: #f0f2f7;
    --exp-card: #ffffff;
    --exp-primary: #4361ee;
    --exp-success: #2ec4b6;
    --exp-warning: #f7b731;
    --exp-danger: #e55353;
    --exp-purple: #7c3aed;
    --exp-dark: #1e2a3a;
    --exp-muted: #6b7a99;
    --exp-border: #e4e9f2;
    --exp-radius: 14px;
    --exp-shadow: 0 2px 16px rgba(67,97,238,.08);
}

body { background: var(--exp-bg) !important; }

.exp-page { padding: 0 0 2rem 0; }

/* Header */
.exp-header {
    background: linear-gradient(135deg, #4361ee 0%, #7c3aed 100%);
    border-radius: var(--exp-radius);
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
}
.exp-header h1 { font-size: 1.6rem; font-weight: 700; margin: 0; letter-spacing: -.3px; }
.exp-header .subtitle { font-size: .85rem; opacity: .8; margin-top: 2px; }
.exp-header-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.exp-header-actions .btn { border-radius: 8px; font-size: .82rem; font-weight: 600; padding: .38rem .9rem; }

/* Filter Bar */
.exp-filter-bar {
    background: var(--exp-card);
    border-radius: var(--exp-radius);
    padding: 1.1rem 1.5rem;
    box-shadow: var(--exp-shadow);
    margin-bottom: 1.5rem;
}
.exp-filter-bar .form-control, .exp-filter-bar .form-select {
    border-radius: 8px;
    border: 1.5px solid var(--exp-border);
    font-size: .88rem;
    background: #f8f9fc;
}
.exp-filter-bar .form-label { font-size: .78rem; font-weight: 600; color: var(--exp-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.exp-filter-bar .btn-apply { background: var(--exp-primary); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: .88rem; }
.exp-filter-bar .btn-apply:hover { background: #3451d1; }
.exp-quick-btns .btn { border-radius: 8px; font-size: .78rem; font-weight: 600; border: 1.5px solid var(--exp-border); color: var(--exp-dark); background: #f8f9fc; }
.exp-quick-btns .btn:hover { background: var(--exp-primary); color: #fff; border-color: var(--exp-primary); }

/* KPI Cards */
.exp-kpi {
    background: var(--exp-card);
    border-radius: var(--exp-radius);
    padding: 1.2rem 1.4rem;
    box-shadow: var(--exp-shadow);
    border-left: 4px solid transparent;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.exp-kpi::after {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: .07;
    background: currentColor;
}
.exp-kpi.kpi-blue  { border-left-color: var(--exp-primary); color: var(--exp-primary); }
.exp-kpi.kpi-green { border-left-color: var(--exp-success); color: var(--exp-success); }
.exp-kpi.kpi-orange{ border-left-color: var(--exp-warning); color: var(--exp-warning); }
.exp-kpi.kpi-purple{ border-left-color: var(--exp-purple);  color: var(--exp-purple); }
.exp-kpi .kpi-icon { font-size: 1.8rem; margin-bottom: .4rem; }
.exp-kpi .kpi-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--exp-muted); }
.exp-kpi .kpi-value { font-size: 1.45rem; font-weight: 800; color: var(--exp-dark); line-height: 1.2; margin: .2rem 0; }
.exp-kpi .kpi-sub { font-size: .78rem; color: var(--exp-muted); }
.exp-kpi .kpi-trend { font-size: .82rem; font-weight: 700; }
.kpi-trend.up   { color: #e55353; }
.kpi-trend.down { color: #2ec4b6; }

/* Section Cards */
.exp-section {
    background: var(--exp-card);
    border-radius: var(--exp-radius);
    box-shadow: var(--exp-shadow);
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.exp-section-header {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1.5px solid var(--exp-border);
    background: #fafbff;
}
.exp-section-header h3 { font-size: 1rem; font-weight: 700; color: var(--exp-dark); margin: 0; }
.exp-section-header .badge-count { background: var(--exp-primary); color: #fff; border-radius: 20px; padding: 2px 10px; font-size: .75rem; font-weight: 700; }
.exp-section-body { padding: 1.2rem 1.5rem; }

/* Category Totals Grid */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: .75rem; }
.cat-tile {
    background: #f8f9fc;
    border-radius: 10px;
    padding: .85rem 1rem;
    border: 1.5px solid var(--exp-border);
    transition: all .2s;
}
.cat-tile:hover { border-color: var(--exp-primary); background: #eef1ff; transform: translateY(-2px); }
.cat-tile .cat-name { font-size: .82rem; font-weight: 700; color: var(--exp-dark); margin-bottom: 2px; }
.cat-tile .cat-amount { font-size: 1.05rem; font-weight: 800; color: var(--exp-primary); }
.cat-tile .cat-count { font-size: .72rem; color: var(--exp-muted); }

/* Monthly Table */
.exp-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.exp-table th { background: #f0f2f7; color: var(--exp-muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: .65rem 1rem; border-bottom: 2px solid var(--exp-border); }
.exp-table td { padding: .6rem 1rem; border-bottom: 1px solid var(--exp-border); color: var(--exp-dark); vertical-align: middle; }
.exp-table tr.cat-header { background: #eef1ff; cursor: pointer; }
.exp-table tr.cat-header:hover { background: #e0e6ff; }
.exp-table tr.cat-header td { font-weight: 700; color: var(--exp-primary); font-size: .88rem; }
.exp-table tr.person-row { background: #fafbff; }
.exp-table tr.person-row td { font-weight: 600; color: var(--exp-dark); font-size: .83rem; }
.exp-table tr.detail-row td { color: #4a5568; }
.exp-table tr.subtotal-row { background: #f0fdf8; }
.exp-table tr.subtotal-row td { font-weight: 700; color: var(--exp-success); }
.exp-table tr.grand-total-row { background: linear-gradient(90deg,#eef1ff,#f0fdf8); }
.exp-table tr.grand-total-row td { font-weight: 800; font-size: .95rem; color: var(--exp-dark); border-top: 2px solid var(--exp-border); }
.toggle-icon { float: right; transition: transform .2s; font-size: .8rem; }
.collapsed-row .toggle-icon { transform: rotate(180deg); }

/* Daily Summary */
.daily-total-banner {
    background: linear-gradient(135deg, #2ec4b6, #1a9e93);
    border-radius: 10px;
    padding: .9rem 1.3rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.daily-total-banner .dt-label { font-size: .8rem; font-weight: 700; opacity: .85; text-transform: uppercase; letter-spacing: .5px; }
.daily-total-banner .dt-value { font-size: 1.6rem; font-weight: 800; }

.daily-cat-header { font-size: .88rem; font-weight: 700; color: var(--exp-dark); padding: .5rem 0 .3rem; border-bottom: 2px solid var(--exp-primary); margin-bottom: .5rem; display: flex; align-items: center; gap: .4rem; }
.daily-cat-header .cat-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--exp-primary); display: inline-block; }

/* Add Expense Modal */
.exp-modal .modal-content { border-radius: var(--exp-radius); border: none; box-shadow: 0 8px 40px rgba(67,97,238,.18); }
.exp-modal .modal-header { background: linear-gradient(135deg, #4361ee, #7c3aed); color: #fff; border-radius: var(--exp-radius) var(--exp-radius) 0 0; border: none; padding: 1.1rem 1.5rem; }
.exp-modal .modal-header .btn-close { filter: invert(1); }
.exp-modal .modal-body { padding: 1.5rem; }
.exp-modal .form-label { font-size: .8rem; font-weight: 700; color: var(--exp-muted); text-transform: uppercase; letter-spacing: .4px; }
.exp-modal .form-control, .exp-modal .form-select { border-radius: 8px; border: 1.5px solid var(--exp-border); font-size: .9rem; }
.exp-modal .form-control:focus, .exp-modal .form-select:focus { border-color: var(--exp-primary); box-shadow: 0 0 0 3px rgba(67,97,238,.12); }
.exp-modal .btn-submit { background: linear-gradient(135deg, #4361ee, #7c3aed); color: #fff; border: none; border-radius: 8px; font-weight: 700; padding: .55rem 1.5rem; }

/* Search box */
.exp-search { border-radius: 8px; border: 1.5px solid var(--exp-border); font-size: .88rem; background: #f8f9fc; padding: .5rem 1rem .5rem 2.4rem; width: 100%; }
.exp-search-wrap { position: relative; }
.exp-search-wrap .search-icon { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--exp-muted); font-size: .9rem; }

/* Charts */
.chart-tabs .nav-link { font-size: .82rem; font-weight: 600; color: var(--exp-muted); border-radius: 8px 8px 0 0; padding: .45rem 1rem; }
.chart-tabs .nav-link.active { color: var(--exp-primary); background: #eef1ff; border-bottom: 2px solid var(--exp-primary); }

/* Responsive */
@@media(max-width:768px) {
    .exp-header { flex-direction: column; align-items: flex-start; }
    .exp-kpi .kpi-value { font-size: 1.15rem; }
}

/* Collapse */
.collapse { display: none; }
.collapse.show { display: table-row; }
.clickable { cursor: pointer; }
.cat-row-arrow { display:inline-block; transition: transform .2s; font-size:.8rem; color:#6b7a99; }
.collapsed .cat-row-arrow { transform: rotate(-90deg); }
</style>
@endsection

@section('content')
<div class="exp-page container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="exp-header">
        <div>
            <h1><i class="bi bi-wallet2 me-2"></i>Hotel Expenses</h1>
            <div class="subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} &mdash; Financial Overview</div>
        </div>
        <div class="exp-header-actions">
            <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-circle me-1"></i>Add Expense
            </button>
            <a href="{{ route('groups.create') }}" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-tag me-1"></i>Add Category</a>
            <a href="{{ route('persons.create') }}" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-person-plus me-1"></i>Add Person/Shop</a>
            <a href="{{ route('costs.export', ['month' => $month]) }}" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-download me-1"></i>Export CSV</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Month Filter Bar -->
    <div class="exp-filter-bar">
        <form action="{{ route('costs.index') }}" method="GET" id="filterForm">
            <input type="hidden" name="date" id="dateHidden" value="{{ $selectedDate }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-calendar-month me-1"></i>Month</label>
                    <input type="month" name="month" id="month" class="form-control" value="{{ $month }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-apply w-100"><i class="bi bi-search me-1"></i>Apply</button>
                </div>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    @php
        $trend = $analytics['trend_percentage'];
        $trendUp = $trend >= 0;
        $highestCost = $monthlyCosts->sortByDesc('amount')->first();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="exp-kpi kpi-blue">
                <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
                <div class="kpi-label">Total Expenses</div>
                <div class="kpi-value">Rs. {{ number_format($analytics['total_amount'], 2) }}</div>
                <div class="kpi-trend {{ $trendUp ? 'up' : 'down' }}">{{ $trendUp ? '↑' : '↓' }} {{ abs($trend) }}% vs last month</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="exp-kpi kpi-green">
                <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
                <div class="kpi-label">Transactions</div>
                <div class="kpi-value">{{ $analytics['total_transactions'] }}</div>
                <div class="kpi-sub">Avg. Rs. {{ number_format($analytics['avg_transaction'], 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="exp-kpi kpi-orange">
                <div class="kpi-icon"><i class="bi bi-bar-chart-fill"></i></div>
                <div class="kpi-label">Top Category</div>
                <div class="kpi-value" style="font-size:1.05rem;">{{ $analytics['top_category']['name'] }}</div>
                @if($analytics['top_category']['total'] > 0)
                    <div class="kpi-sub">Rs. {{ number_format($analytics['top_category']['total'], 2) }} &bull; {{ $analytics['top_category']['count'] }} txns</div>
                @else
                    <div class="kpi-sub">No expenses</div>
                @endif
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="exp-kpi kpi-purple">
                <div class="kpi-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="kpi-label">Highest Single</div>
                @if($highestCost)
                    <div class="kpi-value">Rs. {{ number_format($highestCost->amount, 2) }}</div>
                    <div class="kpi-sub">{{ $highestCost->group->name ?? '-' }} &mdash; {{ $highestCost->cost_date->format('M d') }}</div>
                @else
                    <div class="kpi-value">Rs. 0.00</div>
                    <div class="kpi-sub">No expenses</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="exp-section h-100">
                <div class="exp-section-header">
                    <h3><i class="bi bi-pie-chart me-2"></i>Category Distribution</h3>
                </div>
                <div class="exp-section-body">
                    <canvas id="categoryChart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="exp-section h-100">
                <div class="exp-section-header">
                    <h3><i class="bi bi-bar-chart me-2"></i>Daily Spending</h3>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary active" onclick="toggleChart('stacked')" id="btnStacked" style="font-size:.75rem;">By Category</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleChart('compare')" id="btnCompare" style="font-size:.75rem;">vs Last Month</button>
                    </div>
                </div>
                <div class="exp-section-body">
                    <canvas id="stackedChart" height="260"></canvas>
                    <canvas id="compareChart" height="260" style="display:none;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Totals Grid -->
    <div class="exp-section mb-4">
        <div class="exp-section-header">
            <h3><i class="bi bi-grid me-2"></i>Category Totals</h3>
            <span class="badge-count">{{ count($analytics['category_breakdown']) }}</span>
        </div>
        <div class="exp-section-body">
            <div class="cat-grid">
                @foreach($analytics['category_breakdown'] as $category)
                <div class="cat-tile">
                    <div class="cat-name">{{ $category['name'] }}</div>
                    <div class="cat-amount">Rs. {{ number_format($category['total'], 2) }}</div>
                    <div class="cat-count">{{ $category['count'] }} transactions</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Summary of Expenses of the Month -->
    <div class="exp-section mb-4">
        <div class="exp-section-header">
            <h3><i class="bi bi-table me-2"></i>Summary — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h3>
        </div>
        <div class="exp-section-body">
            <div class="exp-search-wrap mb-3">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="monthlySummarySearch" class="exp-search" placeholder="Search by category, person/shop, or description..." oninput="filterMonthlySummary(this.value)">
            </div>
            <table class="exp-table" id="monthlySummaryTable">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Person/Shop</th>
                        <th>Description</th>
                        <th>Expense</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                @php $groupIndex = 0; @endphp
                @foreach ($monthlyGroupedCosts as $group => $persons)
                    <!-- Category Row -->
                    @php
                        $catTotal = collect($persons)->sum('total');
                    @endphp
                    <tr data-toggle="collapse" data-target=".group-{{ $groupIndex }}" class="clickable collapsed table-secondary summary-cat-row" data-category="{{ strtolower($group) }}">
                        <td><strong>{{ $group }}</strong></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight:600;color:#1e2a3a;">Rs. {{ number_format($catTotal, 2) }}</td>
                        <td></td>
                        <td style="text-align:right;"><span class="cat-row-arrow">&#x25BC;</span></td>
                    </tr>
                    @foreach ($persons as $person => $data)
                        <!-- Person/Shop Row -->
                        <tr class="collapse group-{{ $groupIndex }} table-light">
                            <td></td>
                            <td colspan="5"><strong>{{ $person }}</strong></td>
                        </tr>
                        @foreach ($data['costs'] as $cost)
                            <!-- Expense Row -->
                            <tr class="collapse group-{{ $groupIndex }}">
                                <td></td>
                                <td></td>
                                <td>{{ $cost->description ?: '-' }}</td>
                                <td>Rs. {{ number_format($cost->amount, 2) }}</td>
                                <td>{{ $cost->cost_date->format('M d, Y') }}</td>
                                <td>{{ $cost->created_at ? $cost->created_at->format('h:i A') : '-' }}</td>
                            </tr>
                        @endforeach
                        <!-- Total for Person/Shop -->
                        <tr class="collapse group-{{ $groupIndex }} table-info">
                            <td></td>
                            <td colspan="2" class="text-end"><strong>Total for {{ $person }}</strong></td>
                            <td><strong>Rs. {{ number_format($data['total'], 2) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    @endforeach
                    @php $groupIndex++; @endphp
                @endforeach
                <!-- Grand Total -->
                <tr class="grand-total-row">
                    <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                    <td><strong>Rs. {{ number_format($grandTotal, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Summary Section -->
    <div class="exp-section mb-4">
        <div class="exp-section-header" style="flex-wrap:wrap; gap:.75rem;">
            <h3><i class="bi bi-calendar-check me-2"></i>Daily — {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</h3>
            <form action="{{ route('costs.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap" style="margin:0;">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="date" name="date" id="dailyDate" class="form-control form-control-sm" value="{{ $selectedDate }}" style="width:160px;">
                <button type="submit" class="btn btn-sm btn-apply" style="white-space:nowrap;"><i class="bi bi-search me-1"></i>Go</button>
                <div class="d-flex gap-1 exp-quick-btns">
                    <button type="button" class="btn btn-sm" onclick="setDailyDate('{{ now()->toDateString() }}')">Today</button>
                    <button type="button" class="btn btn-sm" onclick="setDailyDate('{{ now()->subDay()->toDateString() }}')">Yesterday</button>
                </div>
            </form>
            <a href="{{ route('costs.print.daily', ['date' => $selectedDate]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Print Daily
            </a>
        </div>
        <div class="exp-section-body">
            @php $dailyTotal = $dailyGroupedCosts->flatten(1)->sum('total'); @endphp
            <div class="daily-total-banner">
                <div>
                    <div class="dt-label">Total for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</div>
                    <div class="dt-value">Rs. {{ number_format($dailyTotal, 2) }}</div>
                </div>
                <i class="bi bi-cash-stack" style="font-size:2rem; opacity:.6;"></i>
            </div>
            @if ($dailyGroupedCosts->isEmpty())
                <div class="text-center py-4" style="color:var(--exp-muted);">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2">No expenses found for this date.</p>
                </div>
            @else
                @foreach ($dailyGroupedCosts as $group => $persons)
                    <div class="daily-cat-header">
                        <span class="cat-dot"></span>{{ $group }}
                    </div>
                    <table class="exp-table mb-4">
                        <thead>
                            <tr>
                                <th>Person/Shop</th>
                                <th>Description</th>
                                <th>Expense</th>
                                <th>Time</th>
                                <th>Added By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($persons as $person => $data)
                                <tr class="person-row">
                                    <td colspan="6"><i class="bi bi-person me-1"></i>{{ $person }}</td>
                                </tr>
                                @foreach ($data['costs'] as $cost)
                                    <tr class="detail-row">
                                        <td>{{ $person }}</td>
                                        <td>{{ $cost->description ?? '-' }}</td>
                                        <td>Rs. {{ number_format($cost->amount, 2) }}</td>
                                        <td>{{ $cost->created_at->format('h:i A') }}</td>
                                        <td>{{ $cost->user?->name ?? 'System' }}</td>
                                        <td>
                                            <a href="{{ route('costs.print.transaction', $cost) }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;" title="Print">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="subtotal-row">
                                    <td colspan="3" class="text-end">Total for {{ $person }}</td>
                                    <td>Rs. {{ number_format($data['total'], 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>
    </div>

</div>

<!-- Add Expense Modal -->
<div class="modal fade exp-modal" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('costs.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">Select category...</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Person / Shop</label>
                        <select name="person_id" class="form-select" required>
                            <option value="">Select person/shop...</option>
                            @php
                                $staffIds = \App\Models\StaffCode::where('is_active', 1)->pluck('person_id')->toArray();
                                $staffPersons = $allPersons->filter(fn($p) => in_array($p->id, $staffIds));
                                $otherPersons = $allPersons->filter(fn($p) => !in_array($p->id, $staffIds));
                            @endphp
                            @if($staffPersons->count() > 0)
                                <optgroup label="── Staff Members ──">
                                    @foreach($staffPersons as $personModel)
                                        <option value="{{ $personModel->id }}">{{ $personModel->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if($otherPersons->count() > 0)
                                <optgroup label="── Others (Shops/Suppliers) ──">
                                    @foreach($otherPersons as $personModel)
                                        <option value="{{ $personModel->id }}">{{ $personModel->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (Rs.)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="cost_date" class="form-control" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" readonly style="background:#f0f2f7;cursor:not-allowed;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Notes..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const CHART_COLORS = ['#4361ee','#7c3aed','#2ec4b6','#f7b731','#e55353','#10b981','#3b82f6','#ec4899','#f59e0b','#6366f1'];

document.addEventListener('DOMContentLoaded', function() {
    initCategoryChart();
    initStackedChart();
    initCompareChart();
    initializeCollapse();
});

function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['categoryDistribution']->pluck('category')) !!},
            datasets: [{ data: {!! json_encode($chartData['categoryDistribution']->pluck('total')) !!}, backgroundColor: CHART_COLORS, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: function(c) {
                    const total = c.dataset.data.reduce((a,b)=>a+b,0);
                    return ` Rs. ${c.raw.toLocaleString()} (${((c.raw/total)*100).toFixed(1)}%)`;
                }}}
            }
        }
    });
}

let stackedChartObj = null;
let compareChartObj = null;

function initStackedChart() {
    const ctx = document.getElementById('stackedChart');
    if (!ctx) return;
    const dates = {!! json_encode($chartData['stackedDates'] ?? []) !!};
    const cats  = {!! json_encode(array_keys($chartData['stackedData'] ?? [])) !!};
    const data  = {!! json_encode($chartData['stackedData'] ?? []) !!};
    const datasets = cats.map((cat, i) => ({
        label: cat,
        data: data[cat] || [],
        backgroundColor: CHART_COLORS[i % CHART_COLORS.length],
        borderRadius: 3,
    }));
    stackedChartObj = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: { labels: dates, datasets: datasets },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true, ticks: { font: { size: 10 } } },
                y: { stacked: true, beginAtZero: true, ticks: { callback: v => 'Rs.'+v.toLocaleString(), font: { size: 10 } } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10 } },
                tooltip: { callbacks: { label: c => ` ${c.dataset.label}: Rs. ${c.raw.toLocaleString()}` } }
            }
        }
    });
}

function initCompareChart() {
    const ctx = document.getElementById('compareChart');
    if (!ctx) return;
    const curr = {!! json_encode($chartData['currentMonthDailyTotals'] ?? []) !!};
    const prev = {!! json_encode($chartData['prevMonthDailyTotals'] ?? []) !!};
    const labels = Array.from({length: Math.max(curr.length, prev.length)}, (_, i) => 'Day '+(i+1));
    compareChartObj = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'This Month', data: curr, borderColor: '#4361ee', backgroundColor: 'rgba(67,97,238,.1)', tension: .4, fill: true, pointRadius: 3 },
                { label: 'Last Month', data: prev, borderColor: '#e55353', backgroundColor: 'rgba(229,83,83,.07)', tension: .4, fill: true, borderDash: [5,3], pointRadius: 3 }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rs.'+v.toLocaleString(), font: { size: 10 } } },
                x: { ticks: { font: { size: 10 } } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: c => ` ${c.dataset.label}: Rs. ${c.raw.toLocaleString()}` } }
            }
        }
    });
}

function toggleChart(type) {
    document.getElementById('stackedChart').style.display  = type === 'stacked'  ? '' : 'none';
    document.getElementById('compareChart').style.display  = type === 'compare'  ? '' : 'none';
    document.getElementById('btnStacked').classList.toggle('active', type === 'stacked');
    document.getElementById('btnCompare').classList.toggle('active', type === 'compare');
}

function initializeCollapse() {
    document.querySelectorAll('.clickable').forEach(function(el) {
        el.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            this.classList.toggle('collapsed');
            document.querySelectorAll(target).forEach(function(t) { t.classList.toggle('show'); });
        });
    });
}

function setDailyDate(date) {
    document.getElementById('dailyDate').value = date;
    document.getElementById('dailyDate').closest('form').submit();
}

function filterMonthlySummary(query) {
    const q = query.toLowerCase().trim();
    const table = document.getElementById('monthlySummaryTable');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    if (q === '') {
        rows.forEach(function(row) {
            row.style.display = '';
            if (row.classList.contains('collapse') && !row.classList.contains('show')) row.style.display = 'none';
        });
        return;
    }
    table.querySelectorAll('tr.summary-cat-row').forEach(function(catRow) {
        const target = catRow.getAttribute('data-target');
        const catName = (catRow.getAttribute('data-category') || '').toLowerCase();
        const detailRows = target ? table.querySelectorAll(target) : [];
        let catMatches = catName.includes(q);
        let anyMatch = false;
        detailRows.forEach(function(dRow) {
            if (dRow.textContent.toLowerCase().includes(q)) { anyMatch = true; dRow.style.display = ''; }
            else dRow.style.display = 'none';
        });
        if (catMatches || anyMatch) { catRow.style.display = ''; detailRows.forEach(d => { if (d.style.display !== 'none') d.style.display = ''; }); }
        else { catRow.style.display = 'none'; detailRows.forEach(d => d.style.display = 'none'); }
    });
}
</script>
@endpush

