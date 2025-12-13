@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-chart-bar"></i> Water Bottle Issuance Report</h4>
                <a href="{{ route('water-bottle.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left"></i> Back to Issuance
                </a>
            </div>

            <!-- Date Range Filter -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter by Date Range</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('water-bottle.report') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tint"></i> Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">Current Stock</h6>
                                <h2 class="text-{{ $waterBottle->stock > 10 ? 'success' : ($waterBottle->stock > 0 ? 'warning' : 'danger') }}">
                                    {{ $waterBottle->stock }}
                                </h2>
                                <small>bottles available</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">Total Issued</h6>
                                <h2 class="text-primary">{{ $totalIssued }}</h2>
                                <small>bottles ({{ $startDate }} to {{ $endDate }})</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">Days with Issuance</h6>
                                <h2 class="text-info">{{ $groupedByDate->count() }}</h2>
                                <small>days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Daily Breakdown</h5>
                </div>
                <div class="card-body">
                    @if($groupedByDate->count() > 0)
                        <div class="accordion" id="dailyAccordion">
                            @foreach($groupedByDate as $date => $records)
                                @php
                                    $dailyTotal = abs($records->sum('stock'));
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $loop->index }}">
                                            <div class="d-flex justify-content-between w-100 me-3">
                                                <span>
                                                    <i class="fas fa-calendar-day"></i> 
                                                    {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
                                                </span>
                                                <span class="badge bg-primary">{{ $dailyTotal }} bottles</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#dailyAccordion">
                                        <div class="accordion-body">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Quantity</th>
                                                        <th>Room(s)</th>
                                                        <th>Issued By</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($records as $record)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                                                            <td><span class="badge bg-info">{{ abs($record->stock) }}</span></td>
                                                            <td>
                                                                @if($record->notes)
                                                                    <span class="badge bg-success">{{ str_replace('Room: ', '', $record->notes) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $record->user->name ?? 'Unknown' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-4x mb-3"></i>
                            <h5>No issuance records found</h5>
                            <p>No water bottles were issued during the selected date range.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
