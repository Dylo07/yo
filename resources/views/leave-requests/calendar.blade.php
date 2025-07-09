@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --info-color: #17a2b8;
        --light-bg: #f8f9fa;
        --dark-bg: #2c3e50;
        --border-color: #e9ecef;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .calendar-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        margin: 2rem auto;
        max-width: 1400px;
        overflow: hidden;
    }

    .calendar-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .calendar-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        animation: float 20s infinite linear;
    }

    @keyframes float {
        0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
        100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
    }

    .calendar-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 2;
    }

    .calendar-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
        position: relative;
        z-index: 2;
    }

    .calendar-controls {
        background: white;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .month-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-btn {
        background: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .nav-btn:hover {
        background: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    .current-month {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-color);
        min-width: 200px;
        text-align: center;
    }

    .view-controls {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .view-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--border-color);
        background: white;
        color: var(--primary-color);
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        text-decoration: none;
    }

    .view-btn.active,
    .view-btn:hover {
        background: var(--secondary-color);
        color: white;
        border-color: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .legend {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--light-bg);
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .calendar-grid {
        padding: 2rem;
    }

    .calendar-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
    }

    .calendar-table th {
        background: var(--light-bg);
        color: var(--primary-color);
        padding: 1rem;
        text-align: center;
        font-weight: 700;
        border-radius: 10px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .calendar-table td {
        position: relative;
        width: 14.28%;
        height: 120px;
        vertical-align: top;
        padding: 0;
    }

    .calendar-day {
        width: 100%;
        height: 100%;
        border: 2px solid var(--border-color);
        border-radius: 15px;
        padding: 0.5rem;
        background: white;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .calendar-day:hover {
        border-color: var(--secondary-color);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .calendar-day.other-month {
        background: #fafafa;
        color: #ccc;
    }

    .calendar-day.today {
        border-color: var(--warning-color);
        background: linear-gradient(135deg, #fff3cd, #ffffff);
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    .calendar-day.has-leaves {
        border-color: var(--info-color);
        background: linear-gradient(135deg, #e7f3ff, #ffffff);
    }

    .calendar-day.weekend {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
    }

    .calendar-day.weekend.today {
        background: linear-gradient(135deg, #fff3cd, #ffffff);
    }

    .day-number {
        font-weight: 700;
        font-size: 1rem;
        color: var(--primary-color);
        margin-bottom: 0.3rem;
    }

    .calendar-day.other-month .day-number {
        color: #ccc;
        opacity: 0.4;
    }

    .calendar-day.today .day-number {
        background: var(--warning-color);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .leave-indicator {
        position: absolute;
        bottom: 0.3rem;
        right: 0.3rem;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: 0.7rem;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .leave-events {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 0.3rem;
    }

    .leave-event {
        background: var(--info-color);
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 500;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .leave-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .leave-event.sick { background: #e74c3c; }
    .leave-event.annual { background: #27ae60; }
    .leave-event.emergency { background: #f39c12; }
    .leave-event.personal { background: #9b59b6; }
    .leave-event.maternity { background: #e91e63; }
    .leave-event.other { background: #6c757d; }

    .no-events {
        text-align: center;
        color: #ccc;
        font-style: italic;
        font-size: 0.8rem;
        margin-top: 1rem;
    }

    .calendar-stats {
        background: var(--light-bg);
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--border-color);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-number {
        transform: scale(1.1);
        color: var(--primary-color);
    }

    .stat-label {
        color: var(--primary-color);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }

    .floating-add-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--success-color), #2ecc71);
        color: white;
        border: none;
        border-radius: 50%;
        box-shadow: 0 8px 25px rgba(46, 204, 113, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .floating-add-btn:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 12px 35px rgba(46, 204, 113, 0.6);
    }

    /* Additional styles for enhanced functionality */
    .border-left-sick { border-left: 4px solid #e74c3c !important; }
    .border-left-annual { border-left: 4px solid #27ae60 !important; }
    .border-left-emergency { border-left: 4px solid #f39c12 !important; }
    .border-left-personal { border-left: 4px solid #9b59b6 !important; }
    .border-left-maternity { border-left: 4px solid #e91e63 !important; }
    .border-left-other { border-left: 4px solid #6c757d !important; }
    .border-left-primary { border-left: 4px solid var(--secondary-color) !important; }

    .badge-sick { background-color: #e74c3c; color: white; }
    .badge-annual { background-color: #27ae60; color: white; }
    .badge-emergency { background-color: #f39c12; color: white; }
    .badge-personal { background-color: #9b59b6; color: white; }
    .badge-maternity { background-color: #e91e63; color: white; }
    .badge-other { background-color: #6c757d; color: white; }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(52, 152, 219, 0.3);
        border-radius: 50%;
        border-top-color: var(--secondary-color);
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 1.5rem 2rem;
    }

    .btn-close-white {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--secondary-color);
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--primary-color);
    }

    @media (max-width: 768px) {
        .calendar-container {
            margin: 1rem;
            border-radius: 15px;
        }
        
        .calendar-header {
            padding: 1.5rem;
        }
        
        .calendar-title {
            font-size: 1.8rem;
        }
        
        .calendar-controls {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }
        
        .month-navigation {
            order: 2;
        }
        
        .view-controls {
            order: 1;
        }
        
        .legend {
            order: 3;
            justify-content: center;
        }
        
        .legend-item {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        
        .calendar-table td {
            height: 80px;
        }
        
        .calendar-day {
            height: 60px;
            padding: 0.25rem;
        }
        
        .day-number {
            font-size: 0.8rem;
            margin-bottom: 0.1rem;
        }
        
        .leave-event {
            font-size: 0.6rem;
            padding: 1px 3px;
        }
        
        .leave-indicator {
            width: 15px;
            height: 15px;
            font-size: 0.6rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .calendar-header .d-flex {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .calendar-table th,
        .calendar-table td {
            padding: 2px;
        }
    }

    .calendar-day:active {
        transform: scale(0.98);
    }
</style>

<div class="calendar-container">
    <!-- Header -->
    <div class="calendar-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="calendar-title">
                    <i class="fas fa-calendar-alt me-3"></i>Leave Calendar
                </h1>
                <p class="calendar-subtitle mb-0">
                    Visualize and manage your team's leave requests
                </p>
            </div>
            <div class="d-flex gap-3">
                <button class="btn btn-outline-light" onclick="exportCalendarData()" title="Export calendar data to CSV (Press E)">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button class="btn btn-outline-light" onclick="printCalendar()" title="Print calendar (Press P)">
                    <i class="fas fa-print me-2"></i>Print
                </button>
                <button class="btn btn-outline-light" onclick="showKeyboardShortcuts()" title="Show keyboard shortcuts (Press ?)">
                    <i class="fas fa-question-circle"></i>
                </button>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-list me-2"></i>List View
                </a>
                <button class="btn btn-success btn-lg" onclick="showCreateModal()">
                    <i class="fas fa-plus me-2"></i>New Request
                </button>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="calendar-controls">
        <div class="month-navigation">
            <button class="nav-btn" onclick="changeMonth(-1)" title="Previous Month">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="current-month" id="currentMonth">
                {{ now()->format('F Y') }}
            </div>
            <button class="nav-btn" onclick="changeMonth(1)" title="Next Month">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="view-controls">
            <button class="view-btn" onclick="setView('today')" title="Go to Today">
                <i class="fas fa-calendar-day me-2"></i>Today
            </button>
            <button class="view-btn active" onclick="setView('month')" title="Month View">
                <i class="fas fa-calendar me-2"></i>Month
            </button>
            <button class="view-btn" onclick="setView('week')" title="Week View">
                <i class="fas fa-calendar-week me-2"></i>Week
            </button>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #e74c3c;"></div>
                <span>Sick Leave</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #27ae60;"></div>
                <span>Annual Leave</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f39c12;"></div>
                <span>Emergency</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #9b59b6;"></div>
                <span>Personal</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #e91e63;"></div>
                <span>Maternity</span>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-grid">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Sunday</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                    <th>Saturday</th>
                </tr>
            </thead>
            <tbody id="calendarBody">
                <!-- Calendar days will be generated by JavaScript -->
            </tbody>
        </table>
        
        <div id="noEvents" class="no-events" style="display: none;">
            <i class="fas fa-calendar-check fa-3x mb-3"></i>
            <div>No leave requests found for this month</div>
            <p>Click the + button to create a new leave request</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="calendar-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalLeaves">0</div>
                <div class="stat-label">Total Leaves</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="approvedLeaves">0</div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendingLeaves">0</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="staffOnLeave">0</div>
                <div class="stat-label">Staff on Leave Today</div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Add Button -->
<button class="floating-add-btn" onclick="showCreateModal()" title="New Leave Request">
    <i class="fas fa-plus"></i>
</button>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Leave Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Create Leave Modal -->
<div class="modal fade" id="createLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Create Leave Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('leave-requests.store') }}" method="POST" id="createLeaveForm">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" 
                                  placeholder="Please provide reason for leave..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createLeaveForm" class="btn btn-success">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
// Global variables
let currentDate = new Date();
let currentView = 'month';
let leaveData = [];
let staffMembers = [];

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    loadStaffMembers();
    loadLeaveData();
    initializeFormValidation();
});

// Initialize calendar
function initializeCalendar() {
    updateMonthDisplay();
    generateCalendar();
    updateStatistics();
}

// Load staff members for create modal
async function loadStaffMembers() {
    try {
        const staffSelect = document.querySelector('select[name="person_id"]');
        if (staffSelect) {
            staffMembers = Array.from(staffSelect.options)
                .filter(option => option.value)
                .map(option => ({
                    id: option.value,
                    name: option.textContent.trim()
                }));
        }
    } catch (error) {
        console.error('Error loading staff members:', error);
    }
}

// Load leave data from server
async function loadLeaveData() {
    try {
        showLoadingSpinner(true);
        
        const startDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const endDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        
        const response = await fetch(`/leave-requests/calendar-data?start=${formatDate(startDate)}&end=${formatDate(endDate)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        leaveData = await response.json();
        console.log('Loaded leave data:', leaveData);
        
        generateCalendar();
        updateStatistics();
        
        // Show/hide no events message
        const noEventsDiv = document.getElementById('noEvents');
        if (leaveData.length === 0) {
            noEventsDiv.style.display = 'block';
            document.querySelector('.calendar-table').style.display = 'none';
        } else {
            noEventsDiv.style.display = 'none';
            document.querySelector('.calendar-table').style.display = 'table';
        }
        
    } catch (error) {
        console.error('Error loading leave data:', error);
        showAlert('Failed to load leave data. Please refresh the page.', 'error');
        
        // Show empty calendar
        leaveData = [];
        generateCalendar();
        updateStatistics();
    } finally {
        showLoadingSpinner(false);
    }
}

// Generate calendar grid
function generateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const calendarBody = document.getElementById('calendarBody');
    calendarBody.innerHTML = '';
    
    let currentWeekRow = null;
    let dayCount = 0;
    
    // Generate 6 weeks
    for (let week = 0; week < 6; week++) {
        currentWeekRow = document.createElement('tr');
        calendarBody.appendChild(currentWeekRow);
        
        for (let day = 0; day < 7; day++) {
            const cellDate = new Date(startDate);
            cellDate.setDate(startDate.getDate() + dayCount);
            
            // Use enhanced cell creation
            const cell = createEnhancedCalendarCell(cellDate, month);
            currentWeekRow.appendChild(cell);
            
            dayCount++;
        }
    }
}

// Create enhanced calendar cell
function createEnhancedCalendarCell(date, currentMonth) {
    const cell = document.createElement('td');
    const dayDiv = document.createElement('div');
    
    dayDiv.className = 'calendar-day';
    
    // Add classes based on date properties
    if (date.getMonth() !== currentMonth) {
        dayDiv.classList.add('other-month');
    }
    
    if (isToday(date)) {
        dayDiv.classList.add('today');
    }
    
    if (isWeekend(date)) {
        dayDiv.classList.add('weekend');
    }
    
    // Get leaves for this date
    const dayLeaves = getLeavesForDate(date);
    if (dayLeaves.length > 0) {
        dayDiv.classList.add('has-leaves');
    }
    
    // Day number with enhanced styling
    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = date.getDate();
    dayDiv.appendChild(dayNumber);
    
    // Add leave events with better organization
    if (dayLeaves.length > 0) {
        const eventsContainer = document.createElement('div');
        eventsContainer.className = 'leave-events';
        
        // Sort leaves by staff name for consistent display
        const sortedLeaves = dayLeaves.sort((a, b) => {
            const nameA = (a.extendedProps?.staffName || a.title).toLowerCase();
            const nameB = (b.extendedProps?.staffName || b.title).toLowerCase();
            return nameA.localeCompare(nameB);
        });
        
        // Show up to 3 events, then show count
        const visibleLeaves = sortedLeaves.slice(0, 3);
        visibleLeaves.forEach(leave => {
            const eventDiv = document.createElement('div');
            eventDiv.className = `leave-event ${leave.extendedProps?.leaveType || 'other'}`;
            
            // Extract staff name (remove staff code if present)
            let staffName = leave.extendedProps?.staffName || leave.title;
            if (staffName.includes(' - ')) {
                const parts = staffName.split(' - ');
                staffName = parts.length > 1 ? parts[1] : parts[0];
            }
            
            // Truncate long names for mobile
            if (staffName.length > 12) {
                staffName = staffName.substring(0, 10) + '...';
            }
            
            eventDiv.textContent = staffName;
            eventDiv.title = `${leave.extendedProps?.staffName || leave.title} - ${formatLeaveType(leave.extendedProps?.leaveType)} (${leave.extendedProps?.duration || 'Duration not specified'})`;
            
            eventDiv.onclick = (e) => {
                e.stopPropagation();
                showLeaveDetails(leave);
            };
            
            // Add hover animation
            eventDiv.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-1px)';
            });
            
            eventDiv.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
            
            eventsContainer.appendChild(eventDiv);
        });
        
        // Show count indicator if more than 3 events
        if (dayLeaves.length > 3) {
            const indicator = document.createElement('div');
            indicator.className = 'leave-indicator';
            indicator.style.background = 'var(--info-color)';
            indicator.textContent = `+${dayLeaves.length - 3}`;
            indicator.title = `${dayLeaves.length - 3} more leave request(s)`;
            
            indicator.onclick = (e) => {
                e.stopPropagation();
                showDayLeaves(date, dayLeaves);
            };
            
            dayDiv.appendChild(indicator);
        }
        
        dayDiv.appendChild(eventsContainer);
    }
    
    // Enhanced click handler with visual feedback
    dayDiv.onclick = (e) => {
        // Add click animation
        dayDiv.style.transform = 'scale(0.98)';
        setTimeout(() => {
            dayDiv.style.transform = 'scale(1)';
        }, 100);
        
        if (dayLeaves.length > 0) {
            showDayLeaves(date, dayLeaves);
        } else {
            showCreateModalForDate(date);
        }
    };
    
    cell.appendChild(dayDiv);
    return cell;
}

// Weekend detection
function isWeekend(date) {
    const day = date.getDay();
    return day === 0 || day === 6; // Sunday = 0, Saturday = 6
}

// Get leaves for specific date
function getLeavesForDate(date) {
    return leaveData.filter(leave => {
        const leaveStart = new Date(leave.start);
        const leaveEnd = new Date(leave.end);
        leaveEnd.setDate(leaveEnd.getDate() - 1); // Adjust for exclusive end date
        
        const dateStr = formatDate(date);
        const startStr = formatDate(leaveStart);
        const endStr = formatDate(leaveEnd);
        
        return dateStr >= startStr && dateStr <= endStr;
    });
}

// Check if date is today
function isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
}

// Format date for API
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Format leave type for display
function formatLeaveType(leaveType) {
    if (!leaveType) return 'Leave';
    
    const types = {
        'sick': 'Sick Leave',
        'annual': 'Annual Leave',
        'emergency': 'Emergency Leave',
        'personal': 'Personal Leave',
        'maternity': 'Maternity Leave',
        'other': 'Other Leave'
    };
    
    return types[leaveType] || leaveType.charAt(0).toUpperCase() + leaveType.slice(1);
}

// Update month display
function updateMonthDisplay() {
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    document.getElementById('currentMonth').textContent = 
        `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
}

// Change month
function changeMonth(direction) {
    currentDate.setMonth(currentDate.getMonth() + direction);
    updateMonthDisplay();
    loadLeaveData();
}

// Set view
function setView(view) {
    // Update active button
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    if (view === 'today') {
        currentDate = new Date();
        updateMonthDisplay();
        loadLeaveData();
        // Set month view as active since we're showing month view
        document.querySelector('.view-btn[onclick*="month"]').classList.add('active');
    } else {
        currentView = view;
        
        // Find and activate the clicked button
        const clickedBtn = Array.from(document.querySelectorAll('.view-btn'))
            .find(btn => btn.getAttribute('onclick')?.includes(view));
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }
        
        if (view === 'week') {
            showAlert('Week view coming soon! For now, showing month view.', 'info');
        }
    }
}

// Update statistics with enhanced animation
function updateStatistics() {
    const total = leaveData.length;
    const approved = leaveData.filter(leave => 
        leave.extendedProps?.status === 'approved' || !leave.extendedProps?.status
    ).length;
    const pending = leaveData.filter(leave => 
        leave.extendedProps?.status === 'pending'
    ).length;
    const rejected = leaveData.filter(leave => 
        leave.extendedProps?.status === 'rejected'
    ).length;
    
    // Count staff on leave today
    const today = new Date();
    const todayLeaves = getLeavesForDate(today);
    const staffOnLeaveToday = new Set(
        todayLeaves.map(leave => leave.extendedProps?.staffName || leave.title)
    ).size;
    
    // Animate counters with enhanced effects
    animateCounterEnhanced('totalLeaves', total);
    animateCounterEnhanced('approvedLeaves', approved);
    animateCounterEnhanced('pendingLeaves', pending);
    animateCounterEnhanced('staffOnLeave', staffOnLeaveToday);
    
    // Update tooltips with additional info
    updateStatTooltips(total, approved, pending, rejected, staffOnLeaveToday);
}

// Enhanced counter animation with color changes
function animateCounterEnhanced(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent) || 0;
    
    if (startValue === targetValue) return;
    
    const increment = targetValue > startValue ? 1 : -1;
    const duration = 1000;
    const steps = Math.abs(targetValue - startValue);
    const stepDuration = steps > 0 ? duration / steps : 0;
    
    let currentValue = startValue;
    
    // Add animation class
    element.style.transition = 'transform 0.3s ease, color 0.3s ease';
    
    const timer = setInterval(() => {
        currentValue += increment;
        element.textContent = currentValue;
        
        // Add scale effect during animation
        element.style.transform = 'scale(1.05)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 100);
        
        if (currentValue === targetValue) {
            clearInterval(timer);
            // Flash effect when complete
            element.style.color = 'var(--success-color)';
            setTimeout(() => {
                element.style.color = '';
            }, 500);
        }
    }, stepDuration);
}

// Update stat card tooltips
function updateStatTooltips(total, approved, pending, rejected, staffOnLeaveToday) {
    const statCards = document.querySelectorAll('.stat-card');
    
    if (statCards[0]) {
        statCards[0].title = `Total: ${total} leave requests\nApproved: ${approved}\nPending: ${pending}\nRejected: ${rejected}`;
    }
    
    if (statCards[1]) {
        statCards[1].title = `${approved} approved out of ${total} total requests (${total > 0 ? Math.round((approved/total)*100) : 0}%)`;
    }
    
    if (statCards[2]) {
        statCards[2].title = `${pending} pending requests awaiting approval`;
    }
    
    if (statCards[3]) {
        statCards[3].title = `${staffOnLeaveToday} staff members are on leave today`;
    }
}

// Show leave details modal
function showLeaveDetails(leave) {
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    const content = document.getElementById('leaveDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="loading-spinner"></div>
            <div class="mt-2">Loading leave details...</div>
        </div>
    `;
    
    modal.show();
    
    // Load detailed information
    if (leave.id) {
        fetch(`/leave-requests/${leave.id}`)
            .then(response => response.text())
            .then(html => {
                // Extract content from the show page
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const cardBody = doc.querySelector('.card-body');
                
                if (cardBody) {
                    content.innerHTML = cardBody.innerHTML;
                } else {
                    throw new Error('Could not parse leave details');
                }
            })
            .catch(error => {
                console.error('Error loading leave details:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Error Loading Details</h6>
                        <p>Unable to load leave details. Please try refreshing the page.</p>
                        <a href="/leave-requests/${leave.id}" class="btn btn-primary" target="_blank">
                            View Full Details
                        </a>
                    </div>
                `;
            });
    } else {
        // Show basic information from calendar data
        content.innerHTML = generateBasicLeaveDetails(leave);
    }
}

// Generate basic leave details
function generateBasicLeaveDetails(leave) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6>Leave Information</h6>
                <table class="table table-borderless">
                    <tr>
                        <th>Staff Member:</th>
                        <td>${leave.extendedProps?.staffName || leave.title}</td>
                    </tr>
                    <tr>
                        <th>Leave Type:</th>
                        <td><span class="badge badge-info">${leave.extendedProps?.leaveType || 'N/A'}</span></td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>${leave.extendedProps?.duration || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span class="badge badge-success">${leave.extendedProps?.status || 'Approved'}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Period</h6>
                <table class="table table-borderless">
                    <tr>
                        <th>Start Date:</th>
                        <td>${new Date(leave.start).toLocaleDateString()}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>${new Date(leave.end).toLocaleDateString()}</td>
                    </tr>
                </table>
                
                ${leave.extendedProps?.reason ? `
                    <h6>Reason</h6>
                    <div class="alert alert-info">
                        ${leave.extendedProps.reason}
                    </div>
                ` : ''}
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="/leave-requests/${leave.id}" class="btn btn-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>View Full Details
            </a>
        </div>
    `;
}

// Show day leaves summary
function showDayLeaves(date, dayLeaves) {
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    const content = document.getElementById('leaveDetailsContent');
    
    const dateString = date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div class="text-center mb-4">
            <h4>${dateString}</h4>
            <p class="text-muted">${dayLeaves.length} staff member(s) on leave</p>
        </div>
        
        <div class="row">
    `;
    
    dayLeaves.forEach((leave, index) => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-left-${leave.extendedProps?.leaveType || 'primary'}">
                    <div class="card-body">
                        <h6 class="card-title">${leave.extendedProps?.staffName || leave.title}</h6>
                        <p class="card-text">
                            <span class="badge badge-${leave.extendedProps?.leaveType || 'primary'} mb-2">
                                ${(leave.extendedProps?.leaveType || 'leave').replace('_', ' ').toUpperCase()}
                            </span><br>
                            <small class="text-muted">
                                ${leave.extendedProps?.duration || 'Duration not specified'}
                            </small>
                        </p>
                        <button class="btn btn-sm btn-outline-primary" onclick="showLeaveDetails(leaveData.find(l => l.id === '${leave.id}'))">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-success" onclick="showCreateModalForDate(new Date('${date.toISOString()}'))">
                <i class="fas fa-plus me-2"></i>Add New Leave Request
            </button>
        </div>
    `;
    
    content.innerHTML = html;
    modal.show();
}

// Show create modal for specific date
function showCreateModalForDate(date) {
    const createModal = new bootstrap.Modal(document.getElementById('createLeaveModal'));
    
    // Pre-fill the date
    const startDateInput = document.querySelector('#createLeaveModal input[name="start_date"]');
    const endDateInput = document.querySelector('#createLeaveModal input[name="end_date"]');
    
    if (startDateInput && endDateInput) {
        const dateString = formatDate(date);
        startDateInput.value = dateString;
        endDateInput.value = dateString;
        endDateInput.min = dateString;
    }
    
    createModal.show();
}

// Show create modal (general)
function showCreateModal() {
    const createModal = new bootstrap.Modal(document.getElementById('createLeaveModal'));
    
    // Reset form
    document.getElementById('createLeaveForm').reset();
    
    // Set minimum date to today
    const today = formatDate(new Date());
    const startDateInput = document.querySelector('#createLeaveModal input[name="start_date"]');
    const endDateInput = document.querySelector('#createLeaveModal input[name="end_date"]');
    
    if (startDateInput && endDateInput) {
        startDateInput.min = today;
        endDateInput.min = today;
    }
    
    createModal.show();
}

// Show loading spinner
function showLoadingSpinner(show) {
    const calendarGrid = document.querySelector('.calendar-grid');
    
    if (show) {
        if (!document.getElementById('loadingOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255,255,255,0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            `;
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="loading-spinner" style="width: 40px; height: 40px; border-width: 4px;"></div>
                    <div class="mt-2">Loading calendar...</div>
                </div>
            `;
            calendarGrid.style.position = 'relative';
            calendarGrid.appendChild(overlay);
        }
    } else {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
}

// Show alert function
function showAlert(message, type = 'info') {
    // Remove existing alerts
    document.querySelectorAll('.alert-floating').forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed alert-floating`;
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
        'info': 'info-circle',
        'success': 'check-circle',
        'error': 'exclamation-triangle',
        'warning': 'exclamation-circle'
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

// Initialize form validation
function initializeFormValidation() {
    const startDateInput = document.querySelector('#createLeaveModal input[name="start_date"]');
    const endDateInput = document.querySelector('#createLeaveModal input[name="end_date"]');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
        
        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && this.value < startDateInput.value) {
                showAlert('End date cannot be before start date', 'warning');
                this.value = startDateInput.value;
            }
        });
    }
}

// Handle form submission
document.getElementById('createLeaveForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Show loading
    submitButton.innerHTML = '<div class="loading-spinner me-2"></div>Submitting...';
    submitButton.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            showAlert('Leave request submitted successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createLeaveModal')).hide();
            loadLeaveData(); // Refresh calendar
        } else {
            throw new Error(data?.message || 'Form submission failed');
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        showAlert('Failed to submit leave request. Please try again.', 'error');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
});

// Export calendar data functionality
function exportCalendarData() {
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();
    
    // Create CSV content
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Staff Name,Leave Type,Start Date,End Date,Duration,Status,Reason\n";
    
    leaveData.forEach(leave => {
        const staffName = (leave.extendedProps?.staffName || leave.title).replace(/,/g, '');
        const leaveType = formatLeaveType(leave.extendedProps?.leaveType);
        const startDate = new Date(leave.start).toLocaleDateString();
        const endDate = new Date(leave.end).toLocaleDateString();
        const duration = leave.extendedProps?.duration || '';
        const status = leave.extendedProps?.status || 'Approved';
        const reason = (leave.extendedProps?.reason || '').replace(/,/g, ';');
        
        csvContent += `"${staffName}","${leaveType}","${startDate}","${endDate}","${duration}","${status}","${reason}"\n`;
    });
    
    // Download file
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `leave_calendar_${currentYear}_${currentMonth.toString().padStart(2, '0')}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showAlert('Calendar data exported successfully!', 'success');
}

// Print calendar functionality
function printCalendar() {
    const printWindow = window.open('', '_blank');
    const calendarHTML = document.querySelector('.calendar-container').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Leave Calendar - ${document.getElementById('currentMonth').textContent}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .calendar-header { background: #2c3e50; color: white; padding: 20px; margin-bottom: 20px; }
                .calendar-title { font-size: 24px; margin: 0; }
                .calendar-table { width: 100%; border-collapse: collapse; }
                .calendar-table th, .calendar-table td { border: 1px solid #ddd; padding: 8px; }
                .calendar-table th { background: #f5f5f5; font-weight: bold; }
                .calendar-day { height: 80px; vertical-align: top; }
                .day-number { font-weight: bold; margin-bottom: 5px; }
                .leave-event { background: #007bff; color: white; padding: 2px 4px; margin: 1px 0; font-size: 10px; }
                .leave-event.sick { background: #dc3545; }
                .leave-event.annual { background: #28a745; }
                .leave-event.emergency { background: #ffc107; color: #000; }
                .leave-event.personal { background: #6f42c1; }
                .leave-event.maternity { background: #e83e8c; }
                .no-print { display: none; }
                @media print { .no-print { display: none !important; } }
            </style>
        </head>
        <body>
            ${calendarHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Show keyboard shortcuts help
function showKeyboardShortcuts() {
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    const content = document.getElementById('leaveDetailsContent');
    
    content.innerHTML = `
        <div class="text-center mb-4">
            <h4><i class="fas fa-keyboard me-2"></i>Keyboard Shortcuts</h4>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h6>Navigation</h6>
                <table class="table table-sm">
                    <tr><td><kbd>←</kbd></td><td>Previous month</td></tr>
                    <tr><td><kbd>→</kbd></td><td>Next month</td></tr>
                    <tr><td><kbd>Home</kbd></td><td>Go to today</td></tr>
                    <tr><td><kbd>T</kbd></td><td>Go to today</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Actions</h6>
                <table class="table table-sm">
                    <tr><td><kbd>N</kbd></td><td>New leave request</td></tr>
                    <tr><td><kbd>E</kbd></td><td>Export calendar</td></tr>
                    <tr><td><kbd>P</kbd></td><td>Print calendar</td></tr>
                    <tr><td><kbd>?</kbd></td><td>Show this help</td></tr>
                </table>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Tip:</strong> Click on any day to view leaves or create a new request for that date.
        </div>
    `;
    
    modal.show();
}

// Enhanced keyboard navigation
document.addEventListener('keydown', function(e) {
    // Don't trigger shortcuts when typing in form fields
    if (e.target.matches('input, textarea, select')) return;
    
    switch(e.key) {
        case 'ArrowLeft':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                changeMonth(-1);
            }
            break;
        case 'ArrowRight':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                changeMonth(1);
            }
            break;
        case 'Home':
            e.preventDefault();
            setView('today');
            break;
        case 'n':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                showCreateModal();
            }
            break;
        case 't':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                setView('today');
            }
            break;
        case 'e':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                exportCalendarData();
            }
            break;
        case 'p':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                printCalendar();
            }
            break;
        case '?':
            e.preventDefault();
            showKeyboardShortcuts();
            break;
    }
});

// Auto-refresh functionality (optional)
let autoRefreshInterval;

function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        // Only refresh if no modals are open
        if (!document.querySelector('.modal.show')) {
            loadLeaveData();
        }
    }, 300000); // Refresh every 5 minutes
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

// Initialize auto-refresh (uncomment to enable)
// startAutoRefresh();

// Handle window resize for responsive calendar
window.addEventListener('resize', function() {
    // Regenerate calendar on significant size changes
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(() => {
        generateCalendar();
    }, 250);
});

// Handle online/offline status
window.addEventListener('online', function() {
    showAlert('Connection restored', 'success');
    loadLeaveData(); // Refresh data when back online
});

window.addEventListener('offline', function() {
    showAlert('You are currently offline. Changes may not be saved.', 'warning');
});

// Clean up event listeners on page unload
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
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

// Error boundary for JavaScript errors
window.addEventListener('error', function(e) {
    console.error('Calendar error:', e.error);
    showAlert('An error occurred. Please refresh the page if problems persist.', 'error');
});

// Performance monitoring
let loadStartTime = performance.now();

document.addEventListener('DOMContentLoaded', function() {
    const loadTime = performance.now() - loadStartTime;
    console.log(`Calendar loaded in ${loadTime.toFixed(2)}ms`);
});

console.log('Enhanced calendar system loaded successfully - Version 2.0');
</script>

@endsection