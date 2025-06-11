@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Leave Request Details</h3>
                <div>
                    @if($leaveRequest->isPending())
                        <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
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

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Leave Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Request ID:</th>
                                    <td>#{{ $leaveRequest->id }}</td>
                                </tr>
                                <tr>
                                    <th>Staff Member:</th>
                                    <td>
                                        <strong>{{ $leaveRequest->person->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            @if($leaveRequest->person->staffCode)
                                                Code: {{ $leaveRequest->person->staffCode->staff_code }}
                                            @else
                                                ID: {{ $leaveRequest->person->id }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Leave Type:</th>
                                    <td>
                                        <span class="badge badge-info badge-pill">
                                            {{ $leaveRequest->formatted_leave_type }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ $leaveRequest->start_date->format('l, F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>{{ $leaveRequest->end_date->format('l, F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>
                                        <strong>{{ $leaveRequest->days }} day(s)</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="status-display">
                                        <span class="badge badge-{{ $leaveRequest->status_badge_class }} badge-lg">
                                            {{ ucfirst($leaveRequest->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Request Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Requested By:</th>
                                    <td>{{ $leaveRequest->requestedBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>Request Date:</th>
                                    <td>{{ $leaveRequest->created_at->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                @if($leaveRequest->approved_by)
                                <tr>
                                    <th>{{ $leaveRequest->status === 'approved' ? 'Approved' : 'Rejected' }} By:</th>
                                    <td>{{ $leaveRequest->approvedBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ $leaveRequest->status === 'approved' ? 'Approved' : 'Rejected' }} Date:</th>
                                    <td>{{ $leaveRequest->approved_at->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Reason for Leave</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $leaveRequest->reason }}</p>
                </div>
            </div>

            <!-- Admin Remarks Section -->
            @if($leaveRequest->admin_remarks)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Remarks</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $leaveRequest->admin_remarks }}</p>
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            @if(Auth::user()->checkAdmin() && $leaveRequest->isPending())
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield"></i> Admin Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-success btn-block" 
                                    onclick="showStatusModal('approved')">
                                <i class="fas fa-check"></i> Approve Leave Request
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-danger btn-block" 
                                    onclick="showStatusModal('rejected')">
                                <i class="fas fa-times"></i> Reject Leave Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
                    <input type="hidden" id="requestStatus">
                    
                    <div class="form-group">
                        <label for="admin_remarks">Admin Remarks:</label>
                        <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="3" 
                                  placeholder="Optional remarks about this decision..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Action:</strong> <span id="actionText"></span>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Staff Member:</strong> {{ $leaveRequest->person->name }}<br>
                        <strong>Leave Period:</strong> {{ $leaveRequest->start_date->format('M j, Y') }} to {{ $leaveRequest->end_date->format('M j, Y') }} ({{ $leaveRequest->days }} days)
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
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
    }
    .table-borderless th {
        border: none;
        padding-left: 0;
        font-weight: 600;
    }
    .table-borderless td {
        border: none;
        padding-left: 0;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
</style>
@endpush

@push('scripts')
<script>
function showStatusModal(status) {
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
    
    const status = document.getElementById('requestStatus').value;
    const remarks = document.getElementById('admin_remarks').value;
    
    const confirmButton = document.getElementById('confirmButton');
    const originalText = confirmButton.textContent;
    confirmButton.textContent = 'Processing...';
    confirmButton.disabled = true;
    
    try {
        const response = await fetch(`/leave-requests/update-status/{{ $leaveRequest->id }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status,
                admin_remarks: remarks
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the status display
            const statusDisplay = document.getElementById('status-display');
            statusDisplay.innerHTML = data.status_badge.replace('badge-', 'badge-').replace('">', ' badge-lg">');
            
            // Show success message
            showAlert(data.message, 'success');
            
            // Close modal
            $('#statusModal').modal('hide');
            
            // Reload page to update admin actions section
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'An error occurred', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while updating the status', 'danger');
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

// Clear form when modal is hidden
$('#statusModal').on('hidden.bs.modal', function () {
    document.getElementById('admin_remarks').value = '';
});
</script>
@endpush
@endsection@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Leave Request Details</h3>
                <div>
                    @if($leaveRequest->isPending())
                        <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
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

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Leave Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Request ID:</th>
                                    <td>#{{ $leaveRequest->id }}</td>
                                </tr>
                                <tr>
                                    <th>Staff Member:</th>
                                    <td>
                                        <strong>{{ $leaveRequest->person->name }}</strong>
                                        <br>
                                        <small class="text-muted">ID: {{ $leaveRequest->person->id }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Leave Type:</th>
                                    <td>
                                        <span class="badge badge-info badge-pill">
                                            {{ $leaveRequest->formatted_leave_type }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ $leaveRequest->start_date->format('l, F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>{{ $leaveRequest->end_date->format('l, F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>
                                        <strong>{{ $leaveRequest->days }} day(s)</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="status-display">
                                        <span class="badge badge-{{ $leaveRequest->status_badge_class }} badge-lg">
                                            {{ ucfirst($leaveRequest->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Request Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Requested By:</th>
                                    <td>{{ $leaveRequest->requestedBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>Request Date:</th>
                                    <td>{{ $leaveRequest->created_at->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                @if($leaveRequest->approved_by)
                                <tr>
                                    <th>{{ $leaveRequest->status === 'approved' ? 'Approved' : 'Rejected' }} By:</th>
                                    <td>{{ $leaveRequest->approvedBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ $leaveRequest->status === 'approved' ? 'Approved' : 'Rejected' }} Date:</th>
                                    <td>{{ $leaveRequest->approved_at->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Reason for Leave</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $leaveRequest->reason }}</p>
                </div>
            </div>

            <!-- Admin Remarks Section -->
            @if($leaveRequest->admin_remarks)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Remarks</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $leaveRequest->admin_remarks }}</p>
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            @if(Auth::user()->checkAdmin() && $leaveRequest->isPending())
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield"></i> Admin Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-success btn-block" 
                                    onclick="showStatusModal('approved')">
                                <i class="fas fa-check"></i> Approve Leave Request
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-danger btn-block" 
                                    onclick="showStatusModal('rejected')">
                                <i class="fas fa-times"></i> Reject Leave Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
                    <input type="hidden" id="requestStatus">
                    
                    <div class="form-group">
                        <label for="admin_remarks">Admin Remarks:</label>
                        <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="3" 
                                  placeholder="Optional remarks about this decision..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Action:</strong> <span id="actionText"></span>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Staff Member:</strong> {{ $leaveRequest->person->name }}<br>
                        <strong>Leave Period:</strong> {{ $leaveRequest->start_date->format('M j, Y') }} to {{ $leaveRequest->end_date->format('M j, Y') }} ({{ $leaveRequest->days }} days)
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
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
    }
    .table-borderless th {
        border: none;
        padding-left: 0;
        font-weight: 600;
    }
    .table-borderless td {
        border: none;
        padding-left: 0;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
</style>
@endpush

@push('scripts')
<script>
function showStatusModal(status) {
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
    
    const status = document.getElementById('requestStatus').value;
    const remarks = document.getElementById('admin_remarks').value;
    
    const confirmButton = document.getElementById('confirmButton');
    const originalText = confirmButton.textContent;
    confirmButton.textContent = 'Processing...';
    confirmButton.disabled = true;
    
    try {
        const response = await fetch(`/leave-requests/{{ $leaveRequest->id }}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status,
                admin_remarks: remarks
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the status display
            const statusDisplay = document.getElementById('status-display');
            statusDisplay.innerHTML = data.status_badge.replace('badge-', 'badge-').replace('">', ' badge-lg">');
            
            // Show success message
            showAlert(data.message, 'success');
            
            // Close modal
            $('#statusModal').modal('hide');
            
            // Reload page to update admin actions section
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'An error occurred', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while updating the status', 'danger');
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

// Clear form when modal is hidden
$('#statusModal').on('hidden.bs.modal', function () {
    document.getElementById('admin_remarks').value = '';
});
</script>
@endpush
@endsection