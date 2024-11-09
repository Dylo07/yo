
<!DOCTYPE html>
<html lang='en'>
  <head>
     <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar</title>

    
    <style>
    body {
        background-color: #f8f9fa;
    }
    #booking-form {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(50, 67, 76, 0.1);
        max-width: 1000px; /* Increased form width */
        margin: 20px auto;
    }
    #booking-form h1 {
        color: #4caf50;
        text-align: center;
    }
    #booking-form label {
        color: #007bff;
        font-weight: bold;
    }
    #booking-form input, 
    #booking-form textarea, 
    #booking-form select {
        border: 2px solid #007bff;
        border-radius: 5px;
        width: 100%; /* Full width for inputs */
        padding: 10px; /* Increased padding for better UX */
    }
    #booking-form button {
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 15px;
        width: 100%; /* Full width for button */
    }
    #booking-form button:hover {
        background-color: #218838;
        color: white;
    }
    /* Change calendar event background color to dark */
.fc-event {
    background-color: #34495e !important; /* Darker color for the event box */
    color: #ecf0f1 !important; /* Light text for readability */
    border: 1px solid #162cf5 !important; /* Border for better visibility */
    width: 95% !important; /* Increase event box width (adjust percentage as needed) */
    margin: 0 auto; /* Center the event box */
}
/* Hover effect for the calendar event */
.fc-event:hover {
    background-color: #162cf5 !important; /* Even darker on hover */
}

     /* Ensure calendar events wrap text */
     .fc-event-title, .fc-event-time, .fc-list-item-title, .fc-list-item-time {
        white-space: normal;
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-word;


    }
    .fc-daygrid-day-top {
    color: #ffffff !important; /* Change date text to white for better visibility */
    font-weight: bold; /* Make the text bold for clarity */
}
     /* Ensure calendar events have scrollable overflow in day cells */
     .fc-daygrid-day-frame {
        max-height: 150px; /* Adjust height based on your preference */
        overflow-y: auto; /* Add vertical scrolling */
        scrollbar-width: thin; /* Make scrollbar thinner */
        
    }

    /* Optional: Style scrollbar for better appearance */
    .fc-daygrid-day-frame::-webkit-scrollbar {
        width: 8px; /* Width of scrollbar */
    }

    .fc-daygrid-day-frame::-webkit-scrollbar-thumb {
        background-color: #007bff; /* Color of scrollbar thumb */
        border-radius: 4px; /* Rounded edges for thumb */
    }

    .fc-daygrid-day-frame::-webkit-scrollbar-track {
        background-color: #f1f1f1; /* Background color of track */
    }
    .fc-daygrid-day-frame {
    background-color: #2c3e50; /* Dark background for all days */
    color: #ecf0f1; /* Light text for better contrast */
    border: 1px solid #34495e; /* Border for clarity */
}

.fc-day-today .fc-daygrid-day-frame {
    background-color: #3ec70c !important; /* Distinct green background for today */
    color: #ffffff !important; /* Light text for today */
    border: 2px solid #16a085 !important; /* Distinct border for today */
}
.fc-list-item:hover {
    background-color: #34495e !important; /* Ensure hover background matches event background */
    color: #ecf0f1 !important; /* Maintain readable text color */
    
}

.fc-list-item .fc-event-title, 
.fc-list-item .fc-event-time {
    color: #ecf0f1 !important; /* Ensure text remains light and readable */
}



