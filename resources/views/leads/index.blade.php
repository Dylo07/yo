@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    
    <!-- SIMPLE HEADER with BIG ADD BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-4 py-3">
        <h4 class="mb-0">
            <i class="fas fa-users text-primary"></i> 
            <strong>Inquiries</strong>
            <small class="text-muted d-block" style="font-size: 14px;">විමසීම් / Guest Inquiries</small>
        </h4>
        <button class="btn btn-success btn-lg px-4 py-3" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus fa-lg me-2"></i> 
            <span class="fw-bold">ADD NEW</span>
            <small class="d-block">අලුත් එකක් එකතු කරන්න</small>
        </button>
    </div>

    <!-- BIG STAT CARDS - Easy to understand -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('leads.index', ['filter' => 'need_to_contact']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ff9800, #f57c00);">
                    <div class="card-body text-white text-center py-4">
                        <i class="fas fa-phone-alt fa-2x mb-2"></i>
                        <h1 class="display-4 fw-bold mb-0">{{ $stats['pending_calls'] }}</h1>
                        <div class="fw-bold">NEED TO CONTACT</div>
                        <small>සම්බන්ධ වීමට</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('leads.index', ['filter' => 'today']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #2196f3, #1976d2);">
                    <div class="card-body text-white text-center py-4">
                        <i class="fas fa-calendar-day fa-2x mb-2"></i>
                        <h1 class="display-4 fw-bold mb-0">{{ $stats['total_today'] }}</h1>
                        <div class="fw-bold">TODAY</div>
                        <small>අද</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('leads.index', ['filter' => 'overdue']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f44336, #d32f2f);">
                    <div class="card-body text-white text-center py-4">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <h1 class="display-4 fw-bold mb-0">{{ $stats['overdue'] }}</h1>
                        <div class="fw-bold">LATE!</div>
                        <small>ප්‍රමාදයි</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('leads.index', ['filter' => 'booked']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4caf50, #388e3c);">
                    <div class="card-body text-white text-center py-4">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h1 class="display-4 fw-bold mb-0">{{ $stats['won_this_month'] }}</h1>
                        <div class="fw-bold">BOOKED</div>
                        <small>වෙන්කළා</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fs-5">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ACCORDION STYLE CATEGORIES -->
    <div class="accordion-container">
        <div class="accordion" id="leadsAccordion">
            
            <!-- Need To Contact -->
            <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button py-3 px-4 fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse_need_to_contact"
                            style="background: white; border-left: 5px solid #ff9800; color: #333;">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-phone-alt me-3 text-warning"></i>
                            <span class="flex-grow-1">සම්බන්ධ වීමට / Need To Contact</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $leadsByStatus['need_to_contact']->count() }}</span>
                        </span>
                    </button>
                </h2>
                <div id="collapse_need_to_contact" class="accordion-collapse collapse show" data-bs-parent="#leadsAccordion">
                    <div class="accordion-body p-0 bg-white">
                        @forelse($leadsByStatus['need_to_contact'] as $lead)
                            @include('leads.partials.lead-row', ['lead' => $lead])
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">නව විමසීම් නැත / No new inquiries</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Not Respond -->
            <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3 px-4 fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse_not_respond"
                            style="background: white; border-left: 5px solid #6c757d; color: #333;">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-phone-slash me-3 text-secondary"></i>
                            <span class="flex-grow-1">පිළිතුරු නැත / Not Respond</span>
                            <span class="badge bg-secondary rounded-pill">{{ $leadsByStatus['not_respond']->count() }}</span>
                        </span>
                    </button>
                </h2>
                <div id="collapse_not_respond" class="accordion-collapse collapse" data-bs-parent="#leadsAccordion">
                    <div class="accordion-body p-0 bg-white">
                        @forelse($leadsByStatus['not_respond'] as $lead)
                            @include('leads.partials.lead-row', ['lead' => $lead])
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">මෙම කාණ්ඩයේ විමසීම් නැත / No leads in this category</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Called & Send Details -->
            <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3 px-4 fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse_called_send"
                            style="background: white; border-left: 5px solid #0d6efd; color: #333;">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-paper-plane me-3 text-primary"></i>
                            <span class="flex-grow-1">ඇමතූ සහ යැව්වා / Called & Sent</span>
                            <span class="badge bg-primary rounded-pill">{{ $leadsByStatus['called_send_details']->count() }}</span>
                        </span>
                    </button>
                </h2>
                <div id="collapse_called_send" class="accordion-collapse collapse" data-bs-parent="#leadsAccordion">
                    <div class="accordion-body p-0 bg-white">
                        @forelse($leadsByStatus['called_send_details'] as $lead)
                            @include('leads.partials.lead-row', ['lead' => $lead])
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">මෙම කාණ්ඩයේ විමසීම් නැත / No leads in this category</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Booked -->
            <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3 px-4 fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse_booked"
                            style="background: white; border-left: 5px solid #198754; color: #333;">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-check-circle me-3 text-success"></i>
                            <span class="flex-grow-1">වෙන්කළා / Booked</span>
                            <span class="badge bg-success rounded-pill">{{ $leadsByStatus['booked']->count() }}</span>
                        </span>
                    </button>
                </h2>
                <div id="collapse_booked" class="accordion-collapse collapse" data-bs-parent="#leadsAccordion">
                    <div class="accordion-body p-0 bg-white">
                        @forelse($leadsByStatus['booked'] as $lead)
                            @include('leads.partials.lead-row', ['lead' => $lead])
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">වෙන්කළා විමසීම් නැත / No booked leads</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Loss -->
            <div class="accordion-item mb-3 shadow-sm border-0 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3 px-4 fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse_loss"
                            style="background: white; border-left: 5px solid #dc3545; color: #333;">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-times-circle me-3 text-danger"></i>
                            <span class="flex-grow-1">අහිමි / Loss</span>
                            <span class="badge bg-danger rounded-pill">{{ $leadsByStatus['loss']->count() }}</span>
                        </span>
                    </button>
                </h2>
                <div id="collapse_loss" class="accordion-collapse collapse" data-bs-parent="#leadsAccordion">
                    <div class="accordion-body p-0 bg-white">
                        @forelse($leadsByStatus['loss'] as $lead)
                            @include('leads.partials.lead-row', ['lead' => $lead])
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">අහිමි විමසීම් නැත / No lost leads</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ============ ADD NEW LEAD MODAL (SIMPLIFIED) ============ -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('leads.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <h4 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Add New Inquiry
                        <small class="d-block" style="font-size: 14px;">නව විමසීමක් එකතු කරන්න</small>
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- PHONE NUMBER - Most Important -->
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-bold">
                            <i class="fas fa-phone text-success me-1"></i> Phone Number / දුරකථන අංකය
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <select name="country_code" class="form-select" style="max-width: 120px;">
                                <option value="+94">+94</option>
                                <option value="+91">+91</option>
                                <option value="+44">+44</option>
                                <option value="+1">+1</option>
                            </select>
                            <input type="text" name="phone_number" class="form-control form-control-lg" 
                                   placeholder="771234567" required style="font-size: 1.5rem;">
                        </div>
                    </div>

                    <!-- SOURCE - Big Buttons -->
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-bold">
                            <i class="fas fa-share-alt text-primary me-1"></i> Where did they contact? / ඔවුන් කොහෙන් ඇවිත්ද?
                            <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <input type="radio" class="btn-check" name="source" id="src_whatsapp" value="whatsapp" checked>
                            <label class="btn btn-outline-success btn-lg px-4" for="src_whatsapp">
                                <i class="fab fa-whatsapp fa-2x d-block mb-1"></i> WhatsApp
                            </label>
                            
                            <input type="radio" class="btn-check" name="source" id="src_facebook" value="facebook">
                            <label class="btn btn-outline-primary btn-lg px-4" for="src_facebook">
                                <i class="fab fa-facebook fa-2x d-block mb-1"></i> Facebook
                            </label>
                            
                            <input type="radio" class="btn-check" name="source" id="src_instagram" value="instagram">
                            <label class="btn btn-outline-danger btn-lg px-4" for="src_instagram">
                                <i class="fab fa-instagram fa-2x d-block mb-1"></i> Instagram
                            </label>
                            
                            <input type="radio" class="btn-check" name="source" id="src_call" value="phone_call">
                            <label class="btn btn-outline-info btn-lg px-4" for="src_call">
                                <i class="fas fa-phone fa-2x d-block mb-1"></i> Phone
                            </label>
                            
                            <input type="radio" class="btn-check" name="source" id="src_walkin" value="walk_in">
                            <label class="btn btn-outline-secondary btn-lg px-4" for="src_walkin">
                                <i class="fas fa-walking fa-2x d-block mb-1"></i> Walk-in
                            </label>
                        </div>
                    </div>

                    <!-- CUSTOMER NAME -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fs-5 fw-bold mb-0">
                                <i class="fas fa-user text-primary me-1"></i> Customer Name / ගනුදෙනුකරුගේ නම
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="name_unknown" checked onchange="toggleField('customer_name_input', this.checked)">
                                <label class="form-check-label text-muted" for="name_unknown">Unknown / නොදනී</label>
                            </div>
                        </div>
                        <input type="text" name="customer_name" id="customer_name_input" class="form-control form-control-lg" 
                               placeholder="Enter name / නම ලියන්න" disabled style="background-color: #f5f5f5;">
                    </div>

                    <!-- DATES ROW -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fs-5 fw-bold mb-0">
                                    <i class="fas fa-calendar-plus text-success me-1"></i> Check-in / එන දිනය
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkin_unknown" checked onchange="toggleField('check_in_input', this.checked)">
                                    <label class="form-check-label text-muted small" for="checkin_unknown">Unknown</label>
                                </div>
                            </div>
                            <input type="date" name="check_in" id="check_in_input" class="form-control form-control-lg" disabled style="background-color: #f5f5f5;">
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fs-5 fw-bold mb-0">
                                    <i class="fas fa-calendar-minus text-danger me-1"></i> Check-out / යන දිනය
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkout_unknown" checked onchange="toggleField('check_out_input', this.checked)">
                                    <label class="form-check-label text-muted small" for="checkout_unknown">Unknown</label>
                                </div>
                            </div>
                            <input type="date" name="check_out" id="check_out_input" class="form-control form-control-lg" disabled style="background-color: #f5f5f5;">
                        </div>
                    </div>

                    <!-- GUESTS ROW -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fs-5 fw-bold mb-0">
                                    <i class="fas fa-user-friends text-primary me-1"></i> Adults / වැඩිහිටියන්
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="adults_unknown" checked onchange="toggleField('adults_input', this.checked)">
                                    <label class="form-check-label text-muted small" for="adults_unknown">Unknown</label>
                                </div>
                            </div>
                            <input type="number" name="adults" id="adults_input" class="form-control form-control-lg text-center" 
                                   value="" min="0" max="20" style="font-size: 1.5rem; background-color: #f5f5f5;" disabled>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fs-5 fw-bold mb-0">
                                    <i class="fas fa-child text-info me-1"></i> Children / ළමයින්
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="children_unknown" checked onchange="toggleField('children_input', this.checked)">
                                    <label class="form-check-label text-muted small" for="children_unknown">Unknown</label>
                                </div>
                            </div>
                            <input type="number" name="children" id="children_input" class="form-control form-control-lg text-center" 
                                   value="" min="0" max="20" style="font-size: 1.5rem; background-color: #f5f5f5;" disabled>
                        </div>
                    </div>

                    <!-- NOTES -->
                    <div class="mb-3">
                        <label class="form-label fs-5 fw-bold">
                            <i class="fas fa-sticky-note text-warning me-1"></i> Notes / සටහන්
                        </label>
                        <textarea name="requirements" class="form-control" rows="2" 
                                  placeholder="What do they want? / ඔවුන්ට අවශ්‍ය කුමක්ද?"></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel / අවලංගු
                    </button>
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-save me-1"></i> SAVE / සුරකින්න
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ ACTION MODAL (SIMPLIFIED) ============ -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="actionForm" method="POST" action="">
                @csrf
                <input type="hidden" name="lead_id" id="action_lead_id">
                <div class="modal-header bg-primary text-white py-3">
                    <h4 class="modal-title">
                        <i class="fas fa-tasks me-2"></i>Action
                        <small class="d-block" style="font-size: 14px;">ක්‍රියාමාර්ගය</small>
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fs-5 mb-3">Customer: <strong id="action_customer_name"></strong></p>
                    
                    <!-- What Happened (Optional) -->
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-bold">What happened? / මොකද වුනේ? <small class="text-muted fw-normal">(Optional)</small></label>
                        <textarea name="outcome" class="form-control form-control-lg" rows="2" 
                                  placeholder="Write what you talked about..."></textarea>
                    </div>

                    <!-- Status Update - 4 Simple Options -->
                    <div class="mb-4">
                        <label class="form-label fs-5 fw-bold">Result / ප්‍රතිඵලය</label>
                        <div class="d-flex flex-column gap-2">
                            <input type="radio" class="btn-check" name="status" id="status_not_respond" value="not_respond">
                            <label class="btn btn-outline-secondary btn-lg w-100 py-3 text-start" for="status_not_respond">
                                <i class="fas fa-phone-slash fa-lg me-3"></i> Not Respond / පිළිතුරු නැත
                            </label>
                            
                            <input type="radio" class="btn-check" name="status" id="status_called_send" value="called_send_details" checked>
                            <label class="btn btn-outline-info btn-lg w-100 py-3 text-start" for="status_called_send">
                                <i class="fas fa-paper-plane fa-lg me-3"></i> Called & Send Details / ඇමතූ සහ විස්තර යැව්වා
                            </label>
                            
                            <input type="radio" class="btn-check" name="status" id="status_booked" value="booked">
                            <label class="btn btn-outline-success btn-lg w-100 py-3 text-start" for="status_booked">
                                <i class="fas fa-check-circle fa-lg me-3"></i> Booked / වෙන්කළා
                            </label>
                            
                            <input type="radio" class="btn-check" name="status" id="status_loss" value="loss">
                            <label class="btn btn-outline-danger btn-lg w-100 py-3 text-start" for="status_loss">
                                <i class="fas fa-times-circle fa-lg me-3"></i> Loss (Not Interest) / අහිමි
                            </label>
                        </div>
                    </div>

                    <!-- Next Call Date - Only shows when "Not Respond" is selected -->
                    <div class="mb-3" id="followup_section" style="display: none;">
                        <label class="form-label fs-5 fw-bold text-warning">
                            <i class="fas fa-calendar-alt me-1"></i> Call again on / නැවත ඇමතීමට
                            <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="next_follow_up_at" id="next_follow_up_input" class="form-control form-control-lg">
                        <small class="text-muted">When should we call again? / නැවත ඇමතිය යුත්තේ කවදාද?</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-1"></i> SAVE / සුරකින්න
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}
/* Lead Tile Styles */
.lead-tile {
    border-left: 5px solid #0d6efd !important;
    background: #ffffff;
    margin-bottom: 20px !important;
}
.lead-tile:hover {
    border-left-color: #198754 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
.lead-tile .fs-4 {
    font-size: 1.5rem !important;
    letter-spacing: 1px;
}
.accordion-body {
    background-color: #f5f5f5;
    padding: 15px !important;
}
.btn-lg {
    padding: 0.75rem 1.25rem;
}
.form-control-lg {
    font-size: 1.1rem;
}
.badge {
    font-weight: 500;
}
.border-3 {
    border-width: 3px !important;
}
/* Accordion styles */
.accordion-item {
    border: 1px solid #dee2e6;
    background-color: transparent;
}
.accordion-button {
    font-weight: 600;
    color: #495057;
    background-color: #fff;
    box-shadow: none;
}
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #212529;
    box-shadow: none;
}
.accordion-button::after {
    /* Use default black caret */
    filter: none; 
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}
/* Ensure dropdowns aren't clipped */
.accordion-body {
    overflow: visible; 
}
.accordion-collapse {
    overflow: visible;
}

.lead-row:hover {
    background-color: #f8f9fa;
}
/* Status dropdown styles */
.dropdown-menu .status-option {
    transition: all 0.2s;
}
.dropdown-menu .status-option:hover {
    background-color: #f8f9fa;
    padding-left: 1.5rem; /* Indent on hover */
}
</style>

<script>
// Toggle field enabled/disabled based on Unknown checkbox
function toggleField(fieldId, isUnknown) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.disabled = isUnknown;
        field.style.backgroundColor = isUnknown ? '#f5f5f5' : '#ffffff';
        if (isUnknown) {
            field.value = '';
        }
    }
}

function openActionModal(leadId, customerName, currentStatus) {
    document.getElementById('action_lead_id').value = leadId;
    document.getElementById('action_customer_name').textContent = customerName;
    
    // Reset form
    document.getElementById('actionForm').reset();
    document.getElementById('followup_section').style.display = 'none';
    
    // Set default selection based on current status
    // Default to called_send_details if status is need_to_contact
    if (currentStatus === 'need_to_contact') {
        document.getElementById('status_called_send').checked = true;
    } else if (['not_respond', 'called_send_details', 'booked', 'loss'].includes(currentStatus)) {
        // Try to check the radio button matching the current status
        const radio = document.querySelector(`input[name="status"][value="${currentStatus}"]`);
        if (radio) radio.checked = true;
        
        // Show follow-up if not_respond
        if (currentStatus === 'not_respond') {
            document.getElementById('followup_section').style.display = 'block';
        }
    }
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

// Show/hide follow-up date based on status selection
document.querySelectorAll('input[name="status"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const followupSection = document.getElementById('followup_section');
        const followupInput = document.getElementById('next_follow_up_input');
        
        if (this.value === 'not_respond') {
            followupSection.style.display = 'block';
            followupInput.required = true;
        } else {
            followupSection.style.display = 'none';
            followupInput.required = false;
            followupInput.value = '';
        }
    });
});

// Set form action when modal opens and submit as regular form
document.getElementById('actionForm').addEventListener('submit', function(e) {
    const leadId = document.getElementById('action_lead_id').value;
    this.action = '/leads/' + leadId + '/update-status';
    // Let the form submit normally (no preventDefault)
});
</script>
@endsection
