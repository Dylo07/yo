<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="{{ Auth::user()->role }}">
    <title>Room Availability Visualizer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
        }
        
        .date-selector {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .stats-container {
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .availability-calendar {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .calendar-day {
            min-width: 150px;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #dee2e6;
        }
        
        .calendar-day:last-child {
            border-right: none;
        }
        
        .date-header {
            font-weight: bold;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .day-of-week {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .availability-grid {
            margin-top: 30px;
        }
        
        .room-row {
            display: flex;
            border-bottom: 1px solid #dee2e6;
        }
        
        .room-row:last-child {
            border-bottom: none;
        }
        
        .room-name {
            min-width: 120px;
            padding: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .room-status {
            min-width: 150px;
            padding: 10px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #dee2e6;
        }
        
        .room-status:last-child {
            border-right: none;
        }
        
        .available {
            color: #989e9b;
        }
        
        .booked {
            color: #dc3545;
        }
        
        .room-type-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        .legend-available {
            background-color: rgba(25, 135, 84, 0.2);
            border: 1px solid #989e9b;
        }
        
        .legend-booked {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
        }
        
        .heat-map-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .heat-map {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .heat-map-day {
            width: 180px;
            min-height: 160px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px;
            cursor: pointer;
            transition: transform 0.2s;
            background-color: white;
            margin-bottom: 10px;
        }
        
        .heat-map-day:hover {
            transform: scale(1.03);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .heat-map-date {
            text-align: center;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 5px;
        }
        
        .heat-map-day-of-week {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .time-slot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .time-slot:last-child {
            border-bottom: none;
        }
        
        .time-slot-label {
            font-size: 0.8rem;
            flex: 3;
        }
        
        .time-slot-availability {
            flex: 1;
            text-align: right;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 4px;
        }
        
        /* Heat map colors based on availability percentage */
        .available-100 {
            background-color: rgba(25, 135, 84, 0.9);
            color: white;
        }
        .available-90 {
            background-color: rgba(25, 135, 84, 0.8);
            color: white;
        }
        .available-80 {
            background-color: rgba(25, 135, 84, 0.7);
            color: white;
        }
        .available-70 {
            background-color: rgba(25, 135, 84, 0.6);
            color: white;
        }
        .available-60 {
            background-color: rgba(255, 193, 7, 0.6);
        }
        .available-50 {
            background-color: rgba(255, 193, 7, 0.7);
        }
        .available-40 {
            background-color: rgba(255, 193, 7, 0.8);
        }
        .available-30 {
            background-color: rgba(220, 53, 69, 0.6);
            color: white;
        }
        .available-20 {
            background-color: rgba(220, 53, 69, 0.7);
            color: white;
        }
        .available-10 {
            background-color: rgba(220, 53, 69, 0.8);
            color: white;
        }
        .available-0 {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }
        
        .time-slot-header {
            background-color: #f8f9fa;
            padding: 8px 12px;
            margin-top: 20px;
            border-left: 4px solid #0d6efd;
            font-weight: 500;
            border-radius: 0 4px 4px 0;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-right: 5px;
        }
        
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: #fff;
            border-bottom-color: transparent;
            font-weight: bold;
        }
        
        .tab-content {
            padding-top: 20px;
        }
        
        .booking-group-legend {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .badge-legend {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .badge-legend .badge {
            margin-right: 10px;
        }
        
        /* Animation for the progress bar */
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        .progress-bar-animated {
            animation: progress 2s linear infinite;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Room Availability Visualizer</h1>
                <a href="/calendar" class="btn btn-outline-light">Back to Booking Calendar</a>
            </div>
            <p class="mb-0">View room availability with time slots across multiple days</p>
        </div>
    </div>

    <div class="container">
        <!-- Date selection form -->
        <div class="date-selector">
            <form id="dateRangeForm" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Search Availability</button>
                </div>
            </form>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" class="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        <!-- Statistics cards -->
        <div class="stats-container row g-4" id="statsContainer" style="display: none;">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon text-primary">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <h3 id="totalDays">0</h3>
                    <p class="text-muted">Days Selected</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon text-success">
                        <i class="fas fa-sun"></i>
                    </div>
                    <h3 id="morningAvailability">0%</h3>
                    <p class="text-muted">Morning Availability</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon text-warning">
                        <i class="fas fa-cloud-sun"></i>
                    </div>
                    <h3 id="afternoonAvailability">0%</h3>
                    <p class="text-muted">Afternoon Availability</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="icon text-info">
                        <i class="fas fa-moon"></i>
                    </div>
                    <h3 id="eveningAvailability">0%</h3>
                    <p class="text-muted">Evening Availability</p>
                </div>
            </div>
        </div>
        
        <!-- Heat Map View -->
        <div class="heat-map-container" id="heatMapContainer" style="display: none;">
            <h3>Availability Heat Map</h3>
            <p class="text-muted">Click on a day to see detailed room availability by time slot</p>
            <div class="heat-map" id="heatMap">
                <!-- Heat map will be populated by JavaScript -->
            </div>
        </div>
        
        <!-- Detailed View for a specific day -->
        <div class="availability-calendar" id="detailedViewContainer" style="display: none;">
            <h3 id="detailedViewTitle">Room Availability for Selected Date</h3>
            
            <!-- Time slot tabs -->
            <ul class="nav nav-tabs" id="timeSlotTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="morning-tab" data-bs-toggle="tab" data-bs-target="#morning-content" type="button" role="tab" aria-controls="morning-content" aria-selected="true">
                        <i class="fas fa-sun me-1"></i> Morning
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="afternoon-tab" data-bs-toggle="tab" data-bs-target="#afternoon-content" type="button" role="tab" aria-controls="afternoon-content" aria-selected="false">
                        <i class="fas fa-cloud-sun me-1"></i> Afternoon
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="evening-tab" data-bs-toggle="tab" data-bs-target="#evening-content" type="button" role="tab" aria-controls="evening-content" aria-selected="false">
                        <i class="fas fa-moon me-1"></i> Evening
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="timeSlotTabsContent">
                <div class="tab-pane fade show active" id="morning-content" role="tabpanel" aria-labelledby="morning-tab">
                    <div id="morningAvailabilityContainer" class="time-slot-content">
                        <!-- Morning availability will be populated by JavaScript -->
                    </div>
                </div>
                <div class="tab-pane fade" id="afternoon-content" role="tabpanel" aria-labelledby="afternoon-tab">
                    <div id="afternoonAvailabilityContainer" class="time-slot-content">
                        <!-- Afternoon availability will be populated by JavaScript -->
                    </div>
                </div>
                <div class="tab-pane fade" id="evening-content" role="tabpanel" aria-labelledby="evening-tab">
                    <div id="eveningAvailabilityContainer" class="time-slot-content">
                        <!-- Evening availability will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="legend mt-4">
                <div class="legend-item">
                    <div class="legend-color legend-available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-booked"></div>
                    <span>Booked</span>
                </div>
            </div>
        </div>
        
        <!-- Calendar View -->
        <div class="availability-calendar" id="calendarContainer" style="display: none;">
            <h3>Calendar View with Time Slots</h3>
            <p class="text-muted">This view shows room availability across days with time slots</p>
            
 
            <!-- Booking Group Legend -->
            <div id="bookingGroupLegend" class="booking-group-legend" style="display: none;">
                <h5>Booking Groups Legend</h5>
                <div class="d-flex flex-wrap gap-3">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Time Slot Badge Legend -->
            <div class="badge-legend mt-3 mb-4">
                <span class="badge bg-light text-dark"><strong>M</strong></span>
                <span>Morning (12:00 AM - 12:00 PM)</span>
                <span class="badge bg-light text-dark ms-3"><strong>A</strong></span>
                <span>Afternoon (12:00 PM - 6:00 PM)</span>
                <span class="badge bg-light text-dark ms-3"><strong>E</strong></span>
                <span>Evening (6:00 PM - 12:00 AM)</span>
            </div>
            
            <!-- Button group for time slot selection -->
            <div class="btn-group mb-3" role="group" aria-label="Time slot selector">
                <input type="radio" class="btn-check" name="timeSlotRadio" id="allTimeSlots" value="all" checked>
                <label class="btn btn-outline-primary" for="allTimeSlots">All Times</label>
                
                <input type="radio" class="btn-check" name="timeSlotRadio" id="morningTimeSlot" value="morning">
                <label class="btn btn-outline-primary" for="morningTimeSlot">Morning</label>
                
                <input type="radio" class="btn-check" name="timeSlotRadio" id="afternoonTimeSlot" value="afternoon">
                <label class="btn btn-outline-primary" for="afternoonTimeSlot">Afternoon</label>
                
                <input type="radio" class="btn-check" name="timeSlotRadio" id="eveningTimeSlot" value="evening">
                <label class="btn btn-outline-primary" for="eveningTimeSlot">Evening</label>
            </div>
            
            <div class="overflow-auto">
                <div class="d-flex" id="calendarHeader">
                    <!-- Calendar headers will be populated by JavaScript -->
                </div>
                
                <div id="calendarGrid">
                    <!-- Calendar grid will be populated by JavaScript -->
                </div>
            </div>
            
            <div class="legend mt-4">
                <div class="legend-item">
                    <div class="legend-color legend-available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-booked"></div>
                    <span>Booked</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);
    
    document.getElementById('start_date').value = formatDate(today);
    document.getElementById('end_date').value = formatDate(nextWeek);
    
    // Fetch initial data
    fetchAvailabilityData();
    
    // Handle form submission
    document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchAvailabilityData();
    });
    
    // Handle time slot radio buttons
    document.querySelectorAll('input[name="timeSlotRadio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (allData) {
                renderCalendarView(allData.dateRange, this.value);
            }
        });
    });
    
    // Initialize function type legend
    const functionTypeLegend = document.getElementById('functionTypeLegend');
    if (functionTypeLegend) {
        functionTypeLegend.style.display = 'block';
    }
});

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

let allData = null;
let selectedDay = null;

// Define a set of distinct colors for sequence numbers
const sequenceColors = [
    '#FF5733', // Orange-red
    '#33FF57', // Bright green
    '#3375FF', // Blue
    '#FF33B8', // Pink
    '#33F8FF', // Cyan
    '#FFDA33', // Yellow
    '#9C33FF', // Purple
    '#FF8A33', // Orange
    '#33FFA8', // Mint
    '#FF336E'  // Deep pink
];

// Create a mapping of booking group IDs to sequence numbers and colors
let bookingGroupSequence = new Map();
let sequenceCounter = 0;

// This function will assign a sequence number and color to each booking group
function assignSequenceColors(dateRange) {
    // Reset the mapping and counter
    bookingGroupSequence.clear();
    sequenceCounter = 0;
    
    if (!dateRange || !Array.isArray(dateRange)) {
        return;
    }
    
    // First, collect all unique booking groups
    const groupIds = new Set();
    
    dateRange.forEach(day => {
        if (!day || !day.bookingGroups || !Array.isArray(day.bookingGroups)) return;
        
        day.bookingGroups.forEach(group => {
            if (group && group.id && !groupIds.has(group.id)) {
                groupIds.add(group.id);
                
                // Assign a sequence number and color
                const sequenceNum = sequenceCounter + 1;
                const colorIndex = sequenceCounter % sequenceColors.length;
                const color = sequenceColors[colorIndex];
                
                bookingGroupSequence.set(group.id, {
                    sequenceNum,
                    color
                });
                
                sequenceCounter++;
            }
        });
    });
    
    console.log('Assigned sequence colors:', bookingGroupSequence);
}

function fetchAvailabilityData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'flex';
    document.getElementById('statsContainer').style.display = 'none';
    document.getElementById('heatMapContainer').style.display = 'none';
    document.getElementById('calendarContainer').style.display = 'none';
    document.getElementById('detailedViewContainer').style.display = 'none';
    
    console.log('Fetching availability data for dates:', startDate, 'to', endDate);
    
    // Make API call
    axios.get('/room-visualizer/data', {
        params: {
            start_date: startDate,
            end_date: endDate
        }
    })
    .then(function(response) {
        console.log('Data received successfully:', response.data);
        
        // Hide loading indicator
        document.getElementById('loadingIndicator').style.display = 'none';
        
        // Store the data
        allData = response.data;
        
        // Check if we got an error response
        if (response.data.error) {
            console.error('Error in response:', response.data.error);
            alert('Error receiving data: ' + response.data.error);
            return;
        }
        
        // Assign sequence colors to booking groups
        assignSequenceColors(response.data.dateRange);
        
        // Update the stats
        updateStats(response.data.stats);
        
        // Render heat map
        renderHeatMap(response.data.dateRange);
        
        // Render calendar view with all time slots initially
        renderCalendarView(response.data.dateRange, 'all');
        
        // Render booking group legend
        renderBookingGroupLegend(response.data.dateRange);
        
        // Show the containers
        document.getElementById('statsContainer').style.display = 'flex';
        document.getElementById('heatMapContainer').style.display = 'block';
        document.getElementById('calendarContainer').style.display = 'block';
    })
    .catch(function(error) {
        document.getElementById('loadingIndicator').style.display = 'none';
        
        console.error('Error fetching availability data:', error);
        
        if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
            console.error('Response headers:', error.response.headers);
            alert('Error fetching availability data: ' + 
                  (error.response.data.error || error.response.data.message || 'Server responded with an error'));
        } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
            alert('Error fetching availability data: No response received from server');
        } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Error setting up request:', error.message);
            alert('Error fetching availability data: ' + error.message);
        }
    });
}

