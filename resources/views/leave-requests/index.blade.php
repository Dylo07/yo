@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Staff Leave Requests</h3>
                <div>
                    <a href="{{ route('leave-requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Leave Request
                    </a>
                    <a href="{{ route('leave-requests.calendar') }}" class="btn btn-info">
                        <i class="fas fa-calendar"></i> Calendar View
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
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                        <label for="month">Month:</label>
                        <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                            <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Leave Requests Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Staff Member</th>
                            <th>Leave Type</th>
                            <th>Period</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Requested Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    <strong>{{ $request->person->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($request->person->staffCode)
                                            Code: {{ $request->person->staffCode->staff_code }}
                                        @else
                                            ID: {{ $request->person->id }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $request->formatted_leave_type }}</span>
                                </td>
                                <td>
                                    @if($request->is_datetime_based)
                                        <strong>{{ $request->start_datetime->format('M d, Y') }}</strong>
                                        @if($request->start_datetime->format('Y-m-d') != $request->end_datetime->format('Y-m-d'))
                                            <br>to <strong>{{ $request->end_datetime->format('M d, Y') }}</strong>
                                        @endif
                                        <br>
                                        <small class="text-primary">
                                            <i class="fas fa-clock"></i>
                                            {{ $request->start_datetime->format('g:i A') }} - {{ $request->end_datetime->format('g:i A') }}
                                        </small>
                                    @else
                                        <strong>{{ $request->start_date->format('M d, Y') }}</strong>
                                        @if($request->start_date->format('Y-m-d') != $request->end_date->format('Y-m-d'))
                                            <br>to <strong>{{ $request->end_date->format('M d, Y') }}</strong>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day"></i> Full Day(s)
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->formatted_duration }}</strong>
                                    @if($request->is_datetime_based && $request->hours < 8)
                                        <br><small class="text-info">Half Day</small>
                                    @endif
                                </td>
                                <td id="status-{{ $request->id }}">
                                    <span class="badge badge-{{ $request->status_badge_class }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->requestedBy->name }}</td>
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('leave-requests.show', $request) }}" 
                                           class="btn btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('leave-requests.print', $request) }}" 
                                           class="btn btn-outline-secondary" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        
                                        @if($request->isPending())
                                            <a href="{{ route('leave-requests.edit', $request) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if(Auth::user()->checkAdmin() && $request->isPending())
                                            <button type="button" 
                                                    class="btn btn-outline-success" 
                                                    onclick="showStatusModal({{ $request->id }}, 'approved')"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="showStatusModal({{ $request->id }}, 'rejected')"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if($request->isPending() || Auth::user()->checkAdmin())
                                            <form action="{{ route('leave-requests.destroy', $request) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this leave request?')">
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
                                <td colspan="9" class="text-center">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $leaveRequests->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Status Update Modal (Admin Only) -->
@if(Auth::user()->checkAdmin())
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Leave Request Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="statusForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="requestId">
                    <input type="hidden" id="requestStatus">
                    
                    <div class="form-group">
                        <label for="admin_remarks">Admin Remarks:</label>
                        <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="3" 
                                  placeholder="Optional remarks about this decision..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Action:</strong> <span id="actionText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="confirmButton">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
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
</style>
@endpush

@push('scripts')
<script>
function showStatusModal(requestId, status) {
    document.getElementById('requestId').value = requestId;
    document.getElementById('requestStatus').value = status;
    
    const actionText = status === 'approved' ? 'Approve this leave request' : 'Reject this leave request';
    document.getElementById('actionText').textContent = actionText;
    
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.textContent = status === 'approved' ? 'Approve' : 'Reject';
    confirmButton.className = status === 'approved' ? 'btn btn-success' : 'btn btn-danger';
    
    $('#statusModal').modal('show');
}

document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const requestId = document.getElementById('requestId').value;
    const status = document.getElementById('requestStatus').value;
    const remarks = document.getElementById('admin_remarks').value;
    
    const confirmButton = document.getElementById('confirmButton');
    const originalText = confirmButton.textContent;
    confirmButton.textContent = 'Processing...';
    confirmButton.disabled = true;
    
    try {
        const url = `/leave-requests/update-status/${requestId}`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: status,
                admin_remarks: remarks
            })
        });
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const responseText = await response.text();
            throw new Error('Server error: Expected JSON response but got ' + contentType);
        }
        
        const data = await response.json();
        
        if (response.status === 403) {
            showAlert(data.error || 'Unauthorized', 'danger');
            return;
        }
        
        if (data.success) {
            const statusCell = document.getElementById(`status-${requestId}`);
            if (statusCell) {
                statusCell.innerHTML = data.status_badge;
            }
            
            showAlert(data.message, 'success');
            $('#statusModal').modal('hide');
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'An error occurred', 'danger');
        }
    } catch (error) {
        console.error('Error details:', error);
        showAlert('An error occurred while updating the status: ' + error.message, 'danger');
    } finally {
        confirmButton.textContent = originalText;
        confirmButton.disabled = false;
    }
});

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    const container = document.querySelector('.card-body');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

$('#statusModal').on('hidden.bs.modal', function () {
    document.getElementById('admin_remarks').value = '';
});
</script>
@endpush
@endsection