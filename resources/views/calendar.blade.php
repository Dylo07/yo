<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <title>Booking Calendar</title>
   

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        #booking-form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        #booking-form h1 {
            color: #007bff;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        #booking-form label {
            color: #495057;
            font-weight: bold;
        }
        #booking-form input, 
        #booking-form textarea, 
        #booking-form select {
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        #booking-form .checkbox-group label {
            margin-right: 15px;
            display: inline-block;
        }
        #booking-form button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            width: 100%;
        }
        #booking-form button:hover {
            background-color: #0056b3;
        }
        .fc-event {
            color: #ffffff !important;
        }
        .calendar-container {
            max-width: 1200px;
            margin: 30px auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center my-4">Booking Calendar</h1>
        
    <!-- Add the Home Button -->
    <a href="/" class="btn btn-primary mb-3" style="float: right;">Home</a>
        <form id="booking-form">
            <h1>Enter Booking Details</h1>
            <div class="mb-3">
                <label for="function_type" class="form-label">Function Type:</label>
                <select id="function_type" name="function_type" class="form-control" required>
                    <option value="" selected>Select Function Type</option>
                    <option value="Wedding">Wedding</option>
                    <option value="Night In Group">Night In Group</option>
                    <option value="Day Out">Day Out</option>
                    <option value="Couple Package">Couple Package</option>
                    <option value="Room Only">Room Only</option>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="date" class="form-label">Start Date:</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="start_time" class="form-label">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="end_time" class="form-label">End Time:</label>
                    <input type="time" id="end_time" name="end_time" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="time_slot" class="form-label">Advance Payment:</label>
                <input type="text" id="time_slot" name="advance_payment" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="room_number" class="form-label">Room Numbers:</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="room_number[]" value="Ahala"> Ahala</label>
                    <label><input type="checkbox" name="room_number[]" value="Sepalika"> Sepalika</label>
                    <label><input type="checkbox" name="room_number[]" value="Sudu Araliya"> Sudu Araliya</label>
                    <label><input type="checkbox" name="room_number[]" value="Orchid"> Orchid</label>
                    <label><input type="checkbox" name="room_number[]" value="Olu"> Olu</label>
                    <label><input type="checkbox" name="room_number[]" value="Nelum"> Nelum</label>
                    <label><input type="checkbox" name="room_number[]" value="Hansa"> Hansa</label>
                    <label><input type="checkbox" name="room_number[]" value="Mayura"> Mayura</label>
                    <label><input type="checkbox" name="room_number[]" value="Lihini"> Lihini</label>
                    <label><input type="checkbox" name="room_number[]" value="121"> 121</label>
                    <label><input type="checkbox" name="room_number[]" value="122"> 122</label>
                    <label><input type="checkbox" name="room_number[]" value="123"> 123</label>
                    <label><input type="checkbox" name="room_number[]" value="124"> 124</label>
                    <label><input type="checkbox" name="room_number[]" value="106"> 106</label>
                    <label><input type="checkbox" name="room_number[]" value="107"> 107</label>
                    <label><input type="checkbox" name="room_number[]" value="108"> 108</label>
                    <label><input type="checkbox" name="room_number[]" value="109"> 109</label>
                    <label><input type="checkbox" name="room_number[]" value="CH Room"> CH Room</label>
                    <label><input type="checkbox" name="room_number[]" value="130"> 130</label>
                    <label><input type="checkbox" name="room_number[]" value="131"> 131</label>
                    <label><input type="checkbox" name="room_number[]" value="132"> 132</label>
                    <label><input type="checkbox" name="room_number[]" value="133"> 133</label>
                    <label><input type="checkbox" name="room_number[]" value="134"> 134</label>
                    <label><input type="checkbox" name="room_number[]" value="101"> 101</label>
                    <label><input type="checkbox" name="room_number[]" value="102"> 102</label>
                    <label><input type="checkbox" name="room_number[]" value="103"> 103</label>
                    <label><input type="checkbox" name="room_number[]" value="104"> 104</label>
                    <label><input type="checkbox" name="room_number[]" value="105"> 105</label>
                    
                    
                </div>
                </div>
            
            <div class="mb-3">
                <label for="guest_count" class="form-label">Guest Count (Adults and Kids):</label>
                <input type="text" id="guest_count" name="guest_count" class="form-control" placeholder="E.g., Adults: 2, Kids: 3" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Package Price & Details:</label>
                <textarea id="name" name="name" class="form-control" rows="4" style="resize: none;" required></textarea>
            </div>
            <button type="submit" class="btn">Book</button>
        </form>
    </div>

    <div class="calendar-container">
        <div id="calendar"></div>
    </div>

    <!-- Modal for Available Rooms -->
    <div id="availableRoomsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Available Rooms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Date:</strong> <span id="selectedDate"></span></p>
                    <ul id="availableRoomsList">
                        <!-- Rooms will be listed here dynamically -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>





    <!-- Modal for event details -->
    <div id="eventModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Event details will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" id="editEvent" class="btn btn-primary">Edit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Edit function   -->

<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Package Price & Details:</label>
                        <textarea id="editName" name="name" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editFunctionType" class="form-label">Function Type:</label>
                        <select id="editFunctionType" name="function_type" class="form-control" required>
                            <option value="Wedding">Wedding</option>
                            <option value="Night In Group">Night In Group</option>
                            <option value="Day Out">Day Out</option>
                            <option value="Couple Package">Couple Package</option>
                            <option value="Room Only">Room Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStart" class="form-label">Start Date & Time:</label>
                        <input type="datetime-local" id="editStart" name="start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEnd" class="form-label">End Date & Time:</label>
                        <input type="datetime-local" id="editEnd" name="end" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editContactNumber" class="form-label">Contact Number:</label>
                        <input type="text" id="editContactNumber" name="contact_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAdvancePayment" class="form-label">Advance Payment:</label>
                        <input type="text" id="editAdvancePayment" name="advance_payment" class="form-control" required>
                    </div>
                    <div class="mb-3">
    <label for="editRoomNumbers" class="form-label">Room Numbers:</label>
    <div class="checkbox-group" id="editRoomNumbers">

        
                    <label><input type="checkbox" name="room_number[]" value="Ahala"> Ahala</label>
                    <label><input type="checkbox" name="room_number[]" value="Sepalika"> Sepalika</label>
                    <label><input type="checkbox" name="room_number[]" value="Sudu Araliya"> Sudu Araliya</label>
                    <label><input type="checkbox" name="room_number[]" value="Orchid"> Orchid</label>
                    <label><input type="checkbox" name="room_number[]" value="Olu"> Olu</label>
                    <label><input type="checkbox" name="room_number[]" value="Nelum"> Nelum</label>
                    <label><input type="checkbox" name="room_number[]" value="Hansa"> Hansa</label>
                    <label><input type="checkbox" name="room_number[]" value="Mayura"> Mayura</label>
                    <label><input type="checkbox" name="room_number[]" value="Lihini"> Lihini</label>
                    <label><input type="checkbox" name="room_number[]" value="121"> 121</label>
                    <label><input type="checkbox" name="room_number[]" value="122"> 122</label>
                    <label><input type="checkbox" name="room_number[]" value="123"> 123</label>
                    <label><input type="checkbox" name="room_number[]" value="124"> 124</label>
                    <label><input type="checkbox" name="room_number[]" value="106"> 106</label>
                    <label><input type="checkbox" name="room_number[]" value="107"> 107</label>
                    <label><input type="checkbox" name="room_number[]" value="108"> 108</label>
                    <label><input type="checkbox" name="room_number[]" value="109"> 109</label>
                    <label><input type="checkbox" name="room_number[]" value="CH Room"> CH Room</label>
                    <label><input type="checkbox" name="room_number[]" value="130"> 130</label>
                    <label><input type="checkbox" name="room_number[]" value="131"> 131</label>
                    <label><input type="checkbox" name="room_number[]" value="132"> 132</label>
                    <label><input type="checkbox" name="room_number[]" value="133"> 133</label>
                    <label><input type="checkbox" name="room_number[]" value="134"> 134</label>
                    <label><input type="checkbox" name="room_number[]" value="101"> 101</label>
                    <label><input type="checkbox" name="room_number[]" value="102"> 102</label>
                    <label><input type="checkbox" name="room_number[]" value="103"> 103</label>
                    <label><input type="checkbox" name="room_number[]" value="104"> 104</label>
                    <label><input type="checkbox" name="room_number[]" value="105"> 105</label>
    </div>
</div>

                    <div class="mb-3">
                        <label for="editGuestCount" class="form-label">Guest Count:</label>
                        <input type="text" id="editGuestCount" name="guest_count" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>




    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("booking-form").addEventListener("submit", async function (e) {
            e.preventDefault();
            const formData = new FormData(e.target);

            const start = `${formData.get("date")}T${formData.get("start_time")}`;
            const endDate = formData.get("end_date") ? formData.get("end_date") : formData.get("date");
            const end = formData.get("end_time") ? `${endDate}T${formData.get("end_time")}` : null;
            const advancePayment = formData.get("advance_payment") || "0.00";


            try {
                await axios.post("/bookings", {
                    start: start,
                    end: end,
                    advance_payment: formData.get("advance_payment"),
                    name: formData.get("name"),
                    function_type: formData.get("function_type"),
                    contact_number: formData.get("contact_number"),
                    room_numbers: formData.getAll("room_number[]"),
                    guest_count: formData.get("guest_count"),
                }, {
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    },
                });

                alert("Booking successful!");
                location.reload();
            } catch (error) {
                console.error("Error making booking:", error.response || error.message);
                alert("Failed to make booking. Please try again.");
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const calendarEl = document.getElementById("calendar");

            const functionTypeColors = {
                "Wedding": "#ff5733",
                "Night In Group": "#33ff57",
                "Day Out": "#3375ff",
                "Couple Package": "#ff33b8",
                "Room Only": "#33f8ff",
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
                },
                events: "/bookings",
                dateClick: async function (info) {
    const selectedDate = info.dateStr; // The date clicked
    document.getElementById("selectedDate").textContent = selectedDate;

    try {
        const response = await axios.get(`/available-rooms`, {
            params: { date: selectedDate },
        });

        const availableRooms = response.data;

        // Populate the modal with available rooms
        const roomsList = document.getElementById("availableRoomsList");
        roomsList.innerHTML = "";
        if (availableRooms.length > 0) {
            availableRooms.forEach((room) => {
                const listItem = document.createElement("li");
                listItem.textContent = room;
                roomsList.appendChild(listItem);
            });
        } else {
            roomsList.innerHTML = "<li>No rooms available</li>";
        }

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById("availableRoomsModal"));
        modal.show();
    } catch (error) {
        console.error("Error fetching available rooms:", error);
        alert("Failed to fetch available rooms.");
    }
},





                eventContent: function (info) {
    const functionType = info.event.extendedProps.function_type;
    const guestCount = info.event.extendedProps.guest_count;

    const wrapper = document.createElement("div");
    const title = document.createElement("div");
    title.textContent = functionType || "No Function Type";
    title.style.fontWeight = "bold";

    const guest = document.createElement("div");
    guest.textContent = `Guests: ${guestCount || "N/A"}`;
    wrapper.appendChild(title);
    wrapper.appendChild(guest);

    return { domNodes: [wrapper] };
},






                eventDidMount: function (info) {
                    const functionType = info.event.extendedProps.function_type;
                    const color = functionTypeColors[functionType] || "#6c757d"; // Default ash color
                    info.el.style.backgroundColor = color;
                    info.el.style.borderColor = color;
                    info.el.style.color = "#fff"; // Ensure text visibility

                },
                eventClick: function (info) {
    // Set the title and body of the modal with event details
    document.getElementById("modalTitle").textContent = `Booking Details - ${info.event.title}`;
    document.getElementById("modalBody").innerHTML = `
        <p><strong>Function Type:</strong> ${info.event.extendedProps.function_type}</p>
        <p><strong>Contact Number:</strong> ${info.event.extendedProps.contact_number}</p>
        <p><strong>Room Numbers:</strong> ${info.event.extendedProps.room_numbers || 'N/A'}</p>
        <p><strong>Guest Count:</strong> ${info.event.extendedProps.guest_count || 'N/A'}</p>
        <p><strong>Advance Payment:</strong> ${info.event.extendedProps.advance_payment || 'N/A'}</p>
        <p><strong>Description:</strong> ${info.event.extendedProps.name || 'N/A'}</p>
        <p><strong>Start Time:</strong> ${new Date(info.event.start).toLocaleString()}</p>
        <p><strong>End Time:</strong> ${info.event.end ? new Date(info.event.end).toLocaleString() : 'N/A'}</p>
    `;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById("eventModal"));
    modal.show();
 // Edit button logic
 document.getElementById("editEvent").onclick = function () {
        // Populate the edit form
        document.getElementById("editFunctionType").value = info.event.extendedProps.function_type;
        document.getElementById("editName").value = info.event.extendedProps.name || ""; // Ensure name is pre-filled
        document.getElementById("editStart").value = new Date(info.event.start).toISOString().slice(0, 16);
        document.getElementById("editEnd").value = info.event.end ? new Date(info.event.end).toISOString().slice(0, 16) : '';
        document.getElementById("editContactNumber").value = info.event.extendedProps.contact_number;
        document.getElementById("editAdvancePayment").value = info.event.extendedProps.advance_payment;
        document.getElementById("editGuestCount").value = info.event.extendedProps.guest_count;



       
    const roomNumbers = info.event.extendedProps.room_numbers 
        ? info.event.extendedProps.room_numbers.split(", ") 
        : [];
    
    // Uncheck all checkboxes first
    document.querySelectorAll("#editRoomNumbers input[type='checkbox']").forEach((checkbox) => {
        checkbox.checked = false;
    });

    // Check the relevant checkboxes
    roomNumbers.forEach((room) => {
        const checkbox = document.querySelector(`#editRoomNumbers input[value="${room}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }

    });



        // Show the edit modal
        const editModal = new bootstrap.Modal(document.getElementById("editModal"));
        editModal.show();

        // Save changes
        document.getElementById("editForm").onsubmit = async function (e) {
            e.preventDefault();

             // Collect room numbers
    const roomNumbers = Array.from(document.querySelectorAll("#editRoomNumbers input:checked")).map(
        (checkbox) => checkbox.value
    );

            const updatedData = {
                name: document.getElementById("editName").value, // Include name
        start: document.getElementById("editStart").value,
        end: document.getElementById("editEnd").value,
        function_type: document.getElementById("editFunctionType").value,
        contact_number: document.getElementById("editContactNumber").value,
        advance_payment: document.getElementById("editAdvancePayment").value,
        guest_count: document.getElementById("editGuestCount").value,
        room_numbers: roomNumbers,
            };

            try {
                await axios.put(`/bookings/${info.event.id}`, updatedData, {
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    },
                });

                alert("Booking updated successfully!");
                location.reload();
            } catch (error) {
                console.error("Error updating booking:", error.response || error.message);
                alert("Failed to update booking.");
            }
        };
    };
},



            });

            calendar.render();
        });
    </script>
</body>
</html>
