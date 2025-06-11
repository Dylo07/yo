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
                    <a href="{{ route('leave-requests.print', $leaveRequest) }}" class="btn btn-success" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
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
                                    <th>Duration Type:</th>
                                    <td>
                                        @if($leaveRequest->is_datetime_based)
                                            <span class="badge badge-primary">Specific Time Period</span>
                                        @else
                                            <span class="badge badge-secondary">Full Day(s)</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($leaveRequest->is_datetime_based)
                                    <tr>
                                        <th>Start DateTime:</th>
                                        <td>
                                            <strong>{{ $leaveRequest->start_datetime->format('l, F j, Y') }}</strong><br>
                                            <span class="text-primary">{{ $leaveRequest->start_datetime->format('g:i A') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>End DateTime:</th>
                                        <td>
                                            <strong>{{ $leaveRequest->end_datetime->format('l, F j, Y') }}</strong><br>
                                            <span class="text-primary">{{ $leaveRequest->end_datetime->format('g:i A') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Hours:</th>
                                        <td>
                                            <strong>{{ $leaveRequest->hours }} hours</strong>
                                            @if($leaveRequest->hours < 8)
                                                <span class="badge badge-info ml-2">Half Day</span>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Start Date:</th>
                                        <td>{{ $leaveRequest->start_date->format('l, F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>End Date:</th>
                                        <td>{{ $leaveRequest->end_date->format('l, F j, Y') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Duration:</th>
                                    <td>
                                        <strong>{{ $leaveRequest->formatted_duration }}</strong>
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

                    <!-- Time Period Visual (for datetime-based requests) -->
                    @if($leaveRequest->is_datetime_based)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Time Period Visual</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-container">
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <div class="timeline-point start-point">
                                            <i class="fas fa-play text-success"></i>
                                        </div>
                                        <strong>Start</strong><br>
                                        <small>{{ $leaveRequest->start_datetime->format('M j') }}</small><br>
                                        <span class="text-success">{{ $leaveRequest->start_datetime->format('g:i A') }}</span>
                                    </div>
                                    <div class="col-6 text-center">
                                        <div class="timeline-point end-point">
                                            <i class="fas fa-stop text-danger"></i>
                                        </div>
                                        <strong>End</strong><br>
                                        <small>{{ $leaveRequest->end_datetime->format('M j') }}</small><br>
                                        <span class="text-danger">{{ $leaveRequest->end_datetime->format('g:i A') }}</span>
                                    </div>
                                </div>
                                <div class="timeline-line"></div>
                                <div class="text-center mt-3">
                                    <span class="badge badge-primary badge-lg">
                                        {{ $leaveRequest->formatted_duration }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
                        @if($leaveRequest->is_datetime_based)
                            <strong>Time Period:</strong> {{ $leaveRequest->start_datetime->format('M j, Y g:i A') }} to {{ $leaveRequest->end_datetime->format('M j, Y g:i A') }} ({{ $leaveRequest->formatted_duration }})
                        @else
                            <strong>Leave Period:</strong> {{ $leaveRequest->start_date->format('M j, Y') }} to {{ $leaveRequest->end_date->format('M j, Y') }} ({{ $leaveRequest->days }} days)
                        @endif
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
    .timeline-container {
        position: relative;
        padding: 20px 0;
    }
    .timeline-line {
        position: absolute;
        top: 50%;
        left: 25%;
        right: 25%;
        height: 2px;
        background: linear-gradient(to right, #28a745, #dc3545);
        z-index: 1;
    }
    .timeline-point {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        position: relative;
        z-index: 2;
    }
    .start-point {
        border-color: #28a745;
    }
    .end-point {
        border-color: #dc3545;
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
            const statusDisplay = document.getElementById('status-display');
            statusDisplay.innerHTML = data.status_badge.replace('badge-', 'badge-').replace('">', ' badge-lg">');
            
            showAlert(data.message, 'success');
            $('#statusModal').modal('hide');
            
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

$('#statusModal').on('hidden.bs.modal', function () {
    document.getElementById('admin_remarks').value = '';
});
</script>
@endpush
@endsection