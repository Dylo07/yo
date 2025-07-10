@extends('layouts.app')

@section('content')
@php
// Helper functions to replace $this-> calls
function getLeaveTypeBadgeClass($leaveType) {
    $colors = [
        'sick' => 'danger',
        'annual' => 'success', 
        'emergency' => 'warning',
        'personal' => 'info',
        'maternity' => 'primary',
        'other' => 'secondary'
    ];
    return $colors[$leaveType] ?? 'secondary';
}

function getLeaveTypeColor($leaveType) {
    $colors = [
        'sick' => 'danger',
        'annual' => 'success',
        'emergency' => 'warning', 
        'personal' => 'info',
        'maternity' => 'primary',
        'other' => 'secondary'
    ];
    return $colors[$leaveType] ?? 'secondary';
}
@endphp

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --info-color: #17a2b8;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .dashboard-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-card.pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.approved {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card.rejected {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .btn-custom {
        border-radius: 25px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        margin: 0.25rem;
    }

    .btn-primary-custom {
        background: linear-gradient(135deg, var(--secondary-color), #2980b9);
        color: white;
    }

    .btn-success-custom {
        background: linear-gradient(135deg, var(--success-color), #229954);
        color: white;
    }

    .btn-warning-custom {
        background: linear-gradient(135deg, var(--warning-color), #e67e22);
        color: white;
    }

    .btn-danger-custom {
        background: linear-gradient(135deg, var(--danger-color), #c0392b);
        color: white;
    }

    .leave-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .table thead th {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 600;
    }

    .table tbody tr {
        transition: background-color 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: border-color 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .modal-header {
        background: var(--primary-color);
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .timeline-item {
        border-left: 3px solid var(--secondary-color);
        padding-left: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        width: 10px;
        height: 10px;
        background: var(--secondary-color);
        border-radius: 50%;
        position: absolute;
        left: -6.5px;
        top: 0.5rem;
    }

    .leave-summary-widget {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .progress-custom {
        height: 8px;
        border-radius: 5px;
        background-color: #e9ecef;
    }

    .progress-bar-custom {
        border-radius: 5px;
        transition: width 0.6s ease;
    }

    @media (max-width: 768px) {
        .main-header h1 {
            font-size: 1.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-custom {
            width: 100%;
            margin: 0.25rem 0;
        }
    }
</style>

<!-- Main Header -->
<div class="main-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Leave Management System</h1>
                <p class="mb-0 opacity-75">Streamlined leave request management for your organization</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-light btn-custom" onclick="showCreateModal()">
                    <i class="fas fa-plus me-2"></i>New Leave Request
                </button>
                <a href="{{ route('leave-requests.calendar') }}" class="btn btn-info btn-custom">
                    <i class="fas fa-calendar me-2"></i>Calendar View
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Dashboard Summary -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $statistics['total'] ?? 0 }}</h3>
                        <p class="mb-0">Total Requests</p>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card pending">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $statistics['pending'] ?? 0 }}</h3>
                        <p class="mb-0">Pending</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card approved">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $statistics['approved'] ?? 0 }}</h3>
                        <p class="mb-0">Approved</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card rejected">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $statistics['rejected'] ?? 0 }}</h3>
                        <p class="mb-0">Rejected</p>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>





    <!-- Quick Summary Widget -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="leave-summary-widget">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Leave Distribution</h5>
                <div class="row">
                    @if(isset($leaveTypeStats) && !empty($leaveTypeStats))
                        @foreach($leaveTypeStats as $type => $percentage)
                            <div class="col-6 mb-2">
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $type)) }}</small>
                                <div class="progress-custom mb-2">
                                    <div class="progress-bar-custom bg-{{ getLeaveTypeColor($type) }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ $percentage }}%</small>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center text-muted py-3">
                            <i class="fas fa-chart-pie fa-2x mb-2"></i>
                            <div>No leave data available</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

   <!-- Simple Clean Version (Recommended) -->
<div class="col-md-6">
    <div class="leave-summary-widget">
        <h5 class="mb-3"><i class="fas fa-calendar-week me-2"></i>Upcoming Leaves</h5>
        @if(isset($upcomingLeaves) && $upcomingLeaves->count() > 0)
            @foreach($upcomingLeaves as $leave)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">
                            @if($leave->start_date->format('Y-m-d') == $leave->end_date->format('Y-m-d'))
                                {{ $leave->start_date->format('M d, Y') }}
                            @else
                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                            @endif
                        </small>
                        <span class="badge bg-{{ getLeaveTypeColor($leave->leave_type) }}">
                            {{ $leave->formatted_duration }}
                        </span>
                    </div>
                    <div class="fw-bold">
                        {{ $leave->person->name }} - {{ $leave->formatted_leave_type }}
                    </div>
                    @if($leave->is_datetime_based ?? false)
                        <div class="text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ $leave->start_datetime->format('g:i A') }} - {{ $leave->end_datetime->format('g:i A') }}
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-muted text-center py-3">
                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                <div>No upcoming leaves</div>
            </div>
        @endif
    </div>
</div>






    
    <!-- Filter Section -->
    <div class="filter-section">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter & Search</h5>
        <form method="GET" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Leave Type</label>
                    <select class="form-select" name="leave_type" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                        <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                        <option value="personal" {{ request('leave_type') == 'personal' ? 'selected' : '' }}>Personal Leave</option>
                        <option value="emergency" {{ request('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                        <option value="maternity" {{ request('leave_type') == 'maternity' ? 'selected' : '' }}>Maternity</option>
                        <option value="other" {{ request('leave_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Staff Member</label>
                    <select class="form-select" name="person_id" id="staffFilter">
                        <option value="">All Staff</option>
                        @if(isset($staffMembers))
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ request('person_id') == $staff->id ? 'selected' : '' }}>
                                    @if($staff->staffCode)
                                        {{ $staff->staffCode->staff_code }} - {{ $staff->name }}
                                    @else
                                        {{ $staff->name }}
                                    @endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="month" id="monthFilter" 
                           value="{{ request('month', date('Y-m')) }}">
                </div>
            </div>
            <div class="text-end">
                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary btn-custom">
                    <i class="fas fa-eraser me-2"></i>Clear Filters
                </a>
                <button type="submit" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-search me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="leave-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Staff Member</th>
                        <th>Leave Type</th>
                        <th>Period</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($leaveRequests) && $leaveRequests->count() > 0)
                        @foreach($leaveRequests as $request)
                            <tr>
                                <td><strong>#{{ $request->id }}</strong></td>
                                <td>
                                    <div class="fw-bold">{{ $request->person->name }}</div>
                                    <small class="text-muted">
                                        @if($request->person->staffCode)
                                            Code: {{ $request->person->staffCode->staff_code }}
                                        @else
                                            ID: {{ $request->person->id }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-custom bg-{{ getLeaveTypeBadgeClass($request->leave_type) }}">
                                        {{ $request->formatted_leave_type ?? ucfirst($request->leave_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($request->is_datetime_based ?? false)
                                        <div class="fw-bold">{{ $request->start_datetime->format('M d, Y') }}</div>
                                        @if($request->start_datetime->format('Y-m-d') != $request->end_datetime->format('Y-m-d'))
                                            <div class="fw-bold">to {{ $request->end_datetime->format('M d, Y') }}</div>
                                        @endif
                                        <small class="text-info">
                                            <i class="fas fa-clock"></i>
                                            {{ $request->start_datetime->format('g:i A') }} - {{ $request->end_datetime->format('g:i A') }}
                                        </small>
                                    @else
                                        <div class="fw-bold">{{ $request->start_date->format('M d, Y') }}</div>
                                        @if($request->start_date->format('Y-m-d') != $request->end_date->format('Y-m-d'))
                                            <div class="fw-bold">to {{ $request->end_date->format('M d, Y') }}</div>
                                        @endif
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day"></i> Full Day(s)
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->formatted_duration ?? $request->days . ' day(s)' }}</strong>
                                    @if(($request->is_datetime_based ?? false) && ($request->hours ?? 0) < 8)
                                        <br><small class="text-info">Half Day</small>
                                    @endif
                                </td>
                                <td id="status-{{ $request->id }}">
                                    <span class="badge badge-custom bg-{{ $request->status_badge_class ?? 'secondary' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->requestedBy->name ?? 'Unknown' }}</td>
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewDetails({{ $request->id }})" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <a href="{{ route('leave-requests.print', $request) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        
                                        @if(method_exists($request, 'isPending') ? $request->isPending() : $request->status === 'pending')
                                            <a href="{{ route('leave-requests.edit', $request) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if(Auth::user()->checkAdmin() && (method_exists($request, 'isPending') ? $request->isPending() : $request->status === 'pending'))
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    onclick="showStatusModal({{ $request->id }}, 'approved')"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="showStatusModal({{ $request->id }}, 'rejected')"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if((method_exists($request, 'isPending') ? $request->isPending() : $request->status === 'pending') || Auth::user()->checkAdmin())
                                            <form action="{{ route('leave-requests.destroy', $request) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this leave request?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <div class="h5 text-muted">No leave requests found</div>
                                <p class="text-muted">Try adjusting your filters or create a new leave request.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if(isset($leaveRequests) && method_exists($leaveRequests, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $leaveRequests->firstItem() ?? 0 }} to {{ $leaveRequests->lastItem() ?? 0 }} 
                of {{ $leaveRequests->total() }} results
            </div>
            {{ $leaveRequests->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Create/Edit Leave Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Create New Leave Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('leave-requests.store') }}" method="POST" id="leaveForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Staff Member <span class="text-danger">*</span></label>
                            <select class="form-select" name="person_id" required>
                                <option value="">-- Select Staff Member --</option>
                                @if(isset($staffMembers))
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}">
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
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="leave_type" required>
                                <option value="">-- Select Leave Type --</option>
                                <option value="sick">Sick Leave</option>
                                <option value="annual">Annual Leave</option>
                                <option value="emergency">Emergency Leave</option>
                                <option value="personal">Personal Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Leave Duration Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duration_type" 
                                           id="fullDay" value="full_day" checked>
                                    <label class="form-check-label" for="fullDay">Full Day(s)</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duration_type" 
                                           id="specificTime" value="specific_time">
                                    <label class="form-check-label" for="specificTime">Specific Time Period</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Day Inputs -->
                    <div id="fullDayInputs" class="duration-inputs">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" 
                                       min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" 
                                       min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Specific Time Inputs -->
                    <div id="timeInputs" class="duration-inputs" style="display: none;">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date_time" 
                                       min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time" value="09:00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date_time" 
                                       min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time" value="17:00">
                            </div>
                        </div>
                        
                        <!-- Duration Display -->
                        <div class="alert alert-info" id="durationDisplay" style="display: none;">
                            <strong>Duration:</strong> <span id="calculatedDuration">0 hours</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Leave <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="4" 
                                  placeholder="Please provide detailed reason for the leave request..." 
                                  maxlength="1000" required></textarea>
                        <small class="form-text text-muted">Maximum 1000 characters</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This leave request will be submitted for admin approval.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="leaveForm" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal (Admin Only) -->
@if(Auth::user()->checkAdmin())
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalTitle">Update Leave Request Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="requestId">
                    <input type="hidden" id="requestStatus">
                    
                    <div class="mb-3">
                        <label for="admin_remarks" class="form-label">Admin Remarks:</label>
                        <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="3" 
                                  placeholder="Optional remarks about this decision..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Action:</strong> <span id="actionText"></span>
                    </div>

                    <div class="alert alert-warning" id="leaveDetailsAlert">
                        <strong>Staff Member:</strong> <span id="alertStaffName"></span><br>
                        <strong>Leave Period:</strong> <span id="alertLeavePeriod"></span><br>
                        <strong>Leave Type:</strong> <span id="alertLeaveType"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="confirmButton">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Leave Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDetailsContent">
                <div class="text-center py-4" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading leave request details...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="printDetailsBtn" class="btn btn-outline-secondary" target="_blank">
                    <i class="fas fa-print me-2"></i>Print
                </a>
                @if(Auth::user()->checkAdmin())
                    <div id="adminActionsInModal" style="display: none;">
                        <button type="button" class="btn btn-success btn-custom" id="approveFromModal">
                            <i class="fas fa-check me-2"></i>Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-custom" id="rejectFromModal">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div class="btn-group-vertical" role="group">
        <button type="button" class="btn btn-primary btn-lg rounded-pill mb-2" 
                onclick="showCreateModal()" title="New Leave Request">
            <i class="fas fa-plus"></i>
        </button>
        
        <button type="button" class="btn btn-info btn-lg rounded-pill mb-2" 
                onclick="exportData()" title="Export Data">
            <i class="fas fa-download"></i>
        </button>
        
        <button type="button" class="btn btn-secondary btn-lg rounded-pill" 
                onclick="scrollToTop()" title="Back to Top">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<script>
// Global variables
let currentViewRequestId = null;

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeFormToggle();
    initializeValidation();
    updateStatistics();
});

// Initialize form toggle between full day and specific time
function initializeFormToggle() {
    const fullDayRadio = document.getElementById('fullDay');
    const specificTimeRadio = document.getElementById('specificTime');
    const fullDayInputs = document.getElementById('fullDayInputs');
    const timeInputs = document.getElementById('timeInputs');
    const durationDisplay = document.getElementById('durationDisplay');

    function toggleInputSections() {
        if (fullDayRadio && fullDayRadio.checked) {
            fullDayInputs.style.display = 'block';
            timeInputs.style.display = 'none';
            durationDisplay.style.display = 'none';
            
            // Set required attributes
            fullDayInputs.querySelectorAll('input').forEach(input => {
                input.setAttribute('required', 'required');
            });
            timeInputs.querySelectorAll('input').forEach(input => {
                input.removeAttribute('required');
            });
        } else {
            fullDayInputs.style.display = 'none';
            timeInputs.style.display = 'block';
            durationDisplay.style.display = 'block';
            
            // Set required attributes
            timeInputs.querySelectorAll('input').forEach(input => {
                input.setAttribute('required', 'required');
            });
            fullDayInputs.querySelectorAll('input').forEach(input => {
                input.removeAttribute('required');
            });
            
            calculateDuration();
        }
    }

    // Event listeners for radio buttons
    if (fullDayRadio) fullDayRadio.addEventListener('change', toggleInputSections);
    if (specificTimeRadio) specificTimeRadio.addEventListener('change', toggleInputSections);

    // Initialize display
    toggleInputSections();
}

// Initialize form validation
function initializeValidation() {
    // Date validation for full day inputs
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput) {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            }
        });
    }

    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            if (startDateInput && startDateInput.value && this.value < startDateInput.value) {
                showAlert('End date cannot be before start date', 'warning');
                this.value = startDateInput.value;
            }
        });
    }

    // Time validation and calculation
    const timeInputElements = document.querySelectorAll('#timeInputs input');
    timeInputElements.forEach(input => {
        input.addEventListener('change', function() {
            calculateDuration();
            
            // Auto-set end date to start date if not set
            const startDateTimeInput = document.querySelector('input[name="start_date_time"]');
            const endDateTimeInput = document.querySelector('input[name="end_date_time"]');
            
            if (startDateTimeInput && endDateTimeInput && startDateTimeInput.value && !endDateTimeInput.value) {
                endDateTimeInput.value = startDateTimeInput.value;
            }
        });
    });
}

// Calculate duration for time-based inputs
function calculateDuration() {
    const startDate = document.querySelector('input[name="start_date_time"]')?.value;
    const startTime = document.querySelector('input[name="start_time"]')?.value;
    const endDate = document.querySelector('input[name="end_date_time"]')?.value;
    const endTime = document.querySelector('input[name="end_time"]')?.value;
    
    if (startDate && startTime && endDate && endTime) {
        const startDateTime = new Date(startDate + 'T' + startTime);
        const endDateTime = new Date(endDate + 'T' + endTime);
        
        if (endDateTime > startDateTime) {
            const diffMs = endDateTime - startDateTime;
            const diffHours = diffMs / (1000 * 60 * 60);
            
            let durationText;
            if (diffHours < 24) {
                durationText = diffHours.toFixed(1) + ' hours';
            } else {
                const days = Math.floor(diffHours / 24);
                const remainingHours = (diffHours % 24).toFixed(1);
                durationText = days + ' day(s)' + (remainingHours > 0 ? ' ' + remainingHours + ' hours' : '');
            }
            
            document.getElementById('calculatedDuration').textContent = durationText;
        } else {
            document.getElementById('calculatedDuration').textContent = 'Invalid time range';
        }
    }
}

// Show create modal
function showCreateModal() {
    // Reset form
    document.getElementById('leaveForm').reset();
    
    // Set default values
    document.getElementById('fullDay').checked = true;
    document.querySelector('input[name="start_time"]').value = '09:00';
    document.querySelector('input[name="end_time"]').value = '17:00';
    
    // Initialize form display
    initializeFormToggle();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('leaveModal'));
    modal.show();
}

// View details function
function viewDetails(id) {
    currentViewRequestId = id;
    
    // Show modal with loading
    const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('viewDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading leave request details...</div>
        </div>
    `;
    modal.show();
    
    // Fetch details
    fetch(`/leave-requests/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract content from response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Get the main content
        const content = doc.querySelector('.card-body');
        if (content) {
            document.getElementById('viewDetailsContent').innerHTML = content.innerHTML;
        } else {
            throw new Error('Could not parse response');
        }
        
        // Set print URL
        document.getElementById('printDetailsBtn').href = `/leave-requests/${id}/print`;
        
        // Show admin actions if needed
        const request = getRequestFromTable(id);
        if (request && request.status === 'pending') {
            document.getElementById('adminActionsInModal').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error loading details:', error);
        document.getElementById('viewDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <h6>Error Loading Details</h6>
                <p>Unable to load leave request details. Please try again or view the full page.</p>
                <a href="/leave-requests/${id}" class="btn btn-primary">View Full Page</a>
            </div>
        `;
    });
}

// Get request data from table
function getRequestFromTable(id) {
    const rows = document.querySelectorAll('tbody tr');
    for (let row of rows) {
        const idCell = row.querySelector('td strong');
        if (idCell && idCell.textContent === `#${id}`) {
            const cells = row.querySelectorAll('td');
            return {
                id: id,
                staffName: cells[1]?.querySelector('.fw-bold')?.textContent || '',
                leaveType: cells[2]?.querySelector('.badge')?.textContent || '',
                period: cells[3]?.querySelector('.fw-bold')?.textContent || '',
                duration: cells[4]?.querySelector('strong')?.textContent || '',
                status: cells[5]?.querySelector('.badge')?.textContent?.toLowerCase() || ''
            };
        }
    }
    return null;
}

// Enhanced status modal function
function showStatusModal(requestId, status) {
    const request = getRequestFromTable(requestId);
    
    document.getElementById('requestId').value = requestId;
    document.getElementById('requestStatus').value = status;
    
    const actionText = status === 'approved' ? 'Approve this leave request' : 'Reject this leave request';
    document.getElementById('actionText').textContent = actionText;
    
    // Update alert with request details
    if (request) {
        document.getElementById('alertStaffName').textContent = request.staffName;
        document.getElementById('alertLeavePeriod').textContent = request.period + ' (' + request.duration + ')';
        document.getElementById('alertLeaveType').textContent = request.leaveType;
    }
    
    // Update button with proper styling
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.textContent = status === 'approved' ? 'Approve' : 'Reject';
    confirmButton.className = status === 'approved' ? 'btn btn-success' : 'btn btn-danger';
    
    // Clear any previous remarks
    document.getElementById('admin_remarks').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Enhanced Status form submission with real-time UI updates
document.getElementById('statusForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const requestId = document.getElementById('requestId').value;
    const status = document.getElementById('requestStatus').value;
    const remarks = document.getElementById('admin_remarks').value;
    
    const confirmButton = document.getElementById('confirmButton');
    const originalText = confirmButton.textContent;
    
    // Show loading state
    confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    confirmButton.disabled = true;
    
    try {
        const response = await fetch(`/leave-requests/update-status/${requestId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: status,
                admin_remarks: remarks
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Update status in table immediately
            updateTableRowStatus(requestId, status, data);
            
            // Show success message
            showAlert(data.message, 'success');
            
            // Hide modal
            const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
            if (statusModal) {
                statusModal.hide();
            }
            
            // Update statistics
            updateStatistics();
            
            // Optional: Refresh page after delay for complete data sync
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'An error occurred');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showAlert('Error updating status: ' + error.message, 'danger');
    } finally {
        // Restore button
        confirmButton.textContent = originalText;
        confirmButton.disabled = false;
    }
});

// Function to update table row status immediately
function updateTableRowStatus(requestId, newStatus, responseData) {
    try {
        // Find the table row
        const statusCell = document.getElementById(`status-${requestId}`);
        
        if (statusCell) {
            // Update status badge
            const statusClass = newStatus === 'approved' ? 'success' : 'danger';
            const statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            
            statusCell.innerHTML = `
                <span class="badge badge-custom bg-${statusClass}">
                    ${statusText}
                </span>
            `;
        }
        
        // Find the entire row and update action buttons
        const row = statusCell ? statusCell.closest('tr') : null;
        if (row) {
            const actionsCell = row.querySelector('td:last-child .action-buttons');
            if (actionsCell) {
                // Remove approve/reject buttons since status is no longer pending
                const approveBtn = actionsCell.querySelector('button[onclick*="approved"]');
                const rejectBtn = actionsCell.querySelector('button[onclick*="rejected"]');
                const editBtn = actionsCell.querySelector('a[href*="edit"]');
                
                if (approveBtn) approveBtn.remove();
                if (rejectBtn) rejectBtn.remove();
                if (editBtn) editBtn.remove();
                
                // Add a visual indicator that the action was successful
                const successIndicator = document.createElement('span');
                successIndicator.className = 'badge bg-success ms-2';
                successIndicator.innerHTML = '<i class="fas fa-check"></i> Updated';
                actionsCell.appendChild(successIndicator);
                
                // Remove the indicator after 3 seconds
                setTimeout(() => {
                    if (successIndicator.parentNode) {
                        successIndicator.remove();
                    }
                }, 3000);
            }
            
            // Add success animation to the entire row
            row.style.backgroundColor = '#d4edda';
            row.style.transition = 'background-color 0.5s ease';
            
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 2000);
        }
        
        console.log(`Successfully updated request ${requestId} to ${newStatus}`);
    } catch (error) {
        console.error('Error updating table row:', error);
    }
}

// Modal action handlers
document.getElementById('approveFromModal')?.addEventListener('click', function() {
    if (currentViewRequestId) {
        bootstrap.Modal.getInstance(document.getElementById('viewDetailsModal')).hide();
        setTimeout(() => showStatusModal(currentViewRequestId, 'approved'), 300);
    }
});

document.getElementById('rejectFromModal')?.addEventListener('click', function() {
    if (currentViewRequestId) {
        bootstrap.Modal.getInstance(document.getElementById('viewDetailsModal')).hide();
        setTimeout(() => showStatusModal(currentViewRequestId, 'rejected'), 300);
    }
});

// Export functionality
function exportData() {
    const currentUrl = new URL(window.location);
    const exportUrl = new URL('/leave-requests/export', window.location.origin);
    
    // Copy current filters
    for (const [key, value] of currentUrl.searchParams) {
        exportUrl.searchParams.set(key, value);
    }
    
    exportUrl.searchParams.set('format', 'csv');
    
    // Show loading feedback
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    // Create download link
    const link = document.createElement('a');
    link.href = exportUrl.toString();
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Restore button
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        showAlert('Export started successfully', 'success');
    }, 1000);
}

// Scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Enhanced updateStatistics function with animation
function updateStatistics() {
    const rows = document.querySelectorAll('tbody tr');
    let total = 0, pending = 0, approved = 0, rejected = 0;
    
    rows.forEach(row => {
        const statusBadge = row.querySelector('[id^="status-"] .badge');
        if (statusBadge) {
            total++;
            const status = statusBadge.textContent.toLowerCase().trim();
            switch(status) {
                case 'pending': pending++; break;
                case 'approved': approved++; break;
                case 'rejected': rejected++; break;
            }
        }
    });
    
    // Update counters with animation
    animateCounter('.stat-card:not(.pending):not(.approved):not(.rejected) h3', total);
    animateCounter('.stat-card.pending h3', pending);
    animateCounter('.stat-card.approved h3', approved);
    animateCounter('.stat-card.rejected h3', rejected);
}

// Animate counter updates
function animateCounter(selector, newValue) {
    const element = document.querySelector(selector);
    if (element) {
        const currentValue = parseInt(element.textContent) || 0;
        
        if (currentValue !== newValue) {
            // Add a pulse effect
            element.style.transform = 'scale(1.1)';
            element.style.color = '#28a745';
            
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
                element.style.color = '';
            }, 200);
        }
    }
}

// Enhanced showAlert function with better positioning
function showAlert(message, type = 'success') {
    // Remove existing alerts
    document.querySelectorAll('.alert-floating').forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed alert-floating`;
    alertDiv.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        max-width: 500px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        border-radius: 10px;
    `;
    
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle', 
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${iconMap[type] || 'info-circle'} me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }
    }, 5000);
}

// Clear modal data when closed
document.getElementById('statusModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('admin_remarks').value = '';
    
    // Reset button state
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.disabled = false;
    confirmButton.innerHTML = 'Confirm';
});

document.getElementById('viewDetailsModal')?.addEventListener('hidden.bs.modal', function() {
    currentViewRequestId = null;
    document.getElementById('adminActionsInModal').style.display = 'none';
});

// Add visual feedback for button clicks
document.addEventListener('click', function(e) {
    // Add click animation to action buttons
    if (e.target.closest('.action-buttons button')) {
        const btn = e.target.closest('button');
        if (btn && !btn.disabled) {
            // Add ripple effect
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
            }, 150);
        }
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N for new leave request
    if ((e.ctrlKey || e.metaKey) && e.key === 'n' && !e.target.closest('.modal')) {
        e.preventDefault();
        showCreateModal();
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modalInstance = bootstrap.Modal.getInstance(openModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    }
});

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Add loading states to all approve/reject buttons when clicked
document.addEventListener('click', function(e) {
    if (e.target.closest('button[onclick*="showStatusModal"]')) {
        const btn = e.target.closest('button');
        if (btn && !btn.disabled) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            // Restore after modal shows (longer timeout as backup)
            setTimeout(() => {
                if (btn.disabled) {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            }, 5000);
        }
    }
});

// Enhanced error handling for network issues
window.addEventListener('online', function() {
    showAlert('Connection restored', 'success');
});

window.addEventListener('offline', function() {
    showAlert('You are currently offline. Changes may not be saved.', 'warning');
});

// Auto-refresh data every 30 seconds (optional - can be disabled)
let autoRefreshInterval;

function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        // Only refresh if no modals are open
        if (!document.querySelector('.modal.show')) {
            updateStatistics();
        }
    }, 30000); // 30 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

// Start auto-refresh when page loads
// startAutoRefresh(); // Uncomment this line to enable auto-refresh

console.log('Enhanced leave management system loaded successfully');
</script>
@endpush
@endsection