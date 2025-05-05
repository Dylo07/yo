@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Staff Manual Attendance</h3>
                <div>
                @if(Auth::user()->checkAdmin())
    <a href="{{ route('attendance.manual.add-staff-form') }}" class="btn btn-primary mr-2">
        <i class="fas fa-user-plus"></i> Add Staff Member
    </a>
@endif
                    <a href="{{ route('attendance.manual.report') }}" class="btn btn-info">View Report</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="alert-container"></div>

             <!-- Add the session alerts here -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    

            @if(Auth::user()->checkAdmin())
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> As an admin, you can mark attendance for previous dates using the calendar icon.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Person ID</th>
                            <th>Staff Name</th>
                            <th>Attendance Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $member)
                            <tr id="staff-row-{{ $member->id }}">
                                <td>{{ $member->id }}</td>
                                <td>
                                    {{ $member->name }}
                                    @if(Auth::user()->checkAdmin())
                                        <a href="#" onclick="openAttendanceHistory({{ $member->id }}, '{{ $member->name }}')" class="text-secondary ml-2" title="View Attendance History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    @endif
                                </td>
                                <td id="status-cell-{{ $member->id }}">
                                    @if(isset($attendances[$member->id]))
                                        <span class="badge badge-{{ $attendances[$member->id]->status == 'present' ? 'success' : ($attendances[$member->id]->status == 'half' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($attendances[$member->id]->status) }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Not Marked</span>
                                    @endif
                                </td>
                                <td id="remarks-cell-{{ $member->id }}">
                                    {{ isset($attendances[$member->id]) ? $attendances[$member->id]->remarks : '-' }}
                                </td>
                                <td>
                                    <div class="input-group">
                                        @if(Auth::user()->checkAdmin())
                                            <div class="input-group-prepend">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="openAttendanceCalendar({{ $member->id }}, '{{ $member->name }}')" 
                                                        title="Mark attendance for previous dates">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" 
                                                id="date-{{ $member->id }}" 
                                                value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                                        @else
                                            <input type="hidden" 
                                                id="date-{{ $member->id }}" 
                                                value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                                        @endif
                                        <input type="text" class="form-control form-control-sm remarks-input" 
                                            id="remarks-{{ $member->id }}" placeholder="Remarks">
                                        <div class="input-group-append">
                                            <button onclick="markAttendance({{ $member->id }}, 'present')" 
                                                class="btn btn-sm btn-success">Present</button>
                                            <button onclick="markAttendance({{ $member->id }}, 'half')" 
                                                class="btn btn-sm btn-warning">Half Day</button>
                                            <button onclick="markAttendance({{ $member->id }}, 'absent')" 
                                                class="btn btn-sm btn-danger">Absent</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Replace the existing Add Staff Member Modal with this simplified version -->
<div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">Add Staff Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('attendance.manual.add-staff') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="person_id">Person ID:</label>
                        <select name="person_id" id="person_id" class="form-control" required>
                            <option value="">-- Select Person --</option>
                            @php
                                $availablePersons = App\Models\Person::whereDoesntHave('staffCode')
                                    ->orWhereHas('staffCode', function($query) {
                                        $query->where('is_active', 0);
                                    })
                                    ->where('type', 'individual')
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach($availablePersons as $person)
                                <option value="{{ $person->id }}">{{ $person->id }} - {{ $person->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="staff_code">Staff Code:</label>
                        <input type="text" class="form-control" id="staff_code" name="staff_code" 
                               placeholder="e.g. EMP001" required>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Calendar Modal -->
<div class="modal fade" id="attendanceCalendarModal" tabindex="-1" role="dialog" aria-labelledby="attendanceCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceCalendarModalLabel">Attendance for <span id="modal-staff-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-person-id">
                
                <div class="mb-3">
                    <label for="attendance-month">Select Month:</label>
                    <input type="month" id="attendance-month" class="form-control" value="{{ Carbon\Carbon::now()->format('Y-m') }}">
                </div>
                
                <div class="attendance-calendar">
                    <div class="d-flex justify-content-around attendance-legend mb-3">
                        <div><span class="badge badge-success">Present</span></div>
                        <div><span class="badge badge-warning">Half Day</span></div>
                        <div><span class="badge badge-danger">Absent</span></div>
                        <div><span class="badge badge-secondary">Not Marked</span></div>
                    </div>
                    
                    <div id="attendance-days-container" class="mb-3">
                        <!-- Days will be loaded here -->
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Click on a date to toggle attendance status: Present → Half Day → Absent → Not Marked
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance History Modal -->
<div class="modal fade" id="attendanceHistoryModal" tabindex="-1" role="dialog" aria-labelledby="attendanceHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceHistoryModalLabel">Attendance History for <span id="history-staff-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="history-person-id">
                
                <div class="mb-3">
                    <label for="history-month">Select Month:</label>
                    <input type="month" id="history-month" class="form-control" value="{{ Carbon\Carbon::now()->format('Y-m') }}">
                </div>
                
                <div id="attendance-history-container">
                    <!-- History will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .btn-sm { padding: 0.25rem 0.5rem; }
    .badge { font-size: 90%; }
    .form-control-sm { height: calc(1.5em + 0.5rem + 2px); }
    .alert { margin-bottom: 1rem; }
    
    /* Improved Attendance Calendar Styles */
    .attendance-calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .list-group-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover:not(.disabled) {
        background-color: #f8f9fa;
    }
    
    .list-group-item.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .badge-pill {
        padding: 0.5rem 0.8rem;
        font-size: 90%;
    }
    
    .attendance-legend {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    
    /* Person search results */
    .person-result {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .person-result:hover {
        background-color: #f8f9fa;
    }
    
    .person-result.selected {
        background-color: #e3f2fd;
        border-color: #90caf9;
    }
    
    /* Fix for mobile view */
    @media (max-width: 576px) {
        .badge-pill {
            padding: 0.4rem 0.6rem;
            font-size: 80%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    `;
    alertContainer.innerHTML = alert;
    setTimeout(() => {
        const alertElement = alertContainer.firstChild;
        if (alertElement) alertElement.remove();
    }, 3000);
}

async function markAttendance(personId, status) {
    const dateInput = document.getElementById(`date-${personId}`);
    const remarksInput = document.getElementById(`remarks-${personId}`);
    const statusCell = document.getElementById(`status-cell-${personId}`);
    const remarksCell = document.getElementById(`remarks-cell-${personId}`);

    try {
        const response = await fetch('{{ route("attendance.manual.mark") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                person_id: personId,
                status: status,
                remarks: remarksInput.value,
                attendance_date: dateInput.value
            })
        });

        const data = await response.json();

        if (response.status === 403) {
            showAlert(data.message, 'danger');
            return;
        }

        if (data.success) {
            statusCell.innerHTML = data.status_badge;
            remarksCell.textContent = remarksInput.value || '-';
            showAlert(data.message, 'success');
            remarksInput.value = '';
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while marking attendance', 'danger');
    }
}

// Open the attendance calendar modal
function openAttendanceCalendar(personId, personName) {
    document.getElementById('modal-person-id').value = personId;
    document.getElementById('modal-staff-name').textContent = personName;
    
    // Load the calendar
    loadAttendanceCalendar();
    
    // Show the modal
    $('#attendanceCalendarModal').modal('show');
}

// Search for persons in the database
async function searchPersons() {
    const searchQuery = document.getElementById('person-search').value;
    const searchResults = document.getElementById('search-results');
    
    if (!searchQuery.trim()) {
        searchResults.innerHTML = `<div class="alert alert-info">Please enter a search term</div>`;
        return;
    }
    
    searchResults.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    
    try {
        const response = await fetch('{{ route("attendance.manual.search-persons") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                query: searchQuery
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.persons.length === 0) {
                searchResults.innerHTML = `<div class="alert alert-warning">No persons found matching "${searchQuery}"</div>`;
                return;
            }
            
            let resultsHTML = `<div class="list-group">`;
            
            data.persons.forEach(person => {
                resultsHTML += `
                    <div class="person-result" onclick="selectPerson(${person.id}, '${person.name}')">
                        <div class="d-flex justify-content-between">
                            <strong>${person.name}</strong>
                            <span class="badge badge-secondary">ID: ${person.id}</span>
                        </div>
                        <div class="small text-muted">${person.type || ''} ${person.phone || ''}</div>
                    </div>
                `;
            });
            
            resultsHTML += `</div>`;
            searchResults.innerHTML = resultsHTML;
        } else {
            searchResults.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        searchResults.innerHTML = `<div class="alert alert-danger">An error occurred while searching</div>`;
    }
}

// Select a person from search results
function selectPerson(personId, personName) {
    // Clear any previous selection
    const personResults = document.querySelectorAll('.person-result');
    personResults.forEach(result => {
        result.classList.remove('selected');
    });
    
    // Highlight the selected person
    const selectedPerson = document.querySelector(`.person-result[onclick*="${personId}"]`);
    if (selectedPerson) {
        selectedPerson.classList.add('selected');
    }
    
    // Fill in the person ID
    document.getElementById('person-id').value = personId;
    
    // Generate a default staff code if empty
    const staffCodeInput = document.getElementById('staff-code');
    if (!staffCodeInput.value) {
        staffCodeInput.value = `EMP${String(personId).padStart(3, '0')}`;
    }
}

// Add a new staff member
async function addStaffMember() {
    const personId = document.getElementById('person-id').value;
    const staffCode = document.getElementById('staff-code').value;
    const isActive = document.getElementById('is-active').checked ? 1 : 0;
    
    if (!personId || !staffCode) {
        showAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    try {
        const response = await fetch('{{ route("attendance.manual.add-staff") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                person_id: personId,
                staff_code: staffCode,
                is_active: isActive
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Close the modal
            $('#addStaffModal').modal('hide');
            
            // Reload the page to show the new staff member
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while adding staff member', 'danger');
    }
}

// Load attendance calendar for the selected month
async function loadAttendanceCalendar() {
    const personId = document.getElementById('modal-person-id').value;
    const month = document.getElementById('attendance-month').value;
    const daysContainer = document.getElementById('attendance-days-container');
    
    daysContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    
    try {
        const response = await fetch(`{{ url('manual-attendance/staff') }}/${personId}/history?month=${month}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.status === 403) {
            daysContainer.innerHTML = `
                <div class="alert alert-danger">
                    ${data.message}
                </div>
            `;
            return;
        }
        
        if (data.success) {
            renderAttendanceDays(data.dates);
        } else {
            daysContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load attendance data
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        daysContainer.innerHTML = `
            <div class="alert alert-danger">
                An error occurred while loading attendance data: ${error.message}
            </div>
        `;
    }
}

// Render the attendance days in a list format
function renderAttendanceDays(datesData) {
    const daysContainer = document.getElementById('attendance-days-container');
    const monthData = document.getElementById('attendance-month').value.split('-');
    const year = parseInt(monthData[0]);
    const month = parseInt(monthData[1]) - 1; // JS months are 0-indexed
    
    const today = new Date();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Get the month name
    const monthName = firstDay.toLocaleString('default', { month: 'long' });
    
    let html = `
        <div class="text-center mb-3">
            <h4>${monthName} ${year}</h4>
        </div>
        <div class="list-group">
    `;
    
    // Create a day for each date in the month
    for (let day = 1; day <= lastDay.getDate(); day++) {
        const date = new Date(year, month, day);
        const dateKey = date.toISOString().split('T')[0];
        const dateData = datesData[dateKey] || { status: 'not_marked', remarks: '' };
        
        const isFuture = date > today;
        const dayName = date.toLocaleString('default', { weekday: 'short' });
        
        // Determine the status class and text
        let statusClass = '';
        let statusText = '';
        
        switch (dateData.status) {
            case 'present':
                statusClass = 'success';
                statusText = 'Present';
                break;
            case 'half':
                statusClass = 'warning';
                statusText = 'Half Day';
                break;
            case 'absent':
                statusClass = 'danger';
                statusText = 'Absent';
                break;
            default:
                statusClass = 'secondary';
                statusText = 'Not Marked';
        }
        
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center ${isFuture ? 'disabled' : ''}" 
                 data-date="${dateKey}" 
                 ${!isFuture ? `onclick="toggleAttendance('${dateKey}')"` : ''}>
                <div>
                    <strong>${day}</strong> ${dayName}
                    ${dateData.remarks ? `<small class="d-block text-muted">${dateData.remarks}</small>` : ''}
                </div>
                <span class="badge badge-${statusClass} badge-pill">${statusText}</span>
            </div>
        `;
    }
    
    html += `</div>`;
    
    daysContainer.innerHTML = html;
}

// Toggle attendance status when clicking on a calendar day
async function toggleAttendance(date) {
    const personId = document.getElementById('modal-person-id').value;
    
    try {
        const response = await fetch('{{ route("attendance.manual.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                person_id: personId,
                attendance_date: date
            })
        });
        
        const data = await response.json();
        
        if (response.status === 403) {
            showAlert(data.message, 'danger');
            return;
        }
        
        if (data.success) {
            // Reload the calendar to show the updated status
            loadAttendanceCalendar();
            
            // Show a small notification
            showAlert(`Attendance ${data.status === 'not_marked' ? 'cleared' : 'marked as ' + data.status}`, 'success');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while updating attendance', 'danger');
    }
}

// Open attendance history modal
function openAttendanceHistory(personId, personName) {
    document.getElementById('history-person-id').value = personId;
    document.getElementById('history-staff-name').textContent = personName;
    
    // Load the attendance history
    loadAttendanceHistory();
    
    // Show the modal
    $('#attendanceHistoryModal').modal('show');
}

// Load attendance history for the selected month
async function loadAttendanceHistory() {
    const personId = document.getElementById('history-person-id').value;
    const month = document.getElementById('history-month').value;
    const historyContainer = document.getElementById('attendance-history-container');
    
    historyContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    
    try {
        const response = await fetch(`{{ url('manual-attendance/staff') }}/${personId}/history?month=${month}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.status === 403) {
            historyContainer.innerHTML = `
                <div class="alert alert-danger">
                    ${data.message}
                </div>
            `;
            return;
        }
        
        if (data.success) {
            renderAttendanceHistory(data.dates);
        } else {
            historyContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load attendance data
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        historyContainer.innerHTML = `
            <div class="alert alert-danger">
                An error occurred while loading attendance data: ${error.message}
            </div>
        `;
    }
}

// Render attendance history table
function renderAttendanceHistory(datesData) {
    const historyContainer = document.getElementById('attendance-history-container');
    const month = document.getElementById('history-month').value;
    
    let tableHTML = `
        <div class="table-responsive">
            <table class="table table-bordered table-striped attendance-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Sort dates by date (newest first)
    const sortedDates = Object.values(datesData).sort((a, b) => {
        return new Date(b.date) - new Date(a.date);
    });
    
    // Create rows for each date
    sortedDates.forEach(dateData => {
        // Determine the status class and text
        let statusClass = '';
        let statusText = '';
        
        switch (dateData.status) {
            case 'present':
                statusClass = 'success';
                statusText = 'Present';
                break;
            case 'half':
                statusClass = 'warning';
                statusText = 'Half Day';
                break;
            case 'absent':
                statusClass = 'danger';
                statusText = 'Absent';
                break;
            default:
                statusClass = 'secondary';
                statusText = 'Not Marked';
        }
        
        tableHTML += `
            <tr>
                <td>${dateData.formatted_date}</td>
                <td>${dateData.day_name}</td>
                <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                <td>${dateData.remarks || '-'}</td>
            </tr>
        `;
    });
    
    tableHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    historyContainer.innerHTML = tableHTML;
}

// Add event listeners for month selection
document.addEventListener('DOMContentLoaded', function() {
    // Calendar month change
    const attendanceMonth = document.getElementById('attendance-month');
    if (attendanceMonth) {
        attendanceMonth.addEventListener('change', loadAttendanceCalendar);
    }
    
    // History month change
    const historyMonth = document.getElementById('history-month');
    if (historyMonth) {
        historyMonth.addEventListener('change', loadAttendanceHistory);
    }
    
    // Person search on Enter key
    const personSearch = document.getElementById('person-search');
    if (personSearch) {
        personSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPersons();
            }
        });
    }
    
    // Reset the add staff form when the modal is closed
    $('#addStaffModal').on('hidden.bs.modal', function() {
        document.getElementById('person-search').value = '';
        document.getElementById('person-id').value = '';
        document.getElementById('staff-code').value = '';
        document.getElementById('is-active').checked = true;
        document.getElementById('search-results').innerHTML = '';
    });
});
</script>
@endpush
@endsection