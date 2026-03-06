@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fas fa-tint"></i> Water Bottle Room Issuance</h4>
                    <a href="{{ route('water-bottle.report') }}" class="btn btn-outline-dark">
                        <i class="fas fa-chart-bar"></i> View Report
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Universal Date Range Filter -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('water-bottle.index') }}" method="GET" class="row align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0 fw-bold"><i class="fas fa-calendar-alt"></i> Date Range:</label>
                            </div>
                            <div class="col-auto">
                                <input type="datetime-local" name="start_date" class="form-control" value="{{ $startDate }}" required>
                            </div>
                            <div class="col-auto">
                                <span class="fw-bold">to</span>
                            </div>
                            <div class="col-auto">
                                <input type="datetime-local" name="end_date" class="form-control" value="{{ $endDate }}" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('water-bottle.print-combined', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                                   target="_blank" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-print"></i> Print Report
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('water-bottle.print-with-description', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                                   target="_blank" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-print"></i> Print only with Description
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <!-- Vehicle Rooms Section -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-car"></i> Vehicle Rooms</h5>
                            </div>
                            <div class="card-body p-0">
                                @if(isset($vehicleRooms) && $vehicleRooms->count() > 0)
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover table-striped table-sm mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width: 15%;">Room</th>
                                                    <th style="width: 30%;">Vehicle Number</th>
                                                    <th style="width: 25%;">Check In</th>
                                                    <th style="width: 30%;">Check Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vehicleRooms as $vehicle)
                                                    @php
                                                        $rooms = is_string($vehicle->room_numbers) 
                                                            ? json_decode($vehicle->room_numbers, true) 
                                                            : $vehicle->room_numbers;
                                                        $rooms = is_array($rooms) ? $rooms : [];
                                                    @endphp
                                                    @foreach($rooms as $room)
                                                        <tr>
                                                            <td><span class="badge bg-primary">{{ $room }}</span></td>
                                                            <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                                                            <td><small>{{ $vehicle->created_at->format('M d, h:i A') }}</small></td>
                                                            <td>
                                                                @if($vehicle->checkout_time)
                                                                    <small>{{ \Carbon\Carbon::parse($vehicle->checkout_time)->format('M d, h:i A') }}</small>
                                                                @else
                                                                    <span class="badge bg-success">Active</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-car fa-3x mb-3"></i>
                                        <p>No vehicles with rooms found.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Stock History -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Stock History</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="alert alert-danger mb-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><strong>Issued:</strong></span>
                                                <span class="badge bg-danger fs-6">-{{ $totalIssuedToday }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="alert alert-success mb-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><strong>Added:</strong></span>
                                                <span class="badge bg-success fs-6">+{{ $totalAddedToday }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($stockHistory->count() > 0)
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover table-striped table-sm mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width: 10%;">Time</th>
                                                    <th style="width: 10%;">Type</th>
                                                    <th style="width: 8%;">Qty</th>
                                                    <th style="width: 15%;">Room/Note</th>
                                                    <th style="width: 12%;">Bill #</th>
                                                    <th style="width: 30%;">Description</th>
                                                    <th style="width: 15%;">By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stockHistory as $record)
                                                    <tr class="{{ $record->stock > 0 ? 'table-success' : '' }}">
                                                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                                                        <td>
                                                            @if($record->stock > 0)
                                                                <span class="badge bg-success">Added</span>
                                                            @else
                                                                <span class="badge bg-danger">Issued</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $record->stock > 0 ? 'success' : 'danger' }}">
                                                                {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($record->notes)
                                                                <span
                                                                    class="badge bg-info">{{ str_replace('Room: ', '', $record->notes) }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($record->sale_id)
                                                                <span class="badge bg-primary">BILL #{{ $record->sale_id }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($record->dailySalesSummary && $record->dailySalesSummary->description)
                                                                <small class="text-muted">{{ $record->dailySalesSummary->description }}</small>
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
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No stock activity on this date.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Monthly Summary -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Monthly Summary - {{ $currentMonth }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border rounded p-3 bg-light">
                                            <h6 class="text-muted mb-1">Total Issued</h6>
                                            <h3 class="text-danger mb-0">{{ $monthlyIssued }}</h3>
                                            <small class="text-muted">bottles</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3 bg-light">
                                            <h6 class="text-muted mb-1">Total Added</h6>
                                            <h3 class="text-success mb-0">{{ $monthlyAdded }}</h3>
                                            <small class="text-muted">bottles</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <div
                                        class="border rounded p-2 {{ ($monthlyAdded - $monthlyIssued) >= 0 ? 'bg-success' : 'bg-warning' }} bg-opacity-25">
                                        <strong>Net Change:</strong>
                                        <span
                                            class="{{ ($monthlyAdded - $monthlyIssued) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ ($monthlyAdded - $monthlyIssued) >= 0 ? '+' : '' }}{{ $monthlyAdded - $monthlyIssued }}
                                            bottles
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection