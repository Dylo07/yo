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

            <div class="row">
                <!-- Issue Water Bottles Card -->
                <div class="col-md-5 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-hand-holding-water"></i> Issue Water Bottles</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>Current Stock:</strong></span>
                                    <span class="badge bg-{{ $waterBottle->stock > 10 ? 'success' : ($waterBottle->stock > 0 ? 'warning' : 'danger') }} fs-5">
                                        {{ $waterBottle->stock }} bottles
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('water-bottle.issue') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                                    <input type="number" 
                                           class="form-control form-control-lg @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           min="1" 
                                           max="{{ $waterBottle->stock }}"
                                           value="1"
                                           required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="room_numbers" class="form-label"><strong>Room Number(s)</strong></label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('room_numbers') is-invalid @enderror" 
                                           id="room_numbers" 
                                           name="room_numbers" 
                                           placeholder="e.g., 101, 102, 103"
                                           required>
                                    <div class="form-text">Enter room number(s) - separate multiple rooms with commas</div>
                                    @error('room_numbers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                    <i class="fas fa-paper-plane"></i> Issue Water Bottles
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Issue Buttons -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Issue (1 bottle)</h6>
                        </div>
                        <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                            <h6 class="text-muted mb-2">Standard Rooms</h6>
                            <div class="row g-2 mb-3">
                                @foreach(['101', '102', '103', '104', '105', '106', '107', '108', '109'] as $room)
                                    <div class="col-4">
                                        <form action="{{ route('water-bottle.issue') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="room_numbers" value="{{ $room }}">
                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                                {{ $room }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <h6 class="text-muted mb-2">Rooms 121-124</h6>
                            <div class="row g-2 mb-3">
                                @foreach(['121', '122', '123', '124'] as $room)
                                    <div class="col-3">
                                        <form action="{{ route('water-bottle.issue') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="room_numbers" value="{{ $room }}">
                                            <button type="submit" class="btn btn-outline-success btn-sm w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                                {{ $room }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <h6 class="text-muted mb-2">Rooms 130-134</h6>
                            <div class="row g-2 mb-3">
                                @foreach(['130', '131', '132', '133', '134'] as $room)
                                    <div class="col-4">
                                        <form action="{{ route('water-bottle.issue') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="room_numbers" value="{{ $room }}">
                                            <button type="submit" class="btn btn-outline-info btn-sm w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                                {{ $room }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <h6 class="text-muted mb-2">Named Rooms</h6>
                            <div class="row g-2 mb-3">
                                @foreach(['Orchid', 'Ahela', 'Sepalika', 'Sudu Araliya', 'Olu', 'Nelum'] as $room)
                                    <div class="col-4">
                                        <form action="{{ route('water-bottle.issue') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="room_numbers" value="{{ $room }}">
                                            <button type="submit" class="btn btn-outline-warning btn-sm w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                                {{ $room }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <h6 class="text-muted mb-2">Special Rooms</h6>
                            <div class="row g-2">
                                @foreach(['Hansa', 'Lihini', 'Mayura'] as $room)
                                    <div class="col-4">
                                        <form action="{{ route('water-bottle.issue') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="room_numbers" value="{{ $room }}">
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" {{ $waterBottle->stock < 1 ? 'disabled' : '' }}>
                                                {{ $room }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Stock History -->
                <div class="col-md-7 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Stock History</h5>
                                <form action="{{ route('water-bottle.index') }}" method="GET" class="d-flex align-items-center">
                                    <input type="date" 
                                           name="date" 
                                           class="form-control form-control-sm me-2" 
                                           value="{{ $selectedDate }}"
                                           onchange="this.form.submit()">
                                </form>
                            </div>
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
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover table-striped table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Time</th>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Room/Note</th>
                                                <th>Bill #</th>
                                                <th>By</th>
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
                                                            <span class="badge bg-info">{{ str_replace('Room: ', '', $record->notes) }}</span>
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
                            <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Monthly Summary - {{ $currentMonth }}</h6>
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
                                <div class="border rounded p-2 {{ ($monthlyAdded - $monthlyIssued) >= 0 ? 'bg-success' : 'bg-warning' }} bg-opacity-25">
                                    <strong>Net Change:</strong> 
                                    <span class="{{ ($monthlyAdded - $monthlyIssued) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($monthlyAdded - $monthlyIssued) >= 0 ? '+' : '' }}{{ $monthlyAdded - $monthlyIssued }} bottles
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
