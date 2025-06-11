@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Leave Calendar</h3>
                <div>
                    <a href="{{ route('leave-requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Leave Request
                    </a>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> List View
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Legend -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex flex-wrap align-items-center">
                        <strong class="mr-3">Leave Types:</strong>
                        <span class="badge mr-2 mb-1" style="background-color: #dc3545; color: white;">Sick Leave</span>
                        <span class="badge mr-2 mb-1" style="background-color: #28a745; color: white;">Annual Leave</span>
                        <span class="badge mr-2 mb-1" style="background-color: #fd7e14; color: white;">Emergency Leave</span>
                        <span class="badge mr-2 mb-1" style="background-color: #6f42c1; color: white;">Personal Leave</span>
                        <span class="badge mr-2 mb-1" style="background-color: #e83e8c; color: white;">Maternity Leave</span>
                        <span class="badge mr-2 mb-1" style="background-color: #6c757d; color: white;">Other</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Container -->
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" role="dialog" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveDetailsModalLabel">Leave Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="viewDetailsBtn" class="btn btn-primary">View Full Details</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- Include FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .fc-event {
        cursor: pointer !important;
        border-radius: 3px !important;
        padding: 2px 4px !important;
        font-size: 11px !important;
        font-weight: bold !important;
        opacity: 1 !important;
        display: block !important;
        margin: 1px 0 !important;
    }
    
    .fc-daygrid-event {
        white-space: normal !important;
        margin: 1px 2px !important;
    }
    
    .fc-event-title {
        font-weight: bold !important;
        font-size: 11px !important;
    }
    
    .fc-daygrid-event-harness {
        margin: 1px 0 !important;
    }
    
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .leave-summary-item {
        padding: 8px;
        margin-bottom: 8px;
        border-left: 4px solid #dee2e6;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    /* Force event visibility */
    .fc-daygrid-block-event .fc-event-title {
        display: block !important;
    }
    
    .fc-daygrid-block-event {
        border: 1px solid !important;
        background: inherit !important;
    }
</style>
@endpush

@push('scripts')
<!-- Include FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listWeek'
        },
        height: 'auto',
        eventDisplay: 'block',
        dayMaxEvents: false,
        eventOverlap: true,
        events: function(info, successCallback, failureCallback) {
            console.log('Loading calendar events for date range:', info.startStr, 'to', info.endStr);
            
            fetch('/leave-requests/calendar-data?' + new URLSearchParams({
                start: info.startStr,
                end: info.endStr
            }), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Calendar data response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Calendar events received:', data);
                
                // Ensure events have proper date format
                const processedEvents = data.map(event => {
                    return {
                        ...event,
                        start: event.start,
                        end: event.end,
                        allDay: true, // Make sure it's treated as all-day event
                        display: 'block'
                    };
                });
                
                console.log('Processed events for calendar:', processedEvents);
                successCallback(processedEvents);
            })
            .catch(error => {
                console.error('Error loading calendar data:', error);
                failureCallback(error);
                // Show empty calendar instead of failing completely
                successCallback([]);
            });
        },
        eventClick: function(info) {
            console.log('Event clicked:', info.event);
            showLeaveDetails(info.event.id);
        },
        eventDidMount: function(info) {
            // Add tooltip and ensure proper styling
            info.el.setAttribute('title', info.event.title);
            info.el.style.cursor = 'pointer';
            
            // Force event to be visible
            info.el.style.opacity = '1';
            info.el.style.display = 'block';
            
            console.log('Event mounted:', info.event.title, info.el);
        },
        loading: function(bool) {
            if (bool) {
                // Show loading indicator
                document.getElementById('calendar').style.opacity = '0.5';
                console.log('Calendar loading...');
            } else {
                // Hide loading indicator
                document.getElementById('calendar').style.opacity = '1';
                console.log('Calendar loaded');
            }
        },
        eventContent: function(arg) {
            // Custom event rendering to ensure visibility
            return {
                html: '<div style="padding: 2px 4px; font-size: 11px; font-weight: bold;">' + 
                      arg.event.title + 
                      '</div>'
            };
        }
    });
    
    calendar.render();
    
    // Make calendar accessible for debugging
    window.calendar = calendar;
    
    // Function to show leave details in modal
    window.showLeaveDetails = function(leaveId) {
        fetch(`/leave-requests/${leaveId}`)
            .then(response => response.text())
            .then(html => {
                // Extract the content from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Find the leave request data (you might need to adjust this based on your show view structure)
                const leaveData = extractLeaveDataFromHtml(doc);
                
                // Populate modal with leave details
                document.getElementById('leaveDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Leave Information</h6>
                            <div class="leave-summary-item">
                                <strong>Staff Member:</strong> ${leaveData.staffName}<br>
                                <strong>Leave Type:</strong> <span class="badge badge-info">${leaveData.leaveType}</span><br>
                                <strong>Duration:</strong> ${leaveData.startDate} to ${leaveData.endDate}<br>
                                <strong>Days:</strong> ${leaveData.days} day(s)<br>
                                <strong>Status:</strong> <span class="badge badge-${leaveData.statusClass}">${leaveData.status}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Request Details</h6>
                            <div class="leave-summary-item">
                                <strong>Requested By:</strong> ${leaveData.requestedBy}<br>
                                <strong>Request Date:</strong> ${leaveData.requestDate}<br>
                                ${leaveData.approvedBy ? `<strong>Processed By:</strong> ${leaveData.approvedBy}<br>` : ''}
                                ${leaveData.approvedDate ? `<strong>Processed Date:</strong> ${leaveData.approvedDate}` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Reason</h6>
                        <div class="leave-summary-item">
                            ${leaveData.reason}
                        </div>
                    </div>
                    ${leaveData.adminRemarks ? `
                        <div class="mt-3">
                            <h6>Admin Remarks</h6>
                            <div class="leave-summary-item">
                                ${leaveData.adminRemarks}
                            </div>
                        </div>
                    ` : ''}
                `;
                
                // Update the view details button
                document.getElementById('viewDetailsBtn').href = `/leave-requests/${leaveId}`;
                
                // Show the modal
                $('#leaveDetailsModal').modal('show');
            })
            .catch(error => {
                console.error('Error loading leave details:', error);
                alert('Error loading leave details. Please try again.');
            });
    };
    
    // Helper function to extract leave data from HTML (simplified version)
    function extractLeaveDataFromHtml(doc) {
        // This is a simplified extraction - you might need to adjust based on your actual HTML structure
        return {
            staffName: 'Loading...',
            leaveType: 'Loading...',
            startDate: 'Loading...',
            endDate: 'Loading...',
            days: 'Loading...',
            status: 'Loading...',
            statusClass: 'secondary',
            requestedBy: 'Loading...',
            requestDate: 'Loading...',
            reason: 'Loading...',
            approvedBy: null,
            approvedDate: null,
            adminRemarks: null
        };
    }
});
</script>
@endpush
@endsection