@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header with Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-comments text-primary me-2"></i>Customer Feedback / පාරිභෝගික ප්‍රතිපෝෂණ
            </h2>
            <p class="text-muted mb-0">Collect feedback from completed functions / සම්පූර්ණ කළ උත්සවවලින් ප්‍රතිපෝෂණ ලබා ගන්න</p>
        </div>
        <button class="btn btn-success btn-lg px-4" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
            <i class="fas fa-plus-circle me-2"></i>Add Manually / අතින් එකතු කරන්න
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff9800, #f57c00);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-phone-volume fa-2x mb-2"></i>
                    <h1 class="display-4 fw-bold mb-1">{{ $feedbacksByStatus['pending']->count() }}</h1>
                    <p class="mb-0 fs-5">ප්‍රතිපෝෂණ ගත යුතුයි / Need to Take Feedback</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4caf50, #388e3c);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h1 class="display-4 fw-bold mb-1">{{ $feedbacksByStatus['completed']->count() }}</h1>
                    <p class="mb-0 fs-5">ප්‍රතිපෝෂණ ලබාගත්තා / Feedback Taken</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Accordion Categories -->
    <div class="accordion" id="feedbackAccordion">
        
        <!-- Need to Take Feedback -->
        <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button py-3 px-4 fw-bold" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapse_pending"
                        style="background: white; border-left: 5px solid #ff9800; color: #333;">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-phone-volume me-3 text-warning"></i>
                        <span class="flex-grow-1">ප්‍රතිපෝෂණ ගත යුතුයි / Need to Take Feedback</span>
                        <span class="badge bg-warning text-dark rounded-pill">{{ $feedbacksByStatus['pending']->count() }}</span>
                    </span>
                </button>
            </h2>
            <div id="collapse_pending" class="accordion-collapse collapse show" data-bs-parent="#feedbackAccordion">
                <div class="accordion-body p-0 bg-white">
                    @forelse($feedbacksByStatus['pending'] as $feedback)
                        @include('feedback.partials.feedback-row', ['feedback' => $feedback])
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">ප්‍රතිපෝෂණ ගත යුතු අය නැත / No pending feedback</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Feedback Taken -->
        <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-3 px-4 fw-bold" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapse_completed"
                        style="background: white; border-left: 5px solid #4caf50; color: #333;">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-check-circle me-3 text-success"></i>
                        <span class="flex-grow-1">ප්‍රතිපෝෂණ ලබාගත්තා / Feedback Taken</span>
                        <span class="badge bg-success rounded-pill">{{ $feedbacksByStatus['completed']->count() }}</span>
                    </span>
                </button>
            </h2>
            <div id="collapse_completed" class="accordion-collapse collapse" data-bs-parent="#feedbackAccordion">
                <div class="accordion-body p-0 bg-white">
                    @forelse($feedbacksByStatus['completed'] as $feedback)
                        @include('feedback.partials.feedback-row', ['feedback' => $feedback])
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">ප්‍රතිපෝෂණ ලබාගත් අය නැත / No completed feedback</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('feedback.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <h4 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Add Feedback Entry
                        <small class="d-block" style="font-size: 14px;">ප්‍රතිපෝෂණ ඇතුළත් කිරීම එකතු කරන්න</small>
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Customer Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user text-primary me-1"></i> Customer Name / පාරිභෝගික නම
                        </label>
                        <input type="text" name="customer_name" class="form-control form-control-lg" 
                               placeholder="Enter customer name" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-phone text-success me-1"></i> Phone Number / දුරකථන අංකය
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="contact_number" class="form-control form-control-lg" 
                               placeholder="0771234567" required>
                    </div>

                    <!-- Function Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar-check text-info me-1"></i> Function Type / උත්සව වර්ගය
                        </label>
                        <select name="function_type" class="form-select form-select-lg">
                            <option value="">Select / තෝරන්න</option>
                            <option value="Wedding">Wedding / මංගල උත්සවය</option>
                            <option value="Birthday">Birthday / උපන්දින සාදය</option>
                            <option value="Corporate">Corporate / ආයතනික</option>
                            <option value="Room Stay">Room Stay / කාමර නවාතැන</option>
                            <option value="Day Out">Day Out / දවසේ සංචාරය</option>
                            <option value="Other">Other / වෙනත්</option>
                        </select>
                    </div>

                    <!-- Function Date -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-warning me-1"></i> Function Date / උත්සව දිනය
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="function_date" class="form-control form-control-lg" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel / අවලංගු</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-1"></i> Add / එකතු කරන්න
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Feedback Modal (for recording feedback) -->
<div class="modal fade" id="recordFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordFeedbackForm" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h4 class="modal-title">
                        <i class="fas fa-star me-2"></i>Record Feedback
                        <small class="d-block" style="font-size: 14px;">ප්‍රතිපෝෂණ සටහන් කරන්න</small>
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <p class="mb-3">
                        <strong>Customer:</strong> <span id="modal_customer_name"></span><br>
                        <strong>Phone:</strong> <span id="modal_phone"></span>
                    </p>

                    <!-- Star Rating -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-star text-warning me-1"></i> Rating / ශ්‍රේණිගත කිරීම
                            <span class="text-danger">*</span>
                        </label>
                        <div class="star-rating d-flex gap-2 justify-content-center py-3">
                            @for($i = 1; $i <= 5; $i++)
                            <label class="star-label">
                                <input type="radio" name="rating" value="{{ $i }}" class="d-none" required>
                                <i class="fas fa-star fa-3x text-muted star-icon" data-rating="{{ $i }}"></i>
                            </label>
                            @endfor
                        </div>
                    </div>

                    <!-- Feedback Notes -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment text-info me-1"></i> Feedback Notes / ප්‍රතිපෝෂණ සටහන්
                        </label>
                        <textarea name="feedback_notes" class="form-control" rows="3" 
                                  placeholder="What did the customer say? / පාරිභෝගිකයා කුමක් කීවේද?"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel / අවලංගු</button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check me-1"></i> Save Feedback / ප්‍රතිපෝෂණ සුරකින්න
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.star-icon {
    cursor: pointer;
    transition: all 0.2s;
}
.star-icon:hover,
.star-icon.active {
    color: #ffc107 !important;
    transform: scale(1.1);
}
.star-rating:hover .star-icon {
    color: #ffc107 !important;
}
.star-rating .star-label:hover ~ .star-label .star-icon {
    color: #6c757d !important;
}
</style>

<script>
function openRecordFeedbackModal(feedbackId, customerName, phone) {
    document.getElementById('recordFeedbackForm').action = '/feedback/' + feedbackId + '/complete';
    document.getElementById('modal_customer_name').textContent = customerName;
    document.getElementById('modal_phone').textContent = phone;
    
    // Reset stars
    document.querySelectorAll('.star-icon').forEach(star => {
        star.classList.remove('active');
        star.classList.add('text-muted');
    });
    document.querySelectorAll('input[name="rating"]').forEach(input => input.checked = false);
    
    new bootstrap.Modal(document.getElementById('recordFeedbackModal')).show();
}

// Star rating interaction
document.querySelectorAll('.star-icon').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.querySelectorAll('.star-icon').forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('text-muted');
                s.classList.add('active');
            } else {
                s.classList.add('text-muted');
                s.classList.remove('active');
            }
        });
        // Check the corresponding radio
        document.querySelector(`input[name="rating"][value="${rating}"]`).checked = true;
    });
});
</script>
@endsection
