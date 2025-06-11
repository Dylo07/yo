@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Edit Leave Request</h3>
                <div>
                    <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($leaveRequest->status !== 'pending')
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This leave request has already been {{ $leaveRequest->status }}. You cannot edit it.
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> You can only edit leave requests that are still pending approval.
                </div>

                <form action="{{ route('leave-requests.update', $leaveRequest) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="person_id">Staff Member: <span class="text-danger">*</span></label>
                                <select name="person_id" id="person_id" class="form-control" required>
                                    <option value="">-- Select Staff Member --</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}" 
                                                {{ (old('person_id', $leaveRequest->person_id) == $staff->id) ? 'selected' : '' }}>
                                            @if($staff->staffCode)
                                                {{ $staff->staffCode->staff_code }} - {{ $staff->name }}
                                                @if($staff->staffCategory)
                                                    ({{ ucfirst(str_replace('_', ' ', $staff->staffCategory->category)) }})
                                                @endif
                                            @else
                                                {{ $staff->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="leave_type">Leave Type: <span class="text-danger">*</span></label>
                                <select name="leave_type" id="leave_type" class="form-control" required>
                                    <option value="">-- Select Leave Type --</option>
                                    <option value="sick" {{ old('leave_type', $leaveRequest->leave_type) == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                    <option value="annual" {{ old('leave_type', $leaveRequest->leave_type) == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                    <option value="emergency" {{ old('leave_type', $leaveRequest->leave_type) == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                    <option value="personal" {{ old('leave_type', $leaveRequest->leave_type) == 'personal' ? 'selected' : '' }}>Personal Leave</option>
                                    <option value="maternity" {{ old('leave_type', $leaveRequest->leave_type) == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                    <option value="other" {{ old('leave_type', $leaveRequest->leave_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date: <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date: <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Leave: <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="4" 
                                  placeholder="Please provide detailed reason for the leave request..."
                                  required>{{ old('reason', $leaveRequest->reason) }}</textarea>
                        <small class="form-text text-muted">Maximum 1000 characters</small>
                    </div>

                    <!-- Current Request Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Current Request Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Request ID:</strong><br>
                                    #{{ $leaveRequest->id }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge badge-{{ $leaveRequest->status_badge_class }}">
                                        {{ ucfirst($leaveRequest->status) }}
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Original Duration:</strong><br>
                                    {{ $leaveRequest->days }} day(s)
                                </div>
                                <div class="col-md-3">
                                    <strong>Requested By:</strong><br>
                                    {{ $leaveRequest->requestedBy->name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> After updating, this leave request will remain pending and will need admin approval again.
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Leave Request
                        </button>
                        <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Update end date minimum when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
    
    // Set initial minimum for end date
    if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
    }
    
    // Validate end date is not before start date
    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && this.value < startDateInput.value) {
            alert('End date cannot be before start date');
            this.value = startDateInput.value;
        }
    });
});
</script>
@endpush
@endsection