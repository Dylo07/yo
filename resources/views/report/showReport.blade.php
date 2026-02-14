@extends('layouts.app')

@section('content')
<style>
  .report-page { background: #f0f2f5; min-height: 100vh; padding-bottom: 3rem; }
  .report-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 1.5rem 0; margin-bottom: 2rem; border-radius: 0 0 1.5rem 1.5rem; }
  .report-header h2 { font-weight: 700; font-size: 1.5rem; margin: 0; }
  .report-header .breadcrumb { background: none; margin: 0; padding: 0; }
  .report-header .breadcrumb-item a { color: rgba(255,255,255,0.7); text-decoration: none; }
  .report-header .breadcrumb-item a:hover { color: #fff; }
  .report-header .breadcrumb-item.active { color: rgba(255,255,255,0.9); }
  .report-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.5); }

  .stat-card { border-radius: 1rem; padding: 1.25rem; color: #fff; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s; }
  .stat-card:hover { transform: translateY(-3px); }
  .stat-card .stat-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: 0.2; }
  .stat-card .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; margin-bottom: 0.25rem; }
  .stat-card .stat-value { font-size: 1.5rem; font-weight: 700; }
  .stat-card .stat-sub { font-size: 0.7rem; opacity: 0.7; margin-top: 0.25rem; }
  .stat-card.bg-gradient-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stat-card.bg-gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
  .stat-card.bg-gradient-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-card.bg-gradient-teal { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

  .toolbar { background: #fff; border-radius: 1rem; padding: 1rem 1.25rem; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 1.5rem; }
  .toolbar .search-box { border-radius: 2rem; border: 1px solid #e0e0e0; padding: 0.5rem 1rem 0.5rem 2.5rem; width: 100%; font-size: 0.9rem; transition: border-color 0.2s; }
  .toolbar .search-box:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
  .toolbar .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #aaa; }
  .toolbar .filter-btn { border-radius: 2rem; font-size: 0.8rem; padding: 0.4rem 1rem; }
  .toolbar .filter-btn.active { background: #667eea; color: #fff; border-color: #667eea; }

  .sale-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1rem; overflow: hidden; border: 1px solid #eee; transition: box-shadow 0.2s; }
  .sale-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
  .sale-card.cancelled { border-left: 4px solid #dc3545; opacity: 0.75; }
  .sale-card.active-sale { border-left: 4px solid #667eea; }

  .sale-card-header { padding: 1rem 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 1rem; transition: background 0.15s; }
  .sale-card-header:hover { background: #f8f9ff; }
  .sale-card-header .sale-id { font-weight: 700; font-size: 1rem; color: #1e3c72; }
  .sale-card-header .sale-meta { display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap; }
  .sale-card-header .meta-item { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #666; }
  .sale-card-header .meta-item i { color: #999; font-size: 0.75rem; }
  .sale-card-header .sale-amount { font-weight: 700; font-size: 1.1rem; color: #11998e; }
  .sale-card-header .cancelled-amount { color: #dc3545; text-decoration: line-through; }
  .sale-card-header .chevron { transition: transform 0.3s; color: #ccc; }
  .sale-card-header[aria-expanded="true"] .chevron { transform: rotate(180deg); }

  .sale-card-body { border-top: 1px solid #f0f0f0; }
  .detail-table { width: 100%; margin: 0; }
  .detail-table thead th { background: #f8f9fa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; padding: 0.6rem 1rem; border: none; font-weight: 600; }
  .detail-table tbody td { padding: 0.65rem 1rem; font-size: 0.85rem; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
  .detail-table tbody tr:last-child td { border-bottom: none; }
  .detail-table .item-name { font-weight: 600; color: #333; }
  .detail-table .item-qty { background: #eef2ff; color: #667eea; font-weight: 700; border-radius: 0.5rem; padding: 0.2rem 0.6rem; display: inline-block; text-align: center; min-width: 2rem; }
  .detail-table .item-total { font-weight: 600; color: #333; }

  .badge-cancelled { background: #dc3545; color: #fff; font-size: 0.65rem; padding: 0.25rem 0.6rem; border-radius: 2rem; font-weight: 600; letter-spacing: 0.5px; }
  .badge-paid { background: #11998e; color: #fff; font-size: 0.65rem; padding: 0.25rem 0.6rem; border-radius: 2rem; font-weight: 600; letter-spacing: 0.5px; }

  .btn-cancel-bill { border-radius: 2rem; font-size: 0.75rem; padding: 0.3rem 0.8rem; font-weight: 600; }
  .btn-void-item { border-radius: 2rem; font-size: 0.7rem; padding: 0.2rem 0.6rem; font-weight: 600; }

  .summary-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 1.5rem; margin-top: 1rem; }
  .summary-section h5 { font-weight: 700; color: #1e3c72; margin-bottom: 1rem; }
  .summary-cat-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; margin-top: 1rem; margin-bottom: 0.5rem; }
  .summary-table { width: 100%; }
  .summary-table th { font-size: 0.75rem; text-transform: uppercase; color: #888; padding: 0.5rem 1rem; border-bottom: 2px solid #eee; }
  .summary-table td { padding: 0.5rem 1rem; font-size: 0.85rem; border-bottom: 1px solid #f5f5f5; }
  .summary-table .qty-badge { background: #eef2ff; color: #667eea; font-weight: 700; border-radius: 0.5rem; padding: 0.15rem 0.5rem; }

  .pagination-wrapper { margin-top: 1.5rem; }
  .pagination-wrapper .page-link { border-radius: 0.5rem; margin: 0 0.15rem; border: none; color: #667eea; }
  .pagination-wrapper .page-item.active .page-link { background: #667eea; color: #fff; }

  .export-bar { background: #fff; border-radius: 1rem; padding: 1rem 1.25rem; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
  .btn-export { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; border: none; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; font-size: 0.85rem; transition: transform 0.2s, box-shadow 0.2s; }
  .btn-export:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(17,153,142,0.3); color: #fff; }
  .btn-back-report { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; }
  .btn-back-report:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(102,126,234,0.3); color: #fff; }

  .empty-state { text-align: center; padding: 4rem 2rem; }
  .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 1rem; }
  .empty-state h4 { color: #999; font-weight: 600; }
  .empty-state p { color: #bbb; }

  .sale-footer { padding: 0.75rem 1.25rem; background: #fafbfc; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f0f0f0; }
  .sale-footer .footer-label { font-size: 0.75rem; color: #999; }
  .sale-footer .footer-value { font-weight: 700; font-size: 0.9rem; }

  @media (max-width: 768px) {
    .stat-card .stat-value { font-size: 1.2rem; }
    .sale-card-header .sale-meta { gap: 0.75rem; }
    .sale-card-header { flex-wrap: wrap; }
  }
</style>

<div class="report-page">
  {{-- Header --}}
  <div class="report-header">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
          <li class="breadcrumb-item"><a href="/home"><i class="fas fa-home"></i> Home</a></li>
          <li class="breadcrumb-item"><a href="/report">Report</a></li>
          <li class="breadcrumb-item active">Sales Report</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2><i class="fas fa-chart-line me-2"></i>Sales Report</h2>
        <div style="font-size:0.85rem; opacity:0.8;">
          <i class="fas fa-calendar-alt me-1"></i> {{$dateStart}} &mdash; {{$dateEnd}}
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{$error}}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($sales->count() > 0)
      @php
        $paidCount = $sales->where('sale_status', 'paid')->count();
        $cancelledCount = $sales->where('sale_status', 'cancelled')->count();
      @endphp

      {{-- Total Value Card --}}
      @php $totalValue = $totalSale + $serviceCharge; @endphp
      <div class="row g-3 mb-3">
        <div class="col-12">
          <div class="stat-card" style="background:linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding:1.5rem 1.5rem;">
            <div class="stat-icon" style="font-size:3rem;"><i class="fas fa-coins"></i></div>
            <div class="stat-label" style="font-size:0.8rem;">Total Value (Bill Amount + S/C)</div>
            <div class="stat-value" style="font-size:2rem;">Rs {{ number_format($totalValue, 2) }}</div>
            <div class="stat-sub">Rs {{ number_format($totalSale, 2) }} (Bills) + Rs {{ number_format($serviceCharge, 2) }} (S/C)</div>
          </div>
        </div>
      </div>

      {{-- Stat Cards --}}
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="stat-card bg-gradient-blue">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-label">Total Bill Amount</div>
            <div class="stat-value">Rs {{ number_format($totalSale, 2) }}</div>
            <div class="stat-sub">{{ $paidCount }} paid bills</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card bg-gradient-green">
            <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-label">Service Charge (S/C)</div>
            <div class="stat-value">Rs {{ number_format($serviceCharge, 2) }}</div>
            <div class="stat-sub">From paid bills</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card bg-gradient-teal">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-label">Total Bills</div>
            <div class="stat-value">{{ $sales->total() }}</div>
            <div class="stat-sub">{{ $paidCount }} paid / {{ $cancelledCount }} cancelled</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card bg-gradient-orange">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-label">Cancelled</div>
            <div class="stat-value">{{ $cancelledCount }}</div>
            <div class="stat-sub">{{ $sales->total() > 0 ? round(($cancelledCount / $sales->total()) * 100, 1) : 0 }}% of total</div>
          </div>
        </div>
      </div>

      {{-- Toolbar --}}
      <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="position-relative" style="flex:1; max-width:350px;">
          <i class="fas fa-search search-icon"></i>
          <input type="text" class="search-box" id="saleSearch" placeholder="Search by receipt ID, table, staff...">
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-secondary filter-btn active" data-filter="all"><i class="fas fa-list me-1"></i>All</button>
          <button class="btn btn-outline-secondary filter-btn" data-filter="paid"><i class="fas fa-check-circle me-1"></i>Paid</button>
          <button class="btn btn-outline-secondary filter-btn" data-filter="cancelled"><i class="fas fa-ban me-1"></i>Cancelled</button>
          <button class="btn btn-outline-primary filter-btn" id="toggleAllBtn" title="Expand / Collapse All"><i class="fas fa-expand-alt"></i></button>
        </div>
      </div>

      {{-- Sale Cards --}}
      @php $countSale = ($sales->currentPage() - 1) * $sales->perPage() + 1; @endphp
      <div id="salesContainer">
        @foreach($sales as $sale)
          <div class="sale-card {{ $sale->sale_status === 'cancelled' ? 'cancelled' : 'active-sale' }}" data-status="{{ $sale->sale_status }}" data-search="{{ strtolower($sale->id . ' ' . $sale->table_name . ' ' . $sale->user_name) }}" id="sale-row-{{$sale->id}}">
            <div class="sale-card-header" data-bs-toggle="collapse" data-bs-target="#saleBody{{$sale->id}}" aria-expanded="false">
              <div class="d-flex align-items-center gap-3 flex-wrap" style="flex:1;">
                <div>
                  <span class="sale-id">#{{ $sale->id }}</span>
                  @if($sale->sale_status === 'cancelled')
                    <span class="badge-cancelled ms-1">CANCELLED</span>
                  @else
                    <span class="badge-paid ms-1">PAID</span>
                  @endif
                </div>
                <div class="sale-meta">
                  <span class="meta-item"><i class="fas fa-calendar"></i> {{ date("d M Y", strtotime($sale->updated_at)) }}</span>
                  <span class="meta-item"><i class="fas fa-clock"></i> {{ date("H:i", strtotime($sale->updated_at)) }}</span>
                  <span class="meta-item"><i class="fas fa-chair"></i> {{ $sale->table_name }}</span>
                  <span class="meta-item"><i class="fas fa-user"></i> {{ $sale->user_name }}</span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                  <div class="sale-amount {{ $sale->sale_status === 'cancelled' ? 'cancelled-amount' : '' }} sale-total-{{$sale->id}}">
                    Rs {{ number_format($sale->total_price, 2) }}
                  </div>
                  @if($sale->sale_status !== 'cancelled' && $sale->total_recieved > 0)
                    <div style="font-size:0.7rem; color:#999;">S/C: Rs {{ number_format($sale->total_recieved, 2) }}</div>
                  @endif
                </div>
                @if(Auth::user() && Auth::user()->role === 'admin' && $sale->sale_status !== 'cancelled')
                  <button class="btn btn-danger btn-cancel-bill" data-sale-id="{{$sale->id}}" title="Cancel Bill & Restore Stock" onclick="event.stopPropagation();">
                    <i class="fas fa-ban me-1"></i> Cancel
                  </button>
                @endif
                <i class="fas fa-chevron-down chevron"></i>
              </div>
            </div>

            <div class="collapse" id="saleBody{{$sale->id}}">
              <div class="sale-card-body">
                <table class="detail-table">
                  <thead>
                    <tr>
                      <th style="width:60px;">#</th>
                      <th>Item</th>
                      <th style="width:80px;" class="text-center">Qty</th>
                      <th style="width:110px;" class="text-end">Price</th>
                      <th style="width:110px;" class="text-end">Total</th>
                      <th style="width:150px;">Time</th>
                      @if(Auth::user() && Auth::user()->role === 'admin')
                        <th style="width:80px;" class="text-center">Action</th>
                      @endif
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($sale->saleDetails as $saleDetail)
                      <tr id="detail-row-{{$saleDetail->id}}">
                        <td style="color:#aaa; font-size:0.8rem;">{{ $saleDetail->menu_id }}</td>
                        <td class="item-name">{{ $saleDetail->menu_name }}</td>
                        <td class="text-center"><span class="item-qty">{{ $saleDetail->quantity }}</span></td>
                        <td class="text-end">{{ number_format($saleDetail->menu_price, 2) }}</td>
                        <td class="text-end item-total">{{ number_format($saleDetail->menu_price * $saleDetail->quantity, 2) }}</td>
                        <td style="color:#999; font-size:0.8rem;">{{ $saleDetail->created_at ? $saleDetail->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        @if(Auth::user() && Auth::user()->role === 'admin')
                          <td class="text-center">
                            @if($sale->sale_status !== 'cancelled')
                              <button class="btn btn-outline-danger btn-void-item" data-detail-id="{{$saleDetail->id}}" data-menu-name="{{$saleDetail->menu_name}}" data-sale-id="{{$sale->id}}" title="Void Item & Restore Stock" onclick="event.stopPropagation();">
                                <i class="fas fa-times"></i>
                              </button>
                            @endif
                          </td>
                        @endif
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="sale-footer">
                <div>
                  <span class="footer-label">Items: </span>
                  <span class="footer-value">{{ $sale->saleDetails->count() }}</span>
                  <span class="footer-label ms-3">S/C: </span>
                  <span class="footer-value">Rs {{ number_format($sale->total_recieved, 2) }}</span>
                </div>
                <div>
                  <span class="footer-label">Bill Total: </span>
                  <span class="footer-value" style="color:#11998e;">Rs {{ number_format($sale->total_price, 2) }}</span>
                </div>
              </div>
            </div>
          </div>
          @php $countSale++; @endphp
        @endforeach
      </div>

      {{-- Pagination --}}
      <div class="pagination-wrapper d-flex justify-content-center">
        {{ $sales->appends($_GET)->links() }}
      </div>

      {{-- Summary Section with Charts --}}
      @if($summarySales->count() > 0)
        @php
          // Group summary data by category for charts
          $chartCategories = [];
          $chartCategoryTotals = [];
          foreach($summarySales as $item) {
              if (!isset($chartCategories[$item->name])) {
                  $chartCategories[$item->name] = [];
                  $chartCategoryTotals[$item->name] = 0;
              }
              $chartCategories[$item->name][] = ['menu_id' => $item->menu_id, 'menu_name' => $item->menu_name, 'qty' => $item->qty_sum];
              $chartCategoryTotals[$item->name] += $item->qty_sum;
          }
        @endphp

        <div class="summary-section">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Sales Summary</h5>
              <p style="font-size:0.8rem; color:#999; margin:0.25rem 0 0;">Aggregated quantities by menu item (paid bills only)</p>
            </div>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-secondary active" id="viewChart" style="border-radius:2rem 0 0 2rem;"><i class="fas fa-chart-bar me-1"></i>Chart</button>
              <button class="btn btn-sm btn-outline-secondary" id="viewTable" style="border-radius:0 2rem 2rem 0;"><i class="fas fa-table me-1"></i>Table</button>
            </div>
          </div>

          {{-- Chart View --}}
          <div id="chartView">
            {{-- Category Overview Doughnut --}}
            <div class="row g-4 mb-4">
              <div class="col-lg-5">
                <div style="background:#f8f9ff; border-radius:1rem; padding:1.25rem;">
                  <h6 style="font-weight:700; color:#1e3c72; margin-bottom:1rem; font-size:0.9rem;">
                    <i class="fas fa-chart-pie me-2"></i>Category Overview
                  </h6>
                  <div style="max-width:300px; margin:0 auto;">
                    <canvas id="categoryDoughnut"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-lg-7">
                <div style="background:#f8f9ff; border-radius:1rem; padding:1.25rem;">
                  <h6 style="font-weight:700; color:#1e3c72; margin-bottom:1rem; font-size:0.9rem;">
                    <i class="fas fa-chart-bar me-2"></i>Items by Category
                  </h6>
                  <div style="height:{{ max(count($chartCategoryTotals) * 40 + 40, 150) }}px; position:relative;">
                    <canvas id="categoryBar"></canvas>
                  </div>
                </div>
              </div>
            </div>

            {{-- Per-Category Horizontal Bar Charts --}}
            @php $catIndex = 0; @endphp
            @foreach($chartCategories as $catName => $items)
              <div style="background:#f8f9ff; border-radius:1rem; padding:1.25rem; margin-bottom:1rem;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 style="font-weight:700; color:#1e3c72; margin:0; font-size:0.85rem;">
                    <i class="fas fa-tag me-2" style="color:#667eea;"></i>{{ $catName }}
                  </h6>
                  <span style="background:#667eea; color:#fff; border-radius:2rem; padding:0.15rem 0.75rem; font-size:0.75rem; font-weight:600;">
                    {{ $chartCategoryTotals[$catName] }} items
                  </span>
                </div>
                <div style="height:{{ max(count($items) * 35 + 40, 120) }}px; position:relative;">
                  <canvas id="catChart{{ $catIndex }}"></canvas>
                </div>
              </div>
              @php $catIndex++; @endphp
            @endforeach
          </div>

          {{-- Table View (hidden by default) --}}
          <div id="tableView" style="display:none;">
            @php $CategoryNew = ''; @endphp
            @foreach($summarySales as $summaryItem)
              @if($CategoryNew != $summaryItem->name)
                @if($CategoryNew != '')
                  </tbody></table>
                @endif
                <div class="summary-cat-header">
                  <i class="fas fa-tag me-2"></i>{{ $summaryItem->name }}
                </div>
                <table class="summary-table">
                  <thead>
                    <tr>
                      <th style="width:100px;">Menu ID</th>
                      <th>Menu Item</th>
                      <th style="width:120px;" class="text-center">Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
              @endif
              @php $CategoryNew = $summaryItem->name; @endphp
              <tr>
                <td style="color:#999;">{{ $summaryItem->menu_id }}</td>
                <td style="font-weight:500;">{{ $summaryItem->menu_name }}</td>
                <td class="text-center"><span class="qty-badge">{{ $summaryItem->qty_sum }}</span></td>
              </tr>
            @endforeach
            @if($CategoryNew != '')
              </tbody></table>
            @endif
          </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        $(document).ready(function() {
            // View toggle
            $('#viewChart').click(function() {
                $(this).addClass('active'); $('#viewTable').removeClass('active');
                $('#chartView').show(); $('#tableView').hide();
            });
            $('#viewTable').click(function() {
                $(this).addClass('active'); $('#viewChart').removeClass('active');
                $('#tableView').show(); $('#chartView').hide();
            });

            // Color palette
            var colors = [
                '#667eea', '#764ba2', '#f5576c', '#4facfe', '#00f2fe',
                '#43e97b', '#fa709a', '#fee140', '#a18cd1', '#fbc2eb',
                '#f093fb', '#c471f5', '#48c6ef', '#6f86d6', '#0ba360',
                '#ff9a9e', '#fecfef', '#a1c4fd', '#d4fc79', '#96e6a1'
            ];
            var bgColors = colors.map(function(c) { return c + 'cc'; });

            // Category data
            var catNames = @json(array_keys($chartCategoryTotals));
            var catTotals = @json(array_values($chartCategoryTotals));

            // Doughnut Chart
            new Chart(document.getElementById('categoryDoughnut'), {
                type: 'doughnut',
                data: {
                    labels: catNames,
                    datasets: [{
                        data: catTotals,
                        backgroundColor: bgColors.slice(0, catNames.length),
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '55%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } },
                        tooltip: {
                            backgroundColor: 'rgba(30,60,114,0.9)',
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function(ctx) {
                                    var total = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                                    var pct = ((ctx.parsed / total) * 100).toFixed(1);
                                    return ctx.label + ': ' + ctx.parsed + ' items (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Category Bar Chart
            new Chart(document.getElementById('categoryBar'), {
                type: 'bar',
                data: {
                    labels: catNames,
                    datasets: [{
                        label: 'Total Qty',
                        data: catTotals,
                        backgroundColor: bgColors.slice(0, catNames.length),
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 28
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(30,60,114,0.9)',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) { return ctx.parsed.x + ' items sold'; }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
                    }
                }
            });

            // Per-category horizontal bar charts
            var allCatData = @json(array_values($chartCategories));
            allCatData.forEach(function(items, idx) {
                var canvas = document.getElementById('catChart' + idx);
                if (!canvas) return;
                var labels = items.map(function(i) { return i.menu_name; });
                var data = items.map(function(i) { return i.qty; });
                var maxQty = Math.max.apply(null, data);

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: data.map(function(v) {
                                var intensity = 0.3 + (v / maxQty) * 0.7;
                                return 'rgba(102, 126, 234, ' + intensity + ')';
                            }),
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 22
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(30,60,114,0.9)',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(ctx) { return 'Qty: ' + ctx.parsed.x; }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { font: { size: 10 }, stepSize: Math.max(1, Math.ceil(maxQty / 5)) }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 11 }, color: '#333' }
                            }
                        }
                    }
                });
            });
        });
        </script>
      @endif

      {{-- Export Bar --}}
      <div class="export-bar">
        <a href="/report" class="btn-back-report">
          <i class="fas fa-arrow-left me-1"></i> Back to Report
        </a>
        <a href="/report/show/export" class="btn-export">
          <i class="fas fa-file-excel me-1"></i> Export to Excel
        </a>
      </div>

    @else
      <div class="empty-state">
        <i class="fas fa-inbox d-block"></i>
        <h4>No Sales Found</h4>
        <p>There are no sales records for the selected date range.</p>
        <a href="/report" class="btn-back-report mt-3 d-inline-block">
          <i class="fas fa-arrow-left me-1"></i> Back to Report
        </a>
      </div>
    @endif
  </div>
</div>

{{-- Reason Modal --}}
<div class="modal fade" id="reasonModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:1rem; overflow:hidden; border:none;">
      <div class="modal-header" style="background:linear-gradient(135deg,#f5576c 0%,#ff6b6b 100%); color:#fff; border:none;">
        <h5 class="modal-title" id="reasonModalTitle"><i class="fas fa-exclamation-triangle me-2"></i>Reason Required</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.5rem;">
        <p id="reasonModalDesc" class="mb-3" style="font-size:0.9rem;"></p>
        <div class="mb-3">
          <label for="reasonInput" class="form-label" style="font-weight:600; font-size:0.85rem;">Reason for action:</label>
          <textarea id="reasonInput" class="form-control" rows="3" placeholder="Enter reason (e.g., wrong bill, duplicate, customer request)" required style="border-radius:0.75rem;"></textarea>
        </div>
        <div class="alert alert-info small mb-0" style="border-radius:0.75rem; border:none; background:#eef6ff;">
          <i class="fas fa-info-circle me-1"></i> Stock items will be automatically restored to kitchen inventory.
        </div>
      </div>
      <div class="modal-footer" style="border:none; padding:1rem 1.5rem;">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:2rem; padding:0.4rem 1.25rem;">Cancel</button>
        <button type="button" class="btn btn-danger" id="reasonConfirmBtn" style="border-radius:2rem; padding:0.4rem 1.25rem; font-weight:600;">
          <i class="fas fa-check me-1"></i> Confirm
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Result Modal --}}
<div class="modal fade" id="resultModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:1rem; overflow:hidden; border:none;">
      <div class="modal-header" id="resultModalHeader" style="border:none;">
        <h5 class="modal-title" id="resultModalTitle">Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="resultModalBody" style="padding:1.5rem;"></div>
      <div class="modal-footer" style="border:none;">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="border-radius:2rem; padding:0.4rem 1.25rem;">OK</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var pendingAction = null;
    var reasonModalEl = document.getElementById('reasonModal');
    var resultModalEl = document.getElementById('resultModal');
    var reasonModal = new bootstrap.Modal(reasonModalEl);
    var resultModal = new bootstrap.Modal(resultModalEl);

    // Search
    $('#saleSearch').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('.sale-card').each(function() {
            var match = $(this).data('search').toString().indexOf(q) > -1;
            $(this).toggle(match);
        });
    });

    // Filter buttons
    $('.filter-btn[data-filter]').click(function() {
        $('.filter-btn[data-filter]').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        $('.sale-card').each(function() {
            if (filter === 'all') { $(this).show(); }
            else { $(this).toggle($(this).data('status') === filter); }
        });
    });

    // Toggle all expand/collapse
    var allExpanded = false;
    $('#toggleAllBtn').click(function() {
        allExpanded = !allExpanded;
        if (allExpanded) {
            $('.sale-card .collapse').each(function() { new bootstrap.Collapse(this, {show: true}); });
            $(this).html('<i class="fas fa-compress-alt"></i>');
        } else {
            $('.sale-card .collapse').each(function() { new bootstrap.Collapse(this, {show: false}).hide(); });
            $(this).html('<i class="fas fa-expand-alt"></i>');
        }
    });

    // Cancel Bill
    $(document).on('click', '.btn-cancel-bill', function(e) {
        e.stopPropagation();
        var saleId = $(this).data('sale-id');
        $('#reasonModalTitle').html('<i class="fas fa-exclamation-triangle me-2"></i>Cancel Bill #' + saleId);
        $('#reasonModalDesc').html('This will <strong>cancel the entire bill</strong> and <strong>restore all stock items</strong> that were deducted. This action cannot be undone.');
        $('#reasonInput').val('');
        pendingAction = { type: 'cancel-bill', sale_id: saleId };
        reasonModal.show();
    });

    // Void Item
    $(document).on('click', '.btn-void-item', function(e) {
        e.stopPropagation();
        var detailId = $(this).data('detail-id');
        var menuName = $(this).data('menu-name');
        var saleId = $(this).data('sale-id');
        $('#reasonModalTitle').html('<i class="fas fa-exclamation-triangle me-2"></i>Void: ' + menuName);
        $('#reasonModalDesc').html('This will <strong>remove "' + menuName + '"</strong> from Bill #' + saleId + ' and <strong>restore its stock items</strong>. This action cannot be undone.');
        $('#reasonInput').val('');
        pendingAction = { type: 'void-item', sale_detail_id: detailId, sale_id: saleId };
        reasonModal.show();
    });

    // Confirm
    $('#reasonConfirmBtn').click(function() {
        var reason = $('#reasonInput').val().trim();
        if (!reason) { alert('Please enter a reason.'); return; }
        if (!pendingAction) return;
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        if (pendingAction.type === 'cancel-bill') {
            $.ajax({
                type: 'POST', url: '/report/cancel-bill',
                data: { _token: '{{ csrf_token() }}', sale_id: pendingAction.sale_id, reason: reason },
                success: function(data) {
                    reasonModal.hide();
                    showResult(true, data.message, data.restored_items);
                    var card = $('#sale-row-' + pendingAction.sale_id);
                    card.removeClass('active-sale').addClass('cancelled');
                    card.find('.badge-paid').removeClass('badge-paid').addClass('badge-cancelled').text('CANCELLED');
                    card.find('.sale-amount').addClass('cancelled-amount');
                    card.find('.btn-cancel-bill').remove();
                    card.find('.btn-void-item').remove();
                    card.attr('data-status', 'cancelled');
                },
                error: function(xhr) {
                    reasonModal.hide();
                    var msg = 'Failed to cancel bill.';
                    if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    showResult(false, msg, []);
                },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Confirm'); pendingAction = null; }
            });
        } else if (pendingAction.type === 'void-item') {
            $.ajax({
                type: 'POST', url: '/report/void-item',
                data: { _token: '{{ csrf_token() }}', sale_detail_id: pendingAction.sale_detail_id, reason: reason },
                success: function(data) {
                    reasonModal.hide();
                    showResult(true, data.message, data.restored_items);
                    $('#detail-row-' + pendingAction.sale_detail_id).fadeOut(300, function() { $(this).remove(); });
                    if (data.new_total !== undefined) {
                        $('.sale-total-' + pendingAction.sale_id).html('Rs ' + parseFloat(data.new_total).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}));
                    }
                    if (data.sale_cancelled) {
                        var card = $('#sale-row-' + pendingAction.sale_id);
                        card.removeClass('active-sale').addClass('cancelled');
                        card.find('.badge-paid').removeClass('badge-paid').addClass('badge-cancelled').text('CANCELLED');
                        card.find('.sale-amount').addClass('cancelled-amount');
                        card.find('.btn-cancel-bill').remove();
                        card.attr('data-status', 'cancelled');
                    }
                },
                error: function(xhr) {
                    reasonModal.hide();
                    var msg = 'Failed to void item.';
                    if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    showResult(false, msg, []);
                },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Confirm'); pendingAction = null; }
            });
        }
    });

    function showResult(success, message, restoredItems) {
        var header = $('#resultModalHeader');
        header.removeAttr('style').css('border', 'none');
        if (success) {
            header.css('background', 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)').css('color', '#fff');
            $('#resultModalTitle').html('<i class="fas fa-check-circle me-2"></i>Success');
        } else {
            header.css('background', 'linear-gradient(135deg, #f5576c 0%, #ff6b6b 100%)').css('color', '#fff');
            $('#resultModalTitle').html('<i class="fas fa-times-circle me-2"></i>Error');
        }
        var body = '<p style="font-size:0.95rem;">' + message + '</p>';
        if (restoredItems && restoredItems.length > 0) {
            body += '<div class="alert alert-success small" style="border-radius:0.75rem; border:none;"><strong><i class="fas fa-boxes me-1"></i>Stock Restored:</strong><ul class="mb-0 mt-1">';
            restoredItems.forEach(function(item) { body += '<li>' + item + '</li>'; });
            body += '</ul></div>';
        }
        $('#resultModalBody').html(body);
        resultModal.show();
    }
});
</script>

@endsection