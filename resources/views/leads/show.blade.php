@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Back Button & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('leads.index') }}" class="btn btn-outline-dark btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Leads
            </a>
            <h3 class="mb-0">
                <i class="fas fa-user me-2"></i>{{ $lead->customer_name ?? 'Unknown Customer' }}
                <span class="badge bg-{{ $lead->status->badgeColor() }} ms-2">
                    <i class="{{ $lead->status->icon() }} me-1"></i>{{ $lead->status->label() }}
                </span>
            </h3>
        </div>
        <div class="btn-group">
            <a href="{{ $lead->whatsapp_link }}" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> WhatsApp
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#callModal">
                <i class="fas fa-phone me-1"></i> Record Call
            </button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#noteModal">
                <i class="fas fa-sticky-note me-1"></i> Add Note
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column - Lead Details -->
        <div class="col-lg-5">
            <!-- Contact Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Phone:</th>
                            <td>
                                <a href="{{ $lead->whatsapp_link }}" target="_blank" class="text-success">
                                    <i class="fab fa-whatsapp"></i> {{ $lead->formatted_phone }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Source:</th>
                            <td>
                                <span class="badge bg-{{ $lead->source->color() }}">
                                    <i class="{{ $lead->source->icon() }} me-1"></i>{{ $lead->source->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Inquiry Date:</th>
                            <td>{{ $lead->inquiry_date->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Interest Level:</th>
                            <td>
                                @if($lead->interest_level)
                                    <span class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $lead->interest_level ? '' : '-o' }}"></i>
                                        @endfor
                                    </span>
                                    ({{ $lead->interest_level }}/5)
                                @else
                                    <span class="text-muted">Not rated</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Assigned To:</th>
                            <td>
                                @if($lead->assignee)
                                    <span class="badge bg-info">{{ $lead->assignee->name }}</span>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Stay Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Stay Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Check-in:</th>
                            <td>{{ $lead->check_in ? $lead->check_in->format('M d, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Check-out:</th>
                            <td>{{ $lead->check_out ? $lead->check_out->format('M d, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>
                                @if($lead->stay_duration)
                                    <span class="badge bg-secondary">{{ $lead->stay_duration }} Night(s)</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Guests:</th>
                            <td>
                                <i class="fas fa-user"></i> {{ $lead->adults }} Adults
                                @if($lead->children > 0)
                                    , <i class="fas fa-child"></i> {{ $lead->children }} Children
                                @endif
                            </td>
                        </tr>
                    </table>
                    @if($lead->requirements)
                        <hr>
                        <h6 class="fw-bold">Requirements / Special Requests:</h6>
                        <p class="mb-0 text-muted">{{ $lead->requirements }}</p>
                    @endif
                </div>
            </div>

            <!-- Status & Actions Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Status & Actions</h5>
                </div>
                <div class="card-body">
                    <!-- Current Status -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Status</label>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $lead->status->badgeColor() }} fs-6 me-2">
                                <i class="{{ $lead->status->icon() }} me-1"></i>{{ $lead->status->label() }}
                            </span>
                            @if($lead->is_overdue)
                                <span class="badge bg-danger">OVERDUE</span>
                            @endif
                        </div>
                    </div>

                    <!-- Follow-up -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Next Follow-up</label>
                        @if($lead->next_follow_up_at)
                            <p class="mb-0 {{ $lead->is_overdue ? 'text-danger fw-bold' : '' }}">
                                <i class="fas fa-clock"></i> {{ $lead->next_follow_up_at->format('M d, Y h:i A') }}
                                <br><small class="text-muted">{{ $lead->next_follow_up_at->diffForHumans() }}</small>
                            </p>
                        @else
                            <p class="text-muted mb-0">No follow-up scheduled</p>
                        @endif
                    </div>

                    <hr>

                    <!-- Quick Status Update -->
                    <form action="{{ route('leads.update-status', $lead) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Update Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status['value'] }}" 
                                            {{ $lead->status->value === $status['value'] ? 'selected' : '' }}>
                                        {{ $status['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 lost-reason-group" style="{{ $lead->status->value === 'loss' ? '' : 'display:none;' }}">
                            <label class="form-label fw-bold">Lost Reason</label>
                            <select name="lost_reason" class="form-select">
                                <option value="">Select reason...</option>
                                @foreach($lostReasons as $reason)
                                    <option value="{{ $reason['value'] }}"
                                            {{ $lead->lost_reason?->value === $reason['value'] ? 'selected' : '' }}>
                                        {{ $reason['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Schedule Follow-up</label>
                            <input type="datetime-local" name="next_follow_up_at" class="form-control"
                                   value="{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Note (Optional)</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fas fa-save me-1"></i> Update Status
                        </button>
                    </form>

                    <hr>

                    <!-- Assign -->
                    <form action="{{ route('leads.assign', $lead) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ $lead->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-dark w-100">
                            <i class="fas fa-user-tag me-1"></i> Update Assignment
                        </button>
                    </form>

                    @if($lead->status->isConvertible())
                        <hr>
                        <form action="{{ route('leads.convert', $lead) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-circle me-1"></i> Mark as Booked
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Linked Booking -->
            @if($lead->booking)
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Linked Booking</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Booking #{{ $lead->booking->id }}</strong></p>
                        <p class="mb-1">{{ $lead->booking->name }}</p>
                        <p class="mb-0 text-muted">
                            {{ $lead->booking->start?->format('M d') }} - {{ $lead->booking->end?->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Notes History -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activity History</h5>
                    <span class="badge bg-light text-dark">{{ $lead->notes->count() }} entries</span>
                </div>
                <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                    @forelse($lead->notes as $note)
                        <div class="border-bottom p-3 {{ $note->is_system ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-{{ $note->type->color() }} me-2">
                                        <i class="{{ $note->type->icon() }} me-1"></i>{{ $note->type->label() }}
                                    </span>
                                    <strong>{{ $note->user->name ?? 'System' }}</strong>
                                </div>
                                <small class="text-muted">
                                    {{ $note->created_at->format('M d, Y h:i A') }}
                                    <br>{{ $note->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <p class="mb-0 {{ $note->is_system ? 'text-muted fst-italic' : '' }}">
                                {{ $note->note }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No activity recorded yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Call Modal -->
<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leads.record-call', $lead) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-phone me-2"></i>Record Call Outcome</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Call Outcome <span class="text-danger">*</span></label>
                        <textarea name="outcome" class="form-control" rows="4" 
                                  placeholder="What was discussed? What's the result?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Update Status</label>
                        <select name="status" class="form-select">
                            <option value="">Keep Current ({{ $lead->status->label() }})</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Schedule Follow-up</label>
                        <input type="datetime-local" name="next_follow_up_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Call
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leads.add-note', $lead) }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Add Note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Note Type</label>
                        <select name="type" class="form-select">
                            @foreach($noteTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Note <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="4" 
                                  placeholder="Enter your note..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-1"></i> Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const lostGroup = document.querySelector('.lost-reason-group');
    if (this.value === 'loss') {
        lostGroup.style.display = 'block';
    } else {
        lostGroup.style.display = 'none';
    }
});
</script>
@endsection
