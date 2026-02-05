<div class="card mb-4 shadow lead-tile" style="border: 1px solid #e0e0e0; border-radius: 12px;">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <!-- Left: Customer Info -->
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-user text-primary fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $lead->customer_name ?? 'Unknown Guest' }}</h5>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>{{ $lead->inquiry_date->diffForHumans() }}
                            @if($lead->source)
                                <span class="ms-2 badge bg-light text-dark">{{ ucfirst($lead->source->value ?? '') }}</span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <!-- Center: Phone Number (LARGE) -->
            <div class="col-md-4 text-center">
                <a href="tel:{{ $lead->country_code }}{{ $lead->phone_number }}" class="text-decoration-none">
                    <div class="bg-success bg-opacity-10 rounded-3 py-2 px-3">
                        <i class="fas fa-phone-alt text-success me-2"></i>
                        <span class="fs-4 fw-bold text-success">{{ $lead->formatted_phone }}</span>
                    </div>
                </a>
                <div class="mt-2 d-flex justify-content-center gap-2">
                    <a href="{{ $lead->whatsapp_link }}" target="_blank" class="btn btn-success btn-sm px-3">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a href="tel:{{ $lead->country_code }}{{ $lead->phone_number }}" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-phone me-1"></i> Call
                    </a>
                </div>
            </div>

            <!-- Right: Dates & Status -->
            <div class="col-md-4">
                <div class="d-flex flex-column align-items-end">
                    <!-- Check-in/out dates -->
                    @if($lead->check_in)
                        <div class="mb-2 text-end">
                            <span class="badge bg-info text-white px-3 py-2">
                                <i class="fas fa-calendar-check me-1"></i>
                                {{ $lead->check_in->format('M d') }}
                                @if($lead->check_out) - {{ $lead->check_out->format('M d') }} @endif
                            </span>
                        </div>
                    @endif

                    <!-- Follow-up alert -->
                    @if($lead->next_follow_up_at)
                        <div class="mb-2">
                            <span class="badge {{ $lead->is_overdue ? 'bg-danger' : 'bg-warning text-dark' }} px-3 py-2">
                                <i class="fas fa-bell me-1"></i>
                                Follow-up: {{ $lead->next_follow_up_at->format('M d, h:i A') }}
                            </span>
                        </div>
                    @endif

                    <!-- Status Dropdown -->
                    <form action="{{ url('/leads/' . $lead->id . '/update-status') }}" method="POST">
                        @csrf
                        <select name="status" class="form-select form-select-sm fw-bold" style="min-width: 180px;" onchange="this.form.submit()">
                            <option value="need_to_contact" {{ $lead->status->value == 'need_to_contact' ? 'selected' : '' }}>📞 Need To Contact</option>
                            <option value="not_respond" {{ $lead->status->value == 'not_respond' ? 'selected' : '' }}>❌ Not Respond</option>
                            <option value="called_send_details" {{ $lead->status->value == 'called_send_details' ? 'selected' : '' }}>📧 Called & Sent</option>
                            <option value="booked" {{ $lead->status->value == 'booked' ? 'selected' : '' }}>✅ Booked</option>
                            <option value="loss" {{ $lead->status->value == 'loss' ? 'selected' : '' }}>🚫 Loss</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Additional Details Row -->
        <div class="border-top mt-3 pt-3">
            <div class="row">
                @if($lead->adults || $lead->children)
                <div class="col-auto">
                    <small class="text-muted">
                        <i class="fas fa-users me-1"></i>
                        {{ $lead->adults ?? 0 }} Adults
                        @if($lead->children), {{ $lead->children }} Children @endif
                    </small>
                </div>
                @endif
                @if($lead->requirements)
                <div class="col">
                    <small class="text-muted">
                        <i class="fas fa-comment me-1"></i>
                        {{ Str::limit($lead->requirements, 100) }}
                    </small>
                </div>
                @endif
            </div>

            <!-- Quick Note Input -->
            <div class="mt-3">
                <form action="{{ url('/leads/' . $lead->id . '/add-note') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="note" class="form-control form-control-sm" 
                           placeholder="Add a quick note... (e.g., Called, no answer)" required>
                    <button type="submit" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </form>
            </div>

            <!-- Recent Notes -->
            @if($lead->notes && $lead->notes->count() > 0)
            <div class="mt-3">
                <small class="text-muted fw-bold"><i class="fas fa-history me-1"></i> Recent Updates:</small>
                <div class="mt-2" style="max-height: 120px; overflow-y: auto;">
                    @foreach($lead->notes->take(3) as $note)
                    <div class="d-flex align-items-start mb-2 ps-2 border-start border-2 border-primary">
                        <div class="flex-grow-1">
                            <small class="text-dark">{{ $note->note }}</small>
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $note->created_at->format('M d, h:i A') }}
                                    @if($note->user)
                                        <span class="ms-2"><i class="fas fa-user me-1"></i>{{ $note->user->name }}</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
