{{-- resources/views/gate-passes/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['total_today'] }}</h4>
                            <p class="mb-0">Total Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['active'] }}</h4>
                            <p class="mb-0">Currently Out</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sign-out-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['overdue'] }}</h4>
                            <p class="mb-0">Overdue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['returned'] }}</h4>
                            <p class="mb-0">Returned</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sign-in-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Gate Pass Management</h3>
                <div>
                    <a href="{{ route('gate-passes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Gate Pass
                    </a>
                    <a href="{{ route('gate-passes.dashboard') }}" class="btn btn-info">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status">Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Currently Out</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="person_id">Staff Member:</label>
                        <select name="person_id" id="person_id" class="form-control">
                            <option value="">All Staff</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ request('person_id') == $staff->id ? 'selected' : '' }}>
                                    @if($staff->staffCode)
                                        {{ $staff->staffCode->staff_code }} - {{ $staff->name }}
                                    @else
                                        {{ $staff->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                            <a href="{{ route('gate-passes.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Gate Passes Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Gate Pass #</th>
                            <th>Staff Member</th>
                            <th>Purpose</th>
                            <th>Exit Time</th>
                            <th>Expected Return</th>
                            <th>Actual Return</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gatePasses as $pass)
                            <tr class="{{ $pass->is_overdue ? 'table-danger' : '' }}">
                                <td>
                                    <strong>{{ $pass->gate_pass_number }}</strong>
                                    @if($pass->emergency_pass)
                                        <br><span class="badge badge-warning">Emergency</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $pass->person->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($pass->person->staffCode)
                                            {{ $pass->person->staffCode->staff_code }}
                                        @else
                                            ID: {{ $pass->person->id }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $pass->formatted_purpose }}</span>
                                    @if($pass->destination)
                                        <br><small class="text-muted">to {{ $pass->destination }}</small>
                                    @endif
                                </td>
                                <td>{{ $pass->exit_time->format('M d, g:i A') }}</td>
                                <td>
                                    {{ $pass->expected_return->format('M d, g:i A') }}
                                    @if($pass->time_remaining && $pass->status !== 'returned')
                                        <br><small class="text-{{ $pass->is_overdue ? 'danger' : 'success' }}">
                                            {{ $pass->time_remaining }}
                                        </small>
                                    @endif
                                </td>
                                <td id="actual-return-{{ $pass->id }}">
                                    @if($pass->actual_return)
                                        <strong class="text-success">{{ $pass->actual_return->format('M d, g:i A') }}</strong>
                                        @php
                                            $timeDiff = $pass->actual_return->diffInMinutes($pass->expected_return, false);
                                        @endphp
                                        @if($timeDiff > 0)
                                            <br><small class="text-success">
                                                <i class="fas fa-thumbs-up"></i> {{ abs($timeDiff) }} min early
                                            </small>
                                        @elseif($timeDiff < 0)
                                            <br><small class="text-danger">
                                                <i class="fas fa-clock"></i> {{ abs($timeDiff) }} min late
                                            </small>
                                        @else
                                            <br><small class="text-info">
                                                <i class="fas fa-check"></i> On time
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">Not returned yet</span>
                                        @if($pass->is_overdue)
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                {{ abs($pass->expected_return->diffInMinutes(now())) }} min overdue
                                            </small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $pass->formatted_duration }}</td>
                                <td id="status-{{ $pass->id }}">
                                    @if($pass->status === 'returned')
                                        <span class="badge badge-info">Returned</span>
                                    @elseif($pass->is_overdue)
                                        <span class="badge badge-danger">Overdue</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group" id="actions-{{ $pass->id }}">
                                        <a href="{{ route('gate-passes.show', $pass) }}" 
                                           class="btn btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('gate-passes.print', $pass) }}" 
                                           class="btn btn-outline-secondary" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        {{-- Show Mark Return button for all non-returned passes --}}
                                        @if($pass->status !== 'returned')
                                            <button type="button" 
                                                    class="btn btn-outline-primary mark-return-btn" 
                                                    onclick="markReturn({{ $pass->id }})"
                                                    title="Mark Return">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </button>
                                        @endif

                                        {{-- Edit button (always available for non-returned passes) --}}
                                        @if($pass->status !== 'returned')
                                            <a href="{{ route('gate-passes.edit', $pass) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        {{-- Delete button (admin only) --}}
                                        @if(Auth::user()->checkAdmin())
                                            <form action="{{ route('gate-passes.destroy', $pass) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this gate pass?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No gate passes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $gatePasses->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .table-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }
    .alert {
        margin-bottom: 1rem;
    }
    .table td {
        font-size: 0.9rem;
    }
    .badge {
        font-size: 0.75rem;
    }
    .text-success {
        color: #28a745 !important;
    }
    .text-danger {
        color: #dc3545 !important;
    }
    .text-info {
        color: #17a2b8 !important;
    }
</style>
@endpush

@push('scripts')
<script>
function markReturn(passId) {
    if (confirm('Mark this staff member as returned?')) {
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
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
                // Update the status badge
                const statusCell = document.getElementById(`status-${passId}`);
                if (statusCell) {
                    statusCell.innerHTML = '<span class="badge badge-info">Returned</span>';
                }
                
                // Update actual return time with current time
                const now = new Date();
                const returnTimeFormatted = now.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
                const actualReturnCell = document.getElementById(`actual-return-${passId}`);
                if (actualReturnCell) {
                    actualReturnCell.innerHTML = `
                        <strong class="text-success">${returnTimeFormatted}</strong>
                        <br><small class="text-success">
                            <i class="fas fa-check"></i> Just returned
                        </small>
                    `;
                }
                
                // Remove the Mark Return and Edit buttons
                const markReturnBtn = button;
                const editBtn = document.querySelector(`#actions-${passId} .btn-outline-warning`);
                
                if (markReturnBtn) {
                    markReturnBtn.remove();
                }
                if (editBtn) {
                    editBtn.remove();
                }
                
                showAlert(data.message, 'success');
                
                // Update stats after short delay
                setTimeout(() => {
                    updateStats();
                }, 1000);
                
            } else {
                // Restore button on error
                button.innerHTML = originalHtml;
                button.disabled = false;
                showAlert(data.message || 'An error occurred', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Restore button on error
            button.innerHTML = originalHtml;
            button.disabled = false;
            showAlert('An error occurred while marking return', 'danger');
        });
    }
}

function updateStats() {
    // Update the stats cards by reloading them
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract and update stats from the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const statsCards = doc.querySelectorAll('.card h4');
        const currentStatsCards = document.querySelectorAll('.card h4');
        
        if (statsCards.length === currentStatsCards.length) {
            for (let i = 0; i < statsCards.length; i++) {
                currentStatsCards[i].textContent = statsCards[i].textContent;
            }
        }
    })
    .catch(error => {
        console.error('Error updating stats:', error);
    });
}

function showAlert(message, type = 'success') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    const container = document.querySelector('.card-body');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Auto-refresh page every 30 seconds for live updates
setInterval(() => {
    window.location.reload();
}, 30000);

// Update time remaining every minute for active passes
setInterval(() => {
    const timeElements = document.querySelectorAll('small[class*="text-"]');
    timeElements.forEach(element => {
        if (element.textContent.includes('left') || element.textContent.includes('overdue')) {
            // Update time remaining calculations here if needed
            // This is a placeholder for real-time updates
        }
    });
}, 60000);
</script>
@endpush
@endsection