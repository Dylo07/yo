<div class="card mb-4 shadow" style="border: 1px solid #e0e0e0; border-radius: 12px;">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <!-- Left: Customer Info -->
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-user text-primary fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $feedback->customer_name }}</h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>{{ $feedback->function_date->format('M d, Y') }}
                            @if($feedback->function_type)
                                <span class="ms-2 badge bg-light text-dark">{{ $feedback->function_type }}</span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <!-- Center: Phone Number (LARGE) -->
            <div class="col-md-4 text-center">
                <a href="tel:{{ $feedback->contact_number }}" class="text-decoration-none">
                    <div class="bg-success bg-opacity-10 rounded-3 py-2 px-3">
                        <i class="fas fa-phone-alt text-success me-2"></i>
                        <span class="fs-4 fw-bold text-success">{{ $feedback->formatted_phone }}</span>
                    </div>
                </a>
                <div class="mt-2 d-flex justify-content-center gap-2">
                    <a href="{{ $feedback->whatsapp_link }}" target="_blank" class="btn btn-success btn-sm px-3">
                        <i class="fab fa-whatsapp me-1"></i> වට්ස්ඇප්
                    </a>
                    <a href="tel:{{ $feedback->contact_number }}" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-phone me-1"></i> ඇමතුම
                    </a>
                </div>
            </div>

            <!-- Right: Status & Actions -->
            <div class="col-md-4">
                <div class="d-flex flex-column align-items-end">
                    @if($feedback->status === 'pending')
                        <!-- Record Feedback Button -->
                        <button type="button" class="btn btn-primary btn-lg mb-2" 
                                onclick="openRecordFeedbackModal({{ $feedback->id }}, '{{ $feedback->customer_name }}', '{{ $feedback->formatted_phone }}')">
                            <i class="fas fa-star me-1"></i> ප්‍රතිපෝෂණ ගන්න / Take Feedback
                        </button>
                    @else
                        <!-- Show Rating -->
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $feedback->rating ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                            <span class="ms-2 badge bg-success">Completed</span>
                        </div>
                        @if($feedback->feedback_notes)
                            <small class="text-muted text-end" style="max-width: 200px;">
                                "{{ Str::limit($feedback->feedback_notes, 50) }}"
                            </small>
                        @endif
                        @if($feedback->feedbackTakenByUser)
                            <small class="text-muted mt-1">
                                <i class="fas fa-user me-1"></i>{{ $feedback->feedbackTakenByUser->name }}
                                <i class="fas fa-clock ms-2 me-1"></i>{{ $feedback->feedback_taken_at->format('M d, h:i A') }}
                            </small>
                        @endif
                    @endif

                    <!-- Admin Delete Button -->
                    @if(auth()->user() && auth()->user()->checkAdmin())
                    <form action="{{ route('feedback.destroy', $feedback) }}" method="POST" class="mt-2" 
                          onsubmit="return confirm('ඔබට මෙම ප්‍රතිපෝෂණය මකා දැමීමට අවශ්‍යද? / Are you sure you want to delete this?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i> මකන්න / Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Info Row -->
        @if($feedback->booking)
        <div class="border-top mt-3 pt-3">
            <small class="text-muted">
                <i class="fas fa-link me-1"></i> Linked to Booking #{{ $feedback->booking->id }}
                @if($feedback->booking->guest_count)
                    <span class="ms-3"><i class="fas fa-users me-1"></i>{{ $feedback->booking->guest_count }}</span>
                @endif
            </small>
        </div>
        @endif
    </div>
</div>
