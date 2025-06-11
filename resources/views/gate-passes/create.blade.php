{{-- resources/views/gate-passes/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Create New Gate Pass</h3>
                <a href="{{ route('gate-passes.index') }}" class="btn btn-secondary">
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

            <form action="{{ route('gate-passes.store') }}" method="POST">
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
                            <label for="purpose">Purpose: <span class="text-danger">*</span></label>
                            <select name="purpose" id="purpose" class="form-control" required>
                                <option value="">-- Select Purpose --</option>
                                <option value="personal" {{ old('purpose') == 'personal' ? 'selected' : '' }}>Personal</option>
                                <option value="official" {{ old('purpose') == 'official' ? 'selected' : '' }}>Official Work</option>
                                <option value="emergency" {{ old('purpose') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="medical" {{ old('purpose') == 'medical' ? 'selected' : '' }}>Medical</option>
                                <option value="bank" {{ old('purpose') == 'bank' ? 'selected' : '' }}>Bank Work</option>
                                <option value="post_office" {{ old('purpose') == 'post_office' ? 'selected' : '' }}>Post Office</option>
                                <option value="market" {{ old('purpose') == 'market' ? 'selected' : '' }}>Market/Shopping</option>
                                <option value="other" {{ old('purpose') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exit_time">Exit Time: <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="exit_time" 
                                   name="exit_time" 
                                   value="{{ old('exit_time') }}"
                                   min="{{ date('Y-m-d\TH:i') }}"
                                   required>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="sync-time-btn">
                                <i class="fas fa-sync-alt"></i> Sync to Current Time
                            </button>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> Current Time: <span id="live-clock"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="duration_minutes">Duration: <span class="text-danger">*</span></label>
                            <select name="duration_minutes" id="duration_minutes" class="form-control" required>
                                <option value="">-- Select Duration --</option>
                                <option value="5" {{ old('duration_minutes') == '5' ? 'selected' : '' }}>5 minutes</option>
                                <option value="10" {{ old('duration_minutes') == '10' ? 'selected' : '' }}>10 minutes</option>
                                <option value="15" {{ old('duration_minutes') == '15' ? 'selected' : '' }}>15 minutes</option>
                                <option value="30" {{ old('duration_minutes') == '30' ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ old('duration_minutes') == '60' ? 'selected' : '' }}>1 hour</option>
                                <option value="90" {{ old('duration_minutes') == '90' ? 'selected' : '' }}>1.5 hours</option>
                                <option value="120" {{ old('duration_minutes') == '120' ? 'selected' : '' }}>2 hours</option>
                                <option value="180" {{ old('duration_minutes') == '180' ? 'selected' : '' }}>3 hours</option>
                                <option value="240" {{ old('duration_minutes') == '240' ? 'selected' : '' }}>4 hours</option>
                                <option value="custom">Custom Duration</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Custom Duration Input (hidden by default) -->
                <div class="row" id="custom-duration-row" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="custom_duration">Custom Duration (minutes): <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="custom_duration" 
                                   name="custom_duration" 
                                   min="5" 
                                   max="480"
                                   placeholder="Enter minutes (5-480)">
                            <small class="form-text text-muted">Maximum 8 hours (480 minutes)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Expected Return Time:</label>
                            <input type="text" class="form-control" id="expected_return_display" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination">Destination:</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="destination" 
                                   name="destination" 
                                   value="{{ old('destination') }}"
                                   placeholder="Where are you going?">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_number">Contact Number:</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contact_number" 
                                   name="contact_number" 
                                   value="{{ old('contact_number') }}"
                                   placeholder="Emergency contact number">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_number">Vehicle Number (if applicable):</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="vehicle_number" 
                                   name="vehicle_number" 
                                   value="{{ old('vehicle_number') }}"
                                   placeholder="e.g., ABC-1234">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="items_carried">Items Being Carried:</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="items_carried" 
                                   name="items_carried" 
                                   value="{{ old('items_carried') }}"
                                   placeholder="Any items you're taking out">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason/Details: <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              id="reason" 
                              name="reason" 
                              rows="3" 
                              placeholder="Please provide detailed reason for going out..."
                              required>{{ old('reason') }}</textarea>
                    <small class="form-text text-muted">Maximum 500 characters</small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Emergency passes are auto-approved</li>
                        <li>Other passes require admin approval</li>
                        <li>Exit time will be automatically set to current time + 1 minute when submitting</li>
                        <li>Please return on time to avoid being marked overdue</li>
                        <li>Contact security if you need to extend your time</li>
                    </ul>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Gate Pass Request
                    </button>
                    <a href="{{ route('gate-passes.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .sync-button {
        margin-top: 8px;
    }
    
    #live-clock {
        font-weight: bold;
        color: #007bff;
    }
    
    .position-fixed {
        position: fixed !important;
    }
    
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    }
    
    .btn-outline-primary:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const durationSelect = document.getElementById('duration_minutes');
    const customDurationRow = document.getElementById('custom-duration-row');
    const customDurationInput = document.getElementById('custom_duration');
    const exitTimeInput = document.getElementById('exit_time');
    const expectedReturnDisplay = document.getElementById('expected_return_display');
    const syncTimeBtn = document.getElementById('sync-time-btn');
    const form = document.querySelector('form');

    // Handle duration selection
    durationSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDurationRow.style.display = 'block';
            customDurationInput.setAttribute('required', 'required');
            // Clear the name attribute from duration_minutes to use custom_duration instead
            this.removeAttribute('name');
            customDurationInput.setAttribute('name', 'duration_minutes');
        } else {
            customDurationRow.style.display = 'none';
            customDurationInput.removeAttribute('required');
            customDurationInput.removeAttribute('name');
            this.setAttribute('name', 'duration_minutes');
        }
        updateExpectedReturn();
    });

    // Function to get current time + 1 minute in datetime-local format
    function getCurrentDateTimePlus1Min() {
        const now = new Date();
        // Add 1 minute (60000 milliseconds)
        const futureTime = new Date(now.getTime() + 120000);
        // Adjust for timezone offset
        const offsetMs = futureTime.getTimezoneOffset() * 60 * 1000;
        const localTime = new Date(futureTime.getTime() - offsetMs);
        return localTime.toISOString().slice(0, 16);
    }

    // Function to update exit time to current time + 1 minute
    function updateExitTimeToNow() {
        const currentTimePlus1 = getCurrentDateTimePlus1Min();
        exitTimeInput.value = currentTimePlus1;
        updateExpectedReturn();
        showNotification('Exit time synced to current time + 1 minute!', 'success');
    }

    // Update expected return time
    function updateExpectedReturn() {
        const exitTime = exitTimeInput.value;
        const duration = durationSelect.value === 'custom' ? customDurationInput.value : durationSelect.value;
        
        if (exitTime && duration && duration !== '') {
            const exit = new Date(exitTime);
            const returnTime = new Date(exit.getTime() + (duration * 60000)); // Add minutes
            
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            expectedReturnDisplay.value = returnTime.toLocaleDateString('en-US', options);
        } else {
            expectedReturnDisplay.value = '';
        }
    }

    // Update expected return when exit time or custom duration changes
    exitTimeInput.addEventListener('change', function() {
        updateExpectedReturn();
        
        // Check if time is in the past
        const selectedTime = new Date(this.value);
        const now = new Date();
        
        if (selectedTime < now) {
            showNotification('⚠️ Warning: Exit time is in the past!', 'warning');
        }
    });
    
    customDurationInput.addEventListener('input', updateExpectedReturn);

    // Manual sync button
    syncTimeBtn.addEventListener('click', updateExitTimeToNow);

    // AUTO-SYNC: Update exit time to current time + 1 minute when form is submitted
    form.addEventListener('submit', function(e) {
        // Update exit time to current real time + 1 minute right before submission
        const currentTimePlus1 = getCurrentDateTimePlus1Min();
        exitTimeInput.value = currentTimePlus1;
        
        console.log('Gate pass submitted with exit time:', exitTimeInput.value);
        showNotification('Exit time automatically synced to current time + 1 minute!', 'info');
        
        // Allow form to continue submitting
        return true;
    });

    // Update live clock every second
    function updateLiveClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour12: true,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit'
        });
        const clockElement = document.getElementById('live-clock');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }

    setInterval(updateLiveClock, 1000);
    updateLiveClock(); // Initial call

    // Set initial exit time to current time if not set from old input
    if (!exitTimeInput.value) {
        updateExitTimeToNow();
    } else {
        updateExpectedReturn();
    }

    // Show notification function
    function showNotification(message, type = 'success') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }

    // Auto-fill contact number based on staff member selection (if you have this data)
    document.getElementById('person_id').addEventListener('change', function() {
        // You can implement auto-filling of contact number here if stored in staff data
        console.log('Staff member changed:', this.value);
    });

    // Character counter for reason field
    const reasonTextarea = document.getElementById('reason');
    const maxChars = 500;
    
    // Create character counter
    const charCounter = document.createElement('small');
    charCounter.className = 'form-text text-muted';
    charCounter.id = 'char-counter';
    reasonTextarea.parentNode.appendChild(charCounter);
    
    function updateCharCounter() {
        const remaining = maxChars - reasonTextarea.value.length;
        charCounter.textContent = `${remaining} characters remaining`;
        
        if (remaining < 50) {
            charCounter.className = 'form-text text-warning';
        } else if (remaining < 0) {
            charCounter.className = 'form-text text-danger';
        } else {
            charCounter.className = 'form-text text-muted';
        }
    }
    
    reasonTextarea.addEventListener('input', updateCharCounter);
    updateCharCounter(); // Initial call
});
</script>
@endpush
@endsection