function updateStats(stats) {
    document.getElementById('totalDays').textContent = stats.totalDays;
    document.getElementById('morningAvailability').textContent = stats.timeSlotStats.morning + '%';
    document.getElementById('afternoonAvailability').textContent = stats.timeSlotStats.afternoon + '%';
    document.getElementById('eveningAvailability').textContent = stats.timeSlotStats.evening + '%';
}

function renderHeatMap(dateRange) {
    const heatMapContainer = document.getElementById('heatMap');
    heatMapContainer.innerHTML = '';
    
    if (!dateRange || !Array.isArray(dateRange)) {
        console.error('Invalid dateRange data:', dateRange);
        return;
    }
    
    dateRange.forEach(day => {
        if (!day) return;
        
        const heatMapDay = document.createElement('div');
        heatMapDay.className = 'heat-map-day';
        
        const dateHeader = document.createElement('div');
        dateHeader.className = 'heat-map-date';
        dateHeader.innerHTML = `${day.dayOfWeek} ${day.formattedDate ? day.formattedDate.split(', ')[0] : ''}`;
        
        const timeSlotsContainer = document.createElement('div');
        timeSlotsContainer.className = 'time-slots-container';
        
        // Check if timeSlots exist before trying to access them
        if (day.timeSlots) {
            // Morning slot
            if (day.timeSlots.morning) {
                const morningSlot = document.createElement('div');
                morningSlot.className = 'time-slot';
                const availabilityPercentage = day.timeSlots.morning.availabilityPercentage || 0;
                morningSlot.innerHTML = `
                    <div class="time-slot-label">Morning</div>
                    <div class="time-slot-availability available-${Math.floor(availabilityPercentage / 10) * 10}">
                        ${availabilityPercentage}%
                    </div>
                `;
                timeSlotsContainer.appendChild(morningSlot);
            }
            
            // Afternoon slot
            if (day.timeSlots.afternoon) {
                const afternoonSlot = document.createElement('div');
                afternoonSlot.className = 'time-slot';
                const availabilityPercentage = day.timeSlots.afternoon.availabilityPercentage || 0;
                afternoonSlot.innerHTML = `
                    <div class="time-slot-label">Afternoon</div>
                    <div class="time-slot-availability available-${Math.floor(availabilityPercentage / 10) * 10}">
                        ${availabilityPercentage}%
                    </div>
                `;
                timeSlotsContainer.appendChild(afternoonSlot);
            }
            
            // Evening slot
            if (day.timeSlots.evening) {
                const eveningSlot = document.createElement('div');
                eveningSlot.className = 'time-slot';
                const availabilityPercentage = day.timeSlots.evening.availabilityPercentage || 0;
                eveningSlot.innerHTML = `
                    <div class="time-slot-label">Evening</div>
                    <div class="time-slot-availability available-${Math.floor(availabilityPercentage / 10) * 10}">
                        ${availabilityPercentage}%
                    </div>
                `;
                timeSlotsContainer.appendChild(eveningSlot);
            }
        }
        
        heatMapDay.appendChild(dateHeader);
        heatMapDay.appendChild(timeSlotsContainer);
        
        heatMapDay.addEventListener('click', () => {
            showDetailedView(day);
        });
        
        heatMapContainer.appendChild(heatMapDay);
    });
}