</style>

    </head>
   
    <body>
    <h1 class="text-center">Booking Calendar</h1>
    <form id="booking-form">
        <h1>Enter Booking Details</h1>
        <div class="mb-3">
            <label for="date" class="form-label">Start Date:</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time:</label>
            <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control">
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">End Time:</label>
            <input type="time" id="end_time" name="end_time" class="form-control">
        </div>
        <div class="mb-3">
            <label for="time_slot" class="form-label">Advance Payment:</label>
            <input type="text" id="time_slot" name="time_slot" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Description:</label>
            <textarea id="name" name="name" class="form-control" rows="5" style="resize: vertical;" required></textarea>
        </div>
        <button type="submit" class="btn btn-block">Book</button>
    </form>

    <div id='calendar' class="mt-5 container"></div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.css" rel="stylesheet">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/list/main.min.css" rel="stylesheet">
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list/main.min.js"></script>

   <!-- Axios (for making HTTP requests) -->  
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        document.getElementById('booking-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const start = `${formData.get('date')}T${formData.get('start_time')}`;
            const endDate = formData.get('end_date') ? formData.get('end_date') : formData.get('date');
            const end = formData.get('end_time')
                ? `${endDate}T${formData.get('end_time')}`
                : null;

    try {
        await axios.post('/bookings', {
            start: start,
            end: end,
            time_slot: formData.get('time_slot'),
            name: formData.get('name'),
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        alert('Booking successful!');
        location.reload();
    } catch (error) {
        console.error('Error making booking:', error.response || error.message);
        alert('Failed to make booking. Please try again.');
    }
});

       
    

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    },
    selectable: true,
    editable: false, // Disable drag-and-drop entirely
    eventStartEditable: false, // Ensure events cannot be resized or moved
    events: '/bookings', // Fetch events dynamically
    eventContent: function (info) {
        const eventDetails = document.createElement('ul');
        eventDetails.style.listStyleType = 'disc';
        eventDetails.style.paddingLeft = '20px';

        const time = document.createElement('li');
        time.textContent = `Time: ${info.event.start.toLocaleString()} - ${info.event.end ? info.event.end.toLocaleString() : 'N/A'}`;
        eventDetails.appendChild(time);

        const timeSlot = document.createElement('li');
        timeSlot.textContent = `Advance: ${info.event.extendedProps.time_slot}`;
        eventDetails.appendChild(timeSlot);

        const description = document.createElement('li');
        description.textContent = `Description: ${info.event.extendedProps.name}`;
        eventDetails.appendChild(description);

        return { domNodes: [eventDetails] };
    },
    eventDidMount: function (info) {
    const start = new Date(info.event.start);
    const end = info.event.end ? new Date(info.event.end) : start;
    const isMultiDay = start.toDateString() !== end.toDateString();

    // Access the day cell for the event
    const dayCell = info.el.closest('.fc-daygrid-day-frame');

    if (dayCell) {
        if (isMultiDay) {
            // Disable scrolling for multi-day events
            dayCell.style.overflowY = 'visible';
            dayCell.style.maxHeight = 'none';
        } else {
            // Enable scrolling for single-day events
            
            dayCell.style.overflowY = 'auto';
            dayCell.style.maxHeight = '150px'; // Adjust height as needed
        }
    }
},

    eventClick: function (info) {
    const previousDescription = info.event.extendedProps.name;
    const previousTimeSlot = info.event.extendedProps.time_slot;

    const newDescription = prompt('Add to Description:', '');
    const newTimeSlot = prompt('Add to Advance:', '');

    if (newDescription || newTimeSlot) {
        const updatedDescription = previousDescription 
            ? `${previousDescription}  → →  ${newDescription}` // Append with HTML separator
            : newDescription;

        const updatedTimeSlot = previousTimeSlot
            ? `${previousTimeSlot}   → →   ${newTimeSlot}` // Append with HTML separator
            : newTimeSlot;

        axios.put(`/bookings/${info.event.id}`, {
            start: info.event.startStr,
            end: info.event.endStr,
            time_slot: updatedTimeSlot,
            name: updatedDescription,
        })
        .then(() => {
            alert('Booking updated successfully!');
            calendar.refetchEvents();
        })
        .catch((error) => {
            console.error('Error updating booking:', error);
            alert('Failed to update booking. Please try again.');
                        });
                    }
                },
            });

            calendar.render();
        });
    </script>
</body>
</html>