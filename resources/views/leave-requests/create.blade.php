{{-- resources/views/leave-requests/create.blade.php --}}
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

                <!-- Leave Duration Type -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Leave Duration Type: <span class="text-danger">*</span></label>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="duration_type" id="full_day" value="full_day" {{ old('duration_type', 'full_day') == 'full_day' ? 'checked' : '' }}>
                                <label class="form-check-label" for="full_day">
                                    Full Day(s)
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="radio" name="duration_type" id="specific_time" value="specific_time" {{ old('duration_type') == 'specific_time' ? 'checked' : '' }}>
                                <label class="form-check-label" for="specific_time">
                                    Specific Time Period
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Full Day Date Inputs -->
                <div id="full-day-inputs" class="duration-inputs">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date: <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date') }}"
                                       min="{{ date('Y-m-d') }}">
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
                                       min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specific Time Inputs -->
                <div id="time-inputs" class="duration-inputs" style="display: none;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date_time">Start Date: <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="start_date_time" 
                                       name="start_date_time" 
                                       value="{{ old('start_date_time') }}"
                                       min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_time">Start Time: <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="start_time" 
                                       name="start_time" 
                                       value="{{ old('start_time', '09:00') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date_time">End Date: <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="end_date_time" 
                                       name="end_date_time" 
                                       value="{{ old('end_date_time') }}"
                                       min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_time">End Time: <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control" 
                                       id="end_time" 
                                       name="end_time" 
                                       value="{{ old('end_time', '17:00') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Duration Display -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info" id="duration-display" style="display: none;">
                                <strong>Duration:</strong> <span id="calculated-duration">0 hours</span>
                            </div>
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
    const fullDayRadio = document.getElementById('full_day');
    const specificTimeRadio = document.getElementById('specific_time');
    const fullDayInputs = document.getElementById('full-day-inputs');
    const timeInputs = document.getElementById('time-inputs');
    
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const startDateTimeInput = document.getElementById('start_date_time');
    const startTimeInput = document.getElementById('start_time');
    const endDateTimeInput = document.getElementById('end_date_time');
    const endTimeInput = document.getElementById('end_time');
    const durationDisplay = document.getElementById('duration-display');
    const calculatedDuration = document.getElementById('calculated-duration');

    // Toggle input sections
    function toggleInputSections() {
        if (fullDayRadio.checked) {
            fullDayInputs.style.display = 'block';
            timeInputs.style.display = 'none';
            durationDisplay.style.display = 'none';
            
            // Clear time inputs
            startDateTimeInput.removeAttribute('required');
            startTimeInput.removeAttribute('required');
            endDateTimeInput.removeAttribute('required');
            endTimeInput.removeAttribute('required');
            
            // Add required to date inputs
            startDateInput.setAttribute('required', 'required');
            endDateInput.setAttribute('required', 'required');
        } else {
            fullDayInputs.style.display = 'none';
            timeInputs.style.display = 'block';
            durationDisplay.style.display = 'block';
            
            // Clear date inputs
            startDateInput.removeAttribute('required');
            endDateInput.removeAttribute('required');
            
            // Add required to time inputs
            startDateTimeInput.setAttribute('required', 'required');
            startTimeInput.setAttribute('required', 'required');
            endDateTimeInput.setAttribute('required', 'required');
            endTimeInput.setAttribute('required', 'required');
            
            calculateDuration();
        }
    }

    // Calculate duration for time-based inputs
    function calculateDuration() {
        if (startDateTimeInput.value && startTimeInput.value && endDateTimeInput.value && endTimeInput.value) {
            const startDateTime = new Date(startDateTimeInput.value + 'T' + startTimeInput.value);
            const endDateTime = new Date(endDateTimeInput.value + 'T' + endTimeInput.value);
            
            if (endDateTime > startDateTime) {
                const diffMs = endDateTime - startDateTime;
                const diffHours = diffMs / (1000 * 60 * 60);
                
                if (diffHours < 24) {
                    calculatedDuration.textContent = diffHours.toFixed(1) + ' hours';
                } else {
                    const days = Math.floor(diffHours / 24);
                    const remainingHours = diffHours % 24;
                    calculatedDuration.textContent = days + ' day(s) ' + remainingHours.toFixed(1) + ' hours';
                }
            } else {
                calculatedDuration.textContent = 'Invalid time range';
            }
        }
    }

    // Event listeners
    fullDayRadio.addEventListener('change', toggleInputSections);
    specificTimeRadio.addEventListener('change', toggleInputSections);

    // Full day date validation
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });

    endDateInput.addEventListener('change', function() {
        if (startDateInput.value && this.value < startDateInput.value) {
            alert('End date cannot be before start date');
            this.value = startDateInput.value;
        }
    });

    // Time input validation and calculation
    [startDateTimeInput, startTimeInput, endDateTimeInput, endTimeInput].forEach(input => {
        input.addEventListener('change', function() {
            // Auto-set end date to start date if not set
            if (startDateTimeInput.value && !endDateTimeInput.value) {
                endDateTimeInput.value = startDateTimeInput.value;
            }
            
            calculateDuration();
        });
    });

    // Initialize
    toggleInputSections();
});
</script>
@endpush
@endsection