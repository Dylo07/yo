@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Main Functions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kitchen.daily-stock') }}">Kitchen</a></li>
            <li class="breadcrumb-item active">Daily Stock Sheet</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-clipboard-list text-primary mr-2"></i>
                Daily Kitchen Stock Sheet
            </h2>
            <p class="text-muted mb-0">Track daily opening, received, used and closing balances for kitchen items</p>
        </div>
        <div>
            <a href="{{ route('kitchen.daily-stock.settings') }}" class="btn btn-outline-secondary" title="Settings">
                <i class="fas fa-cog mr-1"></i> Settings
            </a>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="dateForm" action="{{ route('kitchen.daily-stock') }}" method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label for="dateInput" class="form-label font-weight-bold">Select Date</label>
                    <input type="date" id="dateInput" name="date" class="form-control"
                           value="{{ $date }}" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search mr-1"></i> Load
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('kitchen.daily-stock', ['date' => now()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary{{ $date == now()->format('Y-m-d') ? ' active' : '' }}">Today</a>
                        <a href="{{ route('kitchen.daily-stock', ['date' => now()->subDay()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary{{ $date == now()->subDay()->format('Y-m-d') ? ' active' : '' }}">Yesterday</a>
                    </div>
                </div>
                <div class="col-md-3 text-right">
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(count($items) === 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            No items are being tracked. Please go to
            <a href="{{ route('kitchen.daily-stock.settings') }}">Settings</a>
            to select items to track on the daily stock sheet.
        </div>
    @else
    <!-- Stock Sheet Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table mr-1"></i>
                Stock Sheet for {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
            </h5>
            <span class="badge badge-info">{{ count($items) }} items tracked</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="stockTable">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Item Name</th>
                            <th style="width: 70px;" class="text-center">Unit</th>
                            <th style="width: 110px;" class="text-center">Opening<br>Balance</th>
                            <th style="width: 110px;" class="text-center text-success">+ Received<br>(auto)</th>
                            <th style="width: 110px;" class="text-center text-danger">- Used in<br>Sales (auto)</th>
                            <th style="width: 110px;" class="text-center text-info">= Expected<br>Balance</th>
                            <th style="width: 120px;" class="text-center">Physical<br>Count</th>
                            <th style="width: 100px;" class="text-center">Variance</th>
                            <th style="width: 180px;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($groupedItems as $groupName => $groupItems)
                        <tr class="bg-secondary text-white">
                            <td colspan="10" class="font-weight-bold py-2" style="font-size: 1.05em;">
                                <i class="fas fa-folder-open mr-1"></i> {{ $groupName }}
                                <span class="badge badge-light ml-2">{{ count($groupItems) }} items</span>
                            </td>
                        </tr>
                        @foreach($groupItems as $item)
                        @php $counter++; @endphp
                        <tr class="{{ $item['physical_count'] === null ? 'bg-warning-light' : '' }}"
                            data-item-id="{{ $item['item_id'] }}"
                            data-expected="{{ $item['expected_balance'] }}">
                            <td class="text-center">{{ $counter }}</td>
                            <td class="font-weight-bold pl-4">{{ $item['name'] }}</td>
                            <td class="text-center text-muted">{{ $item['unit'] }}</td>
                            <td class="text-center">{{ number_format($item['opening_balance'], 3) }}</td>
                            <td class="text-center text-success font-weight-bold">{{ number_format($item['received'], 3) }}</td>
                            <td class="text-center text-danger font-weight-bold">{{ number_format($item['used'], 3) }}</td>
                            <td class="text-center text-info font-weight-bold">{{ number_format($item['expected_balance'], 3) }}</td>
                            <td class="text-center p-1">
                                <input type="number"
                                       class="form-control form-control-sm text-center physical-count-input"
                                       step="0.001"
                                       min="0"
                                       value="{{ $item['physical_count'] !== null ? $item['physical_count'] : '' }}"
                                       placeholder="-"
                                       data-item-id="{{ $item['item_id'] }}">
                            </td>
                            <td class="text-center font-weight-bold variance-cell">
                                @if($item['variance'] !== null)
                                    <span class="{{ $item['variance'] < 0 ? 'text-danger' : ($item['variance'] == 0 ? 'text-success' : 'text-primary') }}">
                                        {{ number_format($item['variance'], 3) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="p-1">
                                <input type="text"
                                       class="form-control form-control-sm notes-input"
                                       value="{{ $item['notes'] }}"
                                       placeholder="Notes..."
                                       data-item-id="{{ $item['item_id'] }}">
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <button type="button" class="btn btn-success btn-lg" id="saveBtn">
                    <i class="fas fa-save mr-1"></i> Save Stock Sheet
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-outline-info" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .physical-count-input {
        max-width: 110px;
        margin: 0 auto;
    }
    .notes-input {
        max-width: 170px;
    }
    @media print {
        .breadcrumb, .card-footer, .btn, nav, .navbar, .sidebar {
            display: none !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background: #fff !important;
            color: #000 !important;
        }
        .thead-dark th {
            background: #ddd !important;
            color: #000 !important;
        }
        .physical-count-input, .notes-input {
            border: none !important;
            background: transparent !important;
            text-align: center;
        }
    }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate variance on physical count input
    $('.physical-count-input').on('input change', function() {
        var row = $(this).closest('tr');
        var expected = parseFloat(row.data('expected'));
        var physicalVal = $(this).val();
        var varianceCell = row.find('.variance-cell');

        if (physicalVal === '' || isNaN(parseFloat(physicalVal))) {
            varianceCell.html('<span class="text-muted">-</span>');
            row.addClass('bg-warning-light');
        } else {
            var physical = parseFloat(physicalVal);
            var variance = physical - expected;
            var colorClass = 'text-success';
            if (variance < 0) {
                colorClass = 'text-danger';
            } else if (variance > 0) {
                colorClass = 'text-primary';
            }
            varianceCell.html('<span class="' + colorClass + '">' + variance.toFixed(3) + '</span>');
            row.removeClass('bg-warning-light');
        }
    });

    // Save button
    $('#saveBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        var itemsData = [];
        $('#stockTable tbody tr').each(function() {
            var row = $(this);
            var itemId = row.data('item-id');
            var physicalCount = row.find('.physical-count-input').val();
            var notes = row.find('.notes-input').val();

            itemsData.push({
                item_id: itemId,
                physical_count: physicalCount,
                notes: notes
            });
        });

        $.ajax({
            url: '{{ route("kitchen.daily-stock.save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                date: '{{ $date }}',
                items: itemsData
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Stock Sheet');
                if (response.success) {
                    alert('Stock sheet saved successfully!');
                    // Remove yellow background from rows that now have physical counts
                    $('#stockTable tbody tr').each(function() {
                        var val = $(this).find('.physical-count-input').val();
                        if (val !== '' && !isNaN(parseFloat(val))) {
                            $(this).removeClass('bg-warning-light');
                        }
                    });
                } else {
                    alert('Error: ' + (response.message || 'Failed to save.'));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Stock Sheet');
                var msg = 'Failed to save. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
});
</script>
@endpush
@endsection