function showDetailedView(day) {
    if (!day) {
        console.error('Invalid day data:', day);
        return;
    }
    
    selectedDay = day;
    const container = document.getElementById('detailedViewContainer');
    const title = document.getElementById('detailedViewTitle');
    
    title.textContent = `Room Availability for ${day.formattedDate || 'Selected Date'}`;
    
    // Check if timeSlots exist before trying to access them
    if (day.timeSlots) {
        // Populate time slot tabs
        if (day.timeSlots.morning) populateTimeSlotTab('morning', day.timeSlots.morning);
        if (day.timeSlots.afternoon) populateTimeSlotTab('afternoon', day.timeSlots.afternoon);
        if (day.timeSlots.evening) populateTimeSlotTab('evening', day.timeSlots.evening);
    }
    
    // Show first tab
    const morningTab = new bootstrap.Tab(document.getElementById('morning-tab'));
    morningTab.show();
    
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth' });
}

function populateTimeSlotTab(timeSlot, slotData) {
    if (!slotData) {
        console.error('Invalid slot data for', timeSlot);
        return;
    }
    
    const container = document.getElementById(`${timeSlot}AvailabilityContainer`);
    if (!container) {
        console.error('Container not found for', timeSlot);
        return;
    }
    
    container.innerHTML = '';
    
    // Group all rooms by type
    const roomsByType = {
        'Luxury Rooms': ['Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa', 'Mayura', 'Lihini'],
        'Standard Rooms': ['121', '122', '123', '124', '106', '107', '108', '109'],
        'Special': ['CH Room'],
        'Deluxe Rooms': ['130', '131', '132', '133', '134'],
        'Economy Rooms': ['101', '102', '103', '104', '105']
    };
    
    // Add time slot summary
    const summaryDiv = document.createElement('div');
    summaryDiv.className = 'alert alert-info';
    
    // Make sure to check if the properties exist
    const availableRooms = Array.isArray(slotData.availableRooms) ? slotData.availableRooms : [];
    const bookedRooms = Array.isArray(slotData.bookedRooms) ? slotData.bookedRooms : [];
    const availabilityPercentage = slotData.availabilityPercentage || 0;
    
    summaryDiv.innerHTML = `
        <strong>Availability:</strong> ${availabilityPercentage}% of rooms available (${availableRooms.length} out of ${availableRooms.length + bookedRooms.length} rooms)
    `;
    container.appendChild(summaryDiv);
    
    // Helper function to find booking group for a room
    const findBookingGroupForRoom = (roomName) => {
        if (!slotData.bookingGroups || !Array.isArray(slotData.bookingGroups)) return null;
        
        for (const group of slotData.bookingGroups) {
            if (group && group.rooms && Array.isArray(group.rooms) && group.rooms.includes(roomName)) {
                return group;
            }
        }
        return null;
    };
    
    // Render each room type
    for (const [type, rooms] of Object.entries(roomsByType)) {
        const typeHeader = document.createElement('div');
        typeHeader.className = 'room-type-header';
        typeHeader.textContent = type;
        container.appendChild(typeHeader);
        
        const roomGrid = document.createElement('div');
        roomGrid.className = 'd-flex flex-wrap gap-2 mb-4';
        
        rooms.forEach(room => {
            const isBooked = bookedRooms.includes(room);
            const bookingGroup = isBooked ? findBookingGroupForRoom(room) : null;
            
            // Get sequence color if available
            let cardColor = '#989e9b';  // Default color for available
            if (isBooked) {
                if (bookingGroup && bookingGroup.id) {
                    const sequenceInfo = bookingGroupSequence.get(bookingGroup.id);
                    cardColor = sequenceInfo ? sequenceInfo.color : bookingGroup.color || '#dc3545';
                } else {
                    cardColor = '#dc3545';  // Default red for booked
                }
            }
            
            const roomCard = document.createElement('div');
            roomCard.className = 'card mb-0';
            roomCard.style.width = '130px';  // Increased width for more content
            roomCard.style.borderColor = cardColor;
            roomCard.style.borderWidth = '2px';
            
            let bookingInfo = '';
            if (bookingGroup) {
                // Get sequence number if available
                const sequenceInfo = bookingGroupSequence.get(bookingGroup.id);
                const sequenceNum = sequenceInfo ? sequenceInfo.sequenceNum + '. ' : '';
                
                // Add sequence number, function type, and times to the booking info
                bookingInfo = `
                    <small class="text-muted d-block">${sequenceNum}${bookingGroup.function_type || 'Unknown'}</small>
                    <small class="text-muted d-block">${bookingGroup.start_time || ''} - ${bookingGroup.end_time || ''}</small>
                `;
            }
            
            roomCard.innerHTML = `
                <div class="card-body p-2 text-center">
                    <h5 class="card-title mb-0">${room}</h5>
                    <p class="card-text mt-2 mb-0" style="color: ${cardColor}">
                        <i class="fas fa-${isBooked ? 'times-circle' : 'check-circle'}"></i>
                        ${isBooked ? 'Booked' : 'Available'}
                    </p>
                    ${bookingInfo}
                </div>
            `;
            
            roomGrid.appendChild(roomCard);
        });
        
        container.appendChild(roomGrid);
    }
    
    // Add booking groups legend for this time slot
    if (slotData.bookingGroups && Array.isArray(slotData.bookingGroups) && slotData.bookingGroups.length > 0) {
        const legendDiv = document.createElement('div');
        legendDiv.className = 'mt-4';
        legendDiv.innerHTML = '<h6>Booking Groups:</h6>';
        
        const legendGrid = document.createElement('div');
        legendGrid.className = 'd-flex flex-wrap gap-2 mb-4';
        
        slotData.bookingGroups.forEach(group => {
            if (!group) return;
            
            // Get sequence info
            const sequenceInfo = bookingGroupSequence.get(group.id);
            const sequenceNum = sequenceInfo ? sequenceInfo.sequenceNum : '';
            const color = sequenceInfo ? sequenceInfo.color : (group.color || '#cccccc');
            
            const groupItem = document.createElement('div');
            groupItem.className = 'p-2 border rounded';
            groupItem.style.borderColor = color;
            groupItem.style.borderWidth = '2px';
            
            groupItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width: 20px; height: 20px; background-color: ${color}; margin-right: 8px; border-radius: 4px;"></div>
                    <div>
                        <strong>${sequenceNum}. ${group.function_type || 'Unknown'}</strong>
                        <small class="d-block text-muted">${group.name || 'Unknown'}</small>
                        <small class="d-block text-muted">${group.start_time || ''} - ${group.end_time || ''}</small>
                    </div>
                </div>
            `;
            
            legendGrid.appendChild(groupItem);
        });
        
        legendDiv.appendChild(legendGrid);
        container.appendChild(legendDiv);
    }
}

function renderCalendarView(dateRange, timeSlotFilter = 'all') {
    if (!dateRange || !Array.isArray(dateRange)) {
        console.error('Invalid dateRange data:', dateRange);
        return;
    }
    
    const calendarHeader = document.getElementById('calendarHeader');
    const calendarGrid = document.getElementById('calendarGrid');
    
    if (!calendarHeader || !calendarGrid) {
        console.error('Calendar containers not found');
        return;
    }
    
    calendarHeader.innerHTML = '';
    calendarGrid.innerHTML = '';
    
    // Add room name column header
    const roomNameHeader = document.createElement('div');
    roomNameHeader.className = 'room-name';
    roomNameHeader.textContent = 'Room';
    calendarHeader.appendChild(roomNameHeader);
    
    // Add date column headers
    dateRange.forEach(day => {
        if (!day) return;
        
        const dateHeader = document.createElement('div');
        dateHeader.className = 'calendar-day date-header';
        dateHeader.innerHTML = `
            ${day.formattedDate || ''}
            <div class="day-of-week">${day.dayOfWeek || ''}</div>
        `;
        calendarHeader.appendChild(dateHeader);
    });
    
    // Group rooms by type for the grid
    const roomsByType = {
        'Luxury Rooms': ['Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa', 'Mayura', 'Lihini'],
        'Standard Rooms': ['121', '122', '123', '124', '106', '107', '108', '109'],
        'Special': ['CH Room'],
        'Deluxe Rooms': ['130', '131', '132', '133', '134'],
        'Economy Rooms': ['101', '102', '103', '104', '105']
    };
    
    // For each room type, create a type header and room rows
    for (const [type, rooms] of Object.entries(roomsByType)) {
        // Create type header
        const typeHeader = document.createElement('div');
        typeHeader.className = 'room-row';
        typeHeader.innerHTML = `<div class="room-name" style="background-color: #e9ecef; font-weight: bold;">${type}</div>`;
        
        // Add empty cells for each date
        dateRange.forEach(() => {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'room-status';
            emptyCell.style.backgroundColor = '#e9ecef';
            typeHeader.appendChild(emptyCell);
        });
        
        calendarGrid.appendChild(typeHeader);
        
        // Create rows for each room
        rooms.forEach(room => {
            const roomRow = document.createElement('div');
            roomRow.className = 'room-row';
            
            // Add room name cell
            const roomNameCell = document.createElement('div');
            roomNameCell.className = 'room-name';
            roomNameCell.textContent = room;
            roomRow.appendChild(roomNameCell);
            
            // Add cells for each date
            dateRange.forEach(day => {
                if (!day) {
                    // If day is invalid, add an empty cell
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'room-status';
                    roomRow.appendChild(emptyCell);
                    return;
                }
                
                let isBooked = false;
                
                // Check if booked based on the selected time slot filter
                if (timeSlotFilter === 'all') {
                    // For "all", check if booked in any time slot
                    isBooked = Array.isArray(day.bookedRooms) && day.bookedRooms.includes(room);
                } else {
                    // Check the specific time slot
                    isBooked = day.timeSlots && 
                              day.timeSlots[timeSlotFilter] && 
                              Array.isArray(day.timeSlots[timeSlotFilter].bookedRooms) && 
                              day.timeSlots[timeSlotFilter].bookedRooms.includes(room);
                }
                
                const statusCell = document.createElement('div');
                statusCell.className = `room-status ${isBooked ? 'booked' : 'available'}`;
                statusCell.style.backgroundColor = isBooked ? 'rgba(220, 53, 69, 0.1)' : 'rgba(25, 135, 84, 0.1)';
                
                if (timeSlotFilter === 'all') {
                    // For "all" view, show detailed time slot indicators with sequence colors
                    
                    // Helper function to find booking group for a room in a time slot
                    const findBookingGroupForRoom = (timeSlot, roomName) => {
                        if (!day.timeSlots || 
                            !day.timeSlots[timeSlot] || 
                            !day.timeSlots[timeSlot].bookingGroups || 
                            !Array.isArray(day.timeSlots[timeSlot].bookingGroups)) {
                            return null;
                        }
                        
                        for (const group of day.timeSlots[timeSlot].bookingGroups) {
                            if (group && 
                                group.rooms && 
                                Array.isArray(group.rooms) && 
                                group.rooms.includes(roomName)) {
                                return group;
                            }
                        }
                        return null;
                    };
                    
                    // Get booking groups for each time slot for this room
                    const morningGroup = findBookingGroupForRoom('morning', room);
                    const afternoonGroup = findBookingGroupForRoom('afternoon', room);
                    const eveningGroup = findBookingGroupForRoom('evening', room);
                    
                    // Check if booked in each time slot
                    const isMorningBooked = day.timeSlots && 
                                          day.timeSlots.morning && 
                                          Array.isArray(day.timeSlots.morning.bookedRooms) && 
                                          day.timeSlots.morning.bookedRooms.includes(room);
                    
                    const isAfternoonBooked = day.timeSlots && 
                                             day.timeSlots.afternoon && 
                                             Array.isArray(day.timeSlots.afternoon.bookedRooms) && 
                                             day.timeSlots.afternoon.bookedRooms.includes(room);
                    
                    const isEveningBooked = day.timeSlots && 
                                           day.timeSlots.evening && 
                                           Array.isArray(day.timeSlots.evening.bookedRooms) && 
                                           day.timeSlots.evening.bookedRooms.includes(room);
                    
                    // Get sequence colors for each group
                    const getMorningColor = () => {
                        if (!isMorningBooked) return '#989e9b';  // Default gray for available
                        if (!morningGroup) return '#dc3545';     // Default red for booked
                        
                        const sequenceInfo = bookingGroupSequence.get(morningGroup.id);
                        return sequenceInfo ? sequenceInfo.color : morningGroup.color || '#dc3545';
                    };
                    
                    const getAfternoonColor = () => {
                        if (!isAfternoonBooked) return '#989e9b';  // Default gray for available
                        if (!afternoonGroup) return '#dc3545';     // Default red for booked
                        
                        const sequenceInfo = bookingGroupSequence.get(afternoonGroup.id);
                        return sequenceInfo ? sequenceInfo.color : afternoonGroup.color || '#dc3545';
                    };
                    
                    const getEveningColor = () => {
                        if (!isEveningBooked) return '#989e9b';  // Default gray for available
                        if (!eveningGroup) return '#dc3545';     // Default red for booked
                        
                        const sequenceInfo = bookingGroupSequence.get(eveningGroup.id);
                        return sequenceInfo ? sequenceInfo.color : eveningGroup.color || '#dc3545';
                    };
                    
                    // Get sequence numbers for tooltips
                    const getMorningTooltip = () => {
                        if (!isMorningBooked) return 'Available: Morning';
                        if (!morningGroup) return 'Booked: Morning';
                        
                        const sequenceInfo = bookingGroupSequence.get(morningGroup.id);
                        const sequenceNum = sequenceInfo ? `${sequenceInfo.sequenceNum}. ` : '';
                        
                        return `${sequenceNum}${morningGroup.function_type} (${morningGroup.start_time} - ${morningGroup.end_time}): Morning`;
                    };
                    
                    const getAfternoonTooltip = () => {
                        if (!isAfternoonBooked) return 'Available: Afternoon';
                        if (!afternoonGroup) return 'Booked: Afternoon';
                        
                        const sequenceInfo = bookingGroupSequence.get(afternoonGroup.id);
                        const sequenceNum = sequenceInfo ? `${sequenceInfo.sequenceNum}. ` : '';
                        
                        return `${sequenceNum}${afternoonGroup.function_type} (${afternoonGroup.start_time} - ${afternoonGroup.end_time}): Afternoon`;
                    };
                    
                    const getEveningTooltip = () => {
                        if (!isEveningBooked) return 'Available: Evening';
                        if (!eveningGroup) return 'Booked: Evening';
                        
                        const sequenceInfo = bookingGroupSequence.get(eveningGroup.id);
                        const sequenceNum = sequenceInfo ? `${sequenceInfo.sequenceNum}. ` : '';
                        
                        return `${sequenceNum}${eveningGroup.function_type} (${eveningGroup.start_time} - ${eveningGroup.end_time}): Evening`;
                    };
                    
                    // Create the time slot badges with sequence colors
                    statusCell.innerHTML = `
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                            <div class="d-flex justify-content-around w-100">
                                <span class="badge" 
                                      style="background-color: ${getMorningColor()}; border: none; color: white;" 
                                      title="${getMorningTooltip()}">
                                    M
                                </span>
                                <span class="badge" 
                                      style="background-color: ${getAfternoonColor()}; border: none; color: white;" 
                                      title="${getAfternoonTooltip()}">
                                    A
                                </span>
                                <span class="badge" 
                                      style="background-color: ${getEveningColor()}; border: none; color: white;" 
                                      title="${getEveningTooltip()}">
                                    E
                                </span>
                            </div>
                        </div>
                    `;
                } else {
                    // For specific time slot view, find the booking group if it exists
                    let bookingGroup = null;
                    
                    if (isBooked && 
                        day.timeSlots && 
                        day.timeSlots[timeSlotFilter] && 
                        day.timeSlots[timeSlotFilter].bookingGroups && 
                        Array.isArray(day.timeSlots[timeSlotFilter].bookingGroups)) {
                        
                        for (const group of day.timeSlots[timeSlotFilter].bookingGroups) {
                            if (group && 
                                group.rooms && 
                                Array.isArray(group.rooms) && 
                                group.rooms.includes(room)) {
                                bookingGroup = group;
                                break;
                            }
                        }
                    }
                    
                    // Use sequence color if available
                    let badgeColor = '#198754';  // Default green for available
                    let tooltipText = 'Available';
                    
                    if (isBooked) {
                        if (bookingGroup) {
                            const sequenceInfo = bookingGroupSequence.get(bookingGroup.id);
                            badgeColor = sequenceInfo ? sequenceInfo.color : (bookingGroup.color || '#dc3545');
                            
                          
                            
                            const sequenceNum = sequenceInfo ? `${sequenceInfo.sequenceNum}. ` : '';
                            tooltipText = `${sequenceNum}${bookingGroup.function_type} (${bookingGroup.start_time} - ${bookingGroup.end_time})`;
                        } else {
                            badgeColor = '#dc3545';  // Default red for booked
                            tooltipText = 'Booked';
                        }
                    }
                    
                    statusCell.innerHTML = `
                        <i class="fas fa-${isBooked ? 'times-circle' : 'check-circle'}" 
                           style="color: ${badgeColor};" 
                           title="${tooltipText}">
                        </i>
                    `;
                }
                
                roomRow.appendChild(statusCell);
            });
            
            calendarGrid.appendChild(roomRow);
        });
    }
}

// Update renderBookingGroupLegend to use sequence colors
function renderBookingGroupLegend(dateRange) {
    // Get all unique booking groups across all dates
    const allGroups = [];
    const groupIds = new Set();
    
    if (!dateRange || !Array.isArray(dateRange)) {
        console.error('Invalid dateRange data:', dateRange);
        document.getElementById('bookingGroupLegend').style.display = 'none';
        return;
    }
    
    // First, ensure sequence numbers and colors are assigned to all booking groups
    assignSequenceColors(dateRange);
    
    dateRange.forEach(day => {
        if (!day) return;
        
        // Check if bookingGroups exists and is an array
        if (day.bookingGroups && Array.isArray(day.bookingGroups)) {
            day.bookingGroups.forEach(group => {
                if (group && group.id && !groupIds.has(group.id)) {
                    groupIds.add(group.id);
                    allGroups.push(group);
                }
            });
        }
    });
    
    // Create the legend container
    const legendContainer = document.getElementById('bookingGroupLegend');
    if (!legendContainer) return;
    
    legendContainer.innerHTML = '';
    
    if (allGroups.length === 0) {
        legendContainer.style.display = 'none';
        return;
    }
    
    legendContainer.style.display = 'block';
    
    // Create a legend item for each booking group
    allGroups.forEach(group => {
        if (!group) return;
        
        const sequenceInfo = bookingGroupSequence.get(group.id) || { sequenceNum: 0, color: group.color || '#cccccc' };
        
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        legendItem.innerHTML = `
            <div class="legend-color" style="background-color: ${sequenceInfo.color};"></div>
            <div>
                <span>${sequenceInfo.sequenceNum}. ${group.function_type || 'Unknown'} - ${group.name || 'Unknown'}</span>
                <small class="d-block text-muted">${group.start_time || ''} - ${group.end_time || ''}</small>
            </div>
        `;
        legendContainer.appendChild(legendItem);
    });
    



}
    </script>