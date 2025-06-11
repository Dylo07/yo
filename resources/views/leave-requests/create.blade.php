@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Create New Leave Request</h3>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
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

            <form action="{{ route('leave-requests.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="person_id">Staff Member: <span class="text-danger">*</span></label>
                            <select name="person_id" id="person_id" class="form-control" required>
                                <option value="">-- Select Staff Member --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ old('person_id') == $staff->id ? 'selected' : '' }}>
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
                                <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Personal Leave</option>
                                <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>Other</option>
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
                                   value="{{ old('start_date') }}"
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
                                   value="{{ old('end_date') }}"
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
                              required>{{ old('reason') }}</textarea>
                    <small class="form-text text-muted">Maximum 1000 characters</small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> This leave request will be submitted for admin approval. You will be notified once it's processed.
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Leave Request
                    </button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
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