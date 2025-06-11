@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gate Pass Dashboard</h2>
        <div>
            <a href="{{ route('gate-passes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Gate Pass
            </a>
            <a href="{{ route('gate-passes.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> All Passes
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['total_today'] }}</h3>
                    <p class="mb-0">Total Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['active'] }}</h3>
                    <p class="mb-0">Currently Out</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['overdue'] }}</h3>
                    <p class="mb-0">Overdue</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['returned'] }}</h3>
                    <p class="mb-0">Returned</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <h3 id="live-clock">{{ date('H:i') }}</h3>
                    <p class="mb-0">Current Time</p>
                </div>
            </div>
        </div>
    </div>

    @if($overdueCount > 0)
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Alert:</strong> {{ $overdueCount }} staff member(s) are overdue! Please contact them immediately.
    </div>
    @endif

    <!-- Active Passes -->
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Currently Active Gate Passes</h4>
        </div>
        <div class="card-body">
            @if($activePasses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Purpose</th>
                                <th>Exit Time</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                                <th>Time Remaining</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activePasses as $pass)
                                <tr class="{{ $pass->is_overdue ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $pass->person->name }}</strong>
                                        <br>
                                        <small>{{ $pass->gate_pass_number }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $pass->formatted_purpose }}</span>
                                        @if($pass->destination)
                                            <br><small>{{ $pass->destination }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $pass->exit_time->format('g:i A') }}</td>
                                    <td>{{ $pass->expected_return->format('g:i A') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $pass->status_badge_class }}">
                                            {{ ucfirst($pass->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-{{ $pass->is_overdue ? 'danger' : 'success' }}">
                                            <strong>{{ $pass->time_remaining }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        @if($pass->contact_number)
                                            <a href="tel:{{ $pass->contact_number }}">{{ $pass->contact_number }}</a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                onclick="markReturn({{ $pass->id }})"
                                                title="Mark Return">
                                            <i class="fas fa-sign-in-alt"></i> Return
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Clear!</h5>
                    <p class="text-muted">No staff members are currently out on gate passes.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<script>
// Live clock update
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour12: false,
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('live-clock').textContent = timeString;
}

// Update clock every second
setInterval(updateClock, 1000);

// Mark return function
function markReturn(passId) {
    if (confirm('Mark this staff member as returned?')) {
        fetch(`/gate-passes/${passId}/mark-return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('An error occurred', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'danger');
        });
    }
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    window.location.reload();
}, 30000);
</script>
@endpush
@endsection