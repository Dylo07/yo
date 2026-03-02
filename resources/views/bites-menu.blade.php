@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Card -->
            <div class="card shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                <!-- Card Header -->
                <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3" style="font-size: 2.5rem;">
                            <i class="fas fa-cookie-bite"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">Edit Bites Menu</h3>
                            <p class="mb-0 opacity-75" style="font-size: 0.9rem;">
                                <i class="fas fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($booking->start)->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4">
                    <!-- Booking Information -->
                    <div class="alert alert-light border-start border-4 border-primary mb-4" style="border-radius: 8px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-2" style="color: #3b82f6;">
                                        <i class="fas fa-user-circle fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block mb-1">Guest Name</small>
                                        <strong class="d-block">{{ $booking->name }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-2" style="color: #10b981;">
                                        <i class="fas fa-calendar-check fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block mb-1">Function Type</small>
                                        <strong class="d-block">{{ $booking->function_type }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-2" style="color: #8b5cf6;">
                                        <i class="fas fa-users fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block mb-1">Guest Count</small>
                                        <strong class="d-block">{{ $booking->guest_count }} guests</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="me-2" style="color: #ec4899;">
                                        <i class="fas fa-phone fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block mb-1">Contact Number</small>
                                        <strong class="d-block">{{ $booking->contact_number }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bites Menu Form -->
                    <form method="POST" action="{{ route('bites-menu.update') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="mb-4">
                            <label for="bites_details" class="form-label fw-bold" style="color: #1e293b; font-size: 1.1rem;">
                                <i class="fas fa-cookie-bite me-2" style="color: #f59e0b;"></i>Bites Details
                            </label>
                            <textarea 
                                class="form-control form-control-lg @error('bites_details') is-invalid @enderror" 
                                id="bites_details" 
                                name="bites_details" 
                                rows="6"
                                placeholder="Enter bites menu details here... (e.g., Samosas, Spring Rolls, Cutlets, Pastries, etc.)"
                                style="border-radius: 8px; border: 2px solid #e5e7eb; font-size: 1rem; resize: vertical;"
                            >{{ old('bites_details', $booking->bites_details) }}</textarea>
                            @error('bites_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>Describe the bites/snacks that will be served during this function
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 flex-wrap">
                            <button type="submit" class="btn btn-lg flex-fill" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                <i class="fas fa-save me-2"></i>Save Bites Details
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-secondary flex-fill" style="border-radius: 8px; border-width: 2px; font-weight: 600;">
                                <i class="fas fa-arrow-left me-2"></i>Go Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-4 d-flex gap-2 flex-wrap">
                <a href="/calendar" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                    <i class="fas fa-calendar me-1"></i>View Calendar
                </a>
                <a href="/food-menu?date={{ \Carbon\Carbon::parse($booking->start)->format('Y-m-d') }}&booking_id={{ $booking->id }}" target="_blank" class="btn btn-sm btn-outline-success" style="border-radius: 8px;">
                    <i class="fas fa-utensils me-1"></i>Edit Food Menu
                </a>
                <a href="/home" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                    <i class="fas fa-home me-1"></i>Home Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
    }
    
    .btn:hover {
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    
    .card {
        animation: fadeInUp 0.5s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
