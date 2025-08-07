<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-role" content="{{ Auth::user()->role }}">

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
            max-width: 1800px;
            margin: 30px auto;
        }

        /* Payment and booking styles */
        .payment-record {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }

        .payment-checkbox {
            margin-right: 10px;
        }

        .payment-details {
            margin-left: 25px;
        }

        .checkbox-group label {
            display: inline-block;
            margin: 5px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .checkbox-group label:hover {
            background-color: #f8f9fa;
        }

        #availableRoomsMessage {
            margin-top: 10px;
            font-weight: bold;
        }

        .checkbox-group input[type="checkbox"]:disabled + span {
            color: #999;
        }

      .recent-bookings-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px; /* Reduced from 25px */
    margin-bottom: 20px; /* Reduced from 30px */
    box-shadow: 0 8px 20px rgba(0,0,0,0.1); /* Slightly reduced shadow */
    color: white;
    max-width: 1200px; /* Add max-width constraint */
    margin-left: auto;
    margin-right: auto;
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px; /* Reduced from 20px */
    border-bottom: 2px solid rgba(255,255,255,0.2);
    padding-bottom: 10px; /* Reduced from 15px */
}

.widget-title {
    font-size: 1.3rem; /* Reduced from 1.5rem */
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px; /* Reduced from 10px */
}


        .refresh-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

       .booking-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px; /* Reduced from 12px */
    padding: 15px; /* Reduced from 20px */
    margin-bottom: 10px; /* Reduced from 15px */
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            background: rgba(255,255,255,0.15);
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--status-color, #28a745);
        }

        .booking-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px; /* Reduced from 15px */
}

       .function-type {
    font-weight: 600;
    font-size: 1rem; /* Reduced from 1.1rem */
    margin-bottom: 4px; /* Reduced from 5px */
}
        .booking-time {
    font-size: 0.8rem; /* Reduced from 0.85rem */
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 4px; /* Reduced from 5px */
}

        .status-badge {
    padding: 3px 8px; /* Reduced from 4px 12px */
    border-radius: 15px; /* Reduced from 20px */
    font-size: 0.7rem; /* Reduced from 0.75rem */
    font-weight: 500;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
}

        .new-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            animation: pulse 2s infinite;
        }

        .updated-badge {
            background: linear-gradient(45deg, #4ecdc4, #44a08d);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .booking-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px; /* Reduced from 15px */
    margin-bottom: 10px; /* Reduced from 15px */
}

       .detail-item {
    display: flex;
    align-items: center;
    gap: 6px; /* Reduced from 8px */
    font-size: 0.85rem; /* Reduced from 0.9rem */
}
        .detail-icon {
            width: 16px;
            opacity: 0.8;
        }

       .booking-actions {
    display: flex;
    gap: 8px; /* Reduced from 10px */
    justify-content: flex-end;
    margin-top: 10px; /* Reduced from 15px */
    padding-top: 10px; /* Reduced from 15px */
    border-top: 1px solid rgba(255,255,255,0.2);
}

        .action-btn {
    padding: 4px 8px; /* Reduced from 6px 12px */
    border: none;
    border-radius: 4px; /* Reduced from 6px */
    font-size: 0.75rem; /* Reduced from 0.8rem */
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.2);
    color: white;
}

        .action-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .no-bookings {
            text-align: center;
            padding: 40px 20px;
            opacity: 0.7;
        }

        .no-bookings i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .view-all-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .view-all-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Time slot modal styles */
        .time-slot-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .time-slot-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .time-slot-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .time-slot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .time-slot-title {
            font-weight: 600;
            color: #495057;
            margin: 0;
        }

        .availability-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .available {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .limited {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .unavailable {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .collapsed .collapse-content {
            display: none;
        }

        .collapse-toggle {
            background: none;
            border: none;
            color: #007bff;
            font-size: 0.9rem;
            padding: 0;
            margin-left: 10px;
        }

        .collapse-toggle:hover {
            text-decoration: underline;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 8px;
            margin-top: 15px;
        }

        .room-badge {
            padding: 8px 4px;
            text-align: center;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .room-available {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1976d2;
        }

        .room-booked {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #d32f2f;
            opacity: 0.6;
        }

        .availability-summary {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        /* Day filter styles */
       .day-filter {
    font-size: 0.7rem; /* Reduced from 0.75rem */
    padding: 3px 6px; /* Reduced from 4px 8px */
    background: rgba(255,255,255,0.1) !important;
    border-color: rgba(255,255,255,0.3) !important;
    color: white !important;
    transition: all 0.3s ease;
}

        .day-filter.active {
            background: rgba(255,255,255,0.3) !important;
            border-color: rgba(255,255,255,0.5) !important;
            font-weight: 600;
        }

        .day-filter:hover {
            background: rgba(255,255,255,0.2) !important;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .widget-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .rooms-grid {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
                gap: 6px;
            }
            
            .room-badge {
                padding: 6px 2px;
                font-size: 0.75rem;
            }
        }

        /* ADD these missing CSS rules to your existing styles */

/* Limit the height of the recent bookings content */
#recentBookingsContent {
    max-height: 400px; /* Add height constraint */
    overflow-y: auto; /* Add scrollbar if needed */
}

/* Customize scrollbar for webkit browsers */
#recentBookingsContent::-webkit-scrollbar {
    width: 6px;
}

#recentBookingsContent::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
}

#recentBookingsContent::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

#recentBookingsContent::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Make refresh button smaller */
.refresh-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 6px 10px; /* Reduced from 8px 12px */
    border-radius: 6px; /* Reduced from 8px */
    transition: all 0.3s ease;
    font-size: 0.8rem; /* Make text smaller */
}

/* Show More button styling */
#showMoreBtn {
    background: rgba(255,255,255,0.15) !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
    color: white !important;
    transition: all 0.3s ease;
}

#showMoreBtn:hover {
    background: rgba(255,255,255,0.25) !important;
    transform: translateY(-1px);
}

/* Mobile responsiveness adjustments for recent bookings */
@media (max-width: 768px) {
    .recent-bookings-container {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .booking-card {
        padding: 12px;
        margin-bottom: 8px;
    }
    
    .widget-title {
        font-size: 1.1rem;
    }
    
    #recentBookingsContent {
        max-height: 300px; /* Smaller on mobile */
    }
    
    .day-filter {
        font-size: 0.7rem;
        padding: 3px 6px;
    }
}
    </style>
</head>

<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="my-3">Booking Calendar</h1>
        <div class="button-group">
            <a href="/" class="btn btn-primary me-2">
                <i class="fas fa-home me-1"></i> Home
            </a>
            <a href="{{ route('room.visualizer') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-1"></i> Room Availability
            </a>
            <a href="{{ route('food-menu.index') }}" class="btn btn-info">
                <i class="fas fa-utensils me-1"></i> Food Menus
            </a>
        </div>
    </div>
</div>

<!-- Recent Bookings Widget -->
<div class="container-fluid">
    <div class="recent-bookings-container">
        <div class="widget-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h2 class="widget-title mb-0">
                    <i class="fas fa-clock"></i>
                    Recent Bookings
                </h2>
                <div class="d-flex gap-2 align-items-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-light day-filter" data-days="1">
                            Today
                        </button>
                        <button type="button" class="btn btn-outline-light day-filter active" data-days="7">
                            7d
                        </button>
                        <button type="button" class="btn btn-outline-light day-filter" data-days="30">
                            30d
                        </button>
                    </div>
                    <button class="refresh-btn" onclick="loadRecentBookings()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <div id="recentBookingsContent">
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="#logTableBody" class="view-all-btn">
                <i class="fas fa-list"></i>
                View All Logs
            </a>
        </div>
    </div>
</div>

<!-- Booking Form -->
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
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="bill_number" class="form-label">Bill Number:</label>
                <input type="text" id="bill_number" name="bill_number" class="form-control" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="advance_date" class="form-label">Advance Date:</label>
                <input type="date" id="advance_date" name="advance_date" class="form-control" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method:</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="">Select Payment Method</option>
                    <option value="online">Online</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3" id="roomNumbersSection" style="display: none;">
        <label for="room_number" class="form-label">Room Numbers:</label>
        <div class="checkbox-group" id="roomCheckboxes">
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
        <div id="availableRoomsMessage" class="text-info mt-2"></div>
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

<!-- Calendar -->
<div class="calendar-container">
    <div id="calendar"></div>
</div>

<!-- Enhanced Modal for Available Rooms -->
<div id="availableRoomsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-door-open me-2"></i>
                    Available Rooms by Time Slots
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4 p-3 bg-primary text-white rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Selected Date</h6>
                            <p class="mb-0" id="selectedDate">-</p>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
                
                <div id="timeSlotsContainer">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="useSelectedDateInForm()" id="useInFormBtn" style="display: none;">
                    <i class="fas fa-check me-1"></i> Use Selected Time Slot
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Log Details -->
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Calendar Log Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Function Type</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>User</th>
                            <th>Advance Payment</th>
                            <th>Guest Count</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <!-- Data will be populated dynamically -->
                    </tbody>
                </table>
                <nav>
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be populated dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="booking-info mb-4">
                    <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                    <div id="modalBody">
                        <!-- Basic booking details will be injected here -->
                    </div>
                    <button type="button" id="viewFoodMenu" class="btn btn-success">
                        <i class="fas fa-utensils me-1"></i> View Food Menu
                    </button>
                </div>
                
                <div class="payment-history">
                    <h6 class="border-bottom pb-2 mb-3">Payment History</h6>
                    <div id="paymentHistoryBody">
                        <!-- Payment history will be injected here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="editEvent" class="btn btn-primary">Edit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="printConfirmation" class="btn btn-info">Print Confirmation</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
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
                        <div class="form-check">
                            <input type="checkbox" id="updateDateTime" class="form-check-input">
                            <label class="form-check-label" for="updateDateTime">Change Date & Time</label>
                        </div>
                    </div>

                    <div id="dateTimeFields" style="display: none;">
                        <div class="mb-3">
                            <label for="editStart" class="form-label">Start Date & Time:</label>
                            <input type="datetime-local" id="editStart" name="start" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editEnd" class="form-label">End Date & Time:</label>
                            <input type="datetime-local" id="editEnd" name="end" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editContactNumber" class="form-label">Contact Number:</label>
                        <input type="text" id="editContactNumber" name="contact_number" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="updatePayment" class="form-check-input">
                            <label class="form-check-label" for="updatePayment">Add New Payment</label>
                        </div>
                    </div>

                    <div id="paymentFields" style="display: none;">
                        <div class="mb-3">
                            <label for="editAdvancePayment" class="form-label">Advance Payment:</label>
                            <input type="text" id="editAdvancePayment" name="advance_payment" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editBillNumber" class="form-label">Bill Number:</label>
                                    <input type="text" id="editBillNumber" name="bill_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editAdvanceDate" class="form-label">Advance Date:</label>
                                    <input type="date" id="editAdvanceDate" name="advance_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editPaymentMethod" class="form-label">Payment Method:</label>
                                    <select id="editPaymentMethod" name="payment_method" class="form-control">
                                        <option value="">Select Payment Method</option>
                                        <option value="online">Online</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                        </div>
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
// Global Variables
const functionTypeColors = {
    "Wedding": "#ff5733",
    "Night In Group": "#33ff57",
    "Day Out": "#3375ff",
    "Couple Package": "#ff33b8",
    "Room Only": "#33f8ff",
};

let currentPage = 1;
const rowsPerPage = 15;
let totalLogs = [];
let currentDays = 30;
let currentLimit = 5;
let calendar;
let selectedTimeSlotData = null;

// All available rooms
const allRooms = [
    'Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa',
    'Mayura', 'Lihini', '121', '122', '123', '124', '106', '107', '108',
    '109', 'CH Room', '130', '131', '132', '133', '134', '101', '102', 
    '103', '104', '105'
];

// Success/Error Message Functions
function showSuccessMessage(message) {
    showMessage(message, 'success');
}

function showErrorMessage(message) {
    showMessage(message, 'danger');
}

function showMessage(message, type) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.minWidth = '300px';
    alert.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Time Slot Functions
function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function generateTimeSlots(selectedDate) {
    const today = new Date(selectedDate);
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const formatDateTime = (date, hour) => {
        const newDate = new Date(date);
        newDate.setHours(hour, 0, 0, 0);
        return newDate.toISOString();
    };

    return [
        {
            id: 'yesterday10-today10',
            title: 'Yesterday 10 AM → Today 10 AM',
            description: '24-hour slot ending this morning',
            icon: 'fas fa-moon',
            startDateTime: formatDateTime(yesterday, 10),
            endDateTime: formatDateTime(today, 10),
            displayStart: yesterday.toLocaleDateString() + ' 10:00 AM',
            displayEnd: today.toLocaleDateString() + ' 10:00 AM'
        },
        {
            id: 'yesterday3-today3',
            title: 'Yesterday 3 PM → Today 3 PM',
            description: '24-hour slot ending this afternoon',
            icon: 'fas fa-sun',
            startDateTime: formatDateTime(yesterday, 15),
            endDateTime: formatDateTime(today, 15),
            displayStart: yesterday.toLocaleDateString() + ' 3:00 PM',
            displayEnd: today.toLocaleDateString() + ' 3:00 PM'
        },
        {
            id: 'today10-tomorrow10',
            title: 'Today 10 AM → Tomorrow 10 AM',
            description: '24-hour slot starting this morning',
            icon: 'fas fa-sunrise',
            startDateTime: formatDateTime(today, 10),
            endDateTime: formatDateTime(tomorrow, 10),
            displayStart: today.toLocaleDateString() + ' 10:00 AM',
            displayEnd: tomorrow.toLocaleDateString() + ' 10:00 AM'
        },
        {
            id: 'today3-tomorrow3',
            title: 'Today 3 PM → Tomorrow 3 PM',
            description: '24-hour slot starting this afternoon',
            icon: 'fas fa-sunset',
            startDateTime: formatDateTime(today, 15),
            endDateTime: formatDateTime(tomorrow, 15),
            displayStart: today.toLocaleDateString() + ' 3:00 PM',
            displayEnd: tomorrow.toLocaleDateString() + ' 3:00 PM'
        }
    ];
}

async function fetchAvailableRooms(startDateTime, endDateTime) {
    try {
        const response = await fetch(`/available-rooms?date=${startDateTime}&endDate=${endDateTime}`);
        if (!response.ok) throw new Error('Failed to fetch room availability');
        return await response.json();
    } catch (error) {
        console.error('Error fetching available rooms:', error);
        return [];
    }
}

function getAvailabilityStatus(availableCount, totalCount) {
    const percentage = (availableCount / totalCount) * 100;
    if (percentage >= 80) return { class: 'available', text: 'Good Availability' };
    if (percentage >= 50) return { class: 'limited', text: 'Limited Availability' };
    return { class: 'unavailable', text: 'Low Availability' };
}

function createRoomGrid(availableRooms) {
    return allRooms.map(room => {
        const isAvailable = availableRooms.includes(room);
        return `
            <div class="room-badge ${isAvailable ? 'room-available' : 'room-booked'}">
                ${room}
            </div>
        `;
    }).join('');
}

function createTimeSlotCard(slot, availableRooms, isCollapsed = true) {
    const allRoomsCount = allRooms.length;
    const availabilityStatus = getAvailabilityStatus(availableRooms.length, allRoomsCount);
    
    return `
        <div class="time-slot-card ${isCollapsed ? 'collapsed' : ''}" data-slot-id="${slot.id}" onclick="toggleTimeSlot('${slot.id}', '${slot.startDateTime}', '${slot.endDateTime}')">
            <div class="time-slot-header">
                <div class="d-flex align-items-center">
                    <i class="${slot.icon} me-2 text-primary"></i>
                    <h6 class="time-slot-title">${slot.title}</h6>
                    <button class="collapse-toggle ms-2" onclick="event.stopPropagation(); toggleCollapse('${slot.id}')">
                        <i class="fas fa-chevron-${isCollapsed ? 'down' : 'up'}"></i>
                        ${isCollapsed ? 'Show' : 'Hide'} Details
                    </button>
                </div>
                <span class="availability-badge ${availabilityStatus.class}">
                    ${availableRooms.length}/${allRoomsCount} ${availabilityStatus.text}
                </span>
            </div>
            
            <div class="collapse-content" ${isCollapsed ? 'style="display: none;"' : ''}>
                <p class="text-muted mb-2">${slot.description}</p>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    ${slot.displayStart} → ${slot.displayEnd}
                </small>
                
                <div class="availability-summary">
                    <strong>${availableRooms.length} rooms available</strong> out of ${allRoomsCount} total rooms
                </div>
                
                <div class="rooms-grid">
                    ${createRoomGrid(availableRooms)}
                </div>
            </div>
        </div>
    `;
}

function toggleTimeSlot(slotId, startDateTime, endDateTime) {
    document.querySelectorAll('.time-slot-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const selectedCard = document.querySelector(`[data-slot-id="${slotId}"]`);
    selectedCard.classList.add('selected');
    
    selectedTimeSlotData = {
        slotId: slotId,
        startDateTime: startDateTime,
        endDateTime: endDateTime,
        startDate: startDateTime.split('T')[0],
        endDate: endDateTime.split('T')[0],
        startTime: new Date(startDateTime).toTimeString().slice(0, 5),
        endTime: new Date(endDateTime).toTimeString().slice(0, 5)
    };
    
    document.getElementById('useInFormBtn').style.display = 'inline-block';
}

function toggleCollapse(slotId) {
    const card = document.querySelector(`[data-slot-id="${slotId}"]`);
    const content = card.querySelector('.collapse-content');
    const button = card.querySelector('.collapse-toggle');
    const icon = button.querySelector('i');
    
    if (card.classList.contains('collapsed')) {
        card.classList.remove('collapsed');
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Details';
    } else {
        card.classList.add('collapsed');
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Details';
    }
}

function useSelectedDateInForm() {
    if (selectedTimeSlotData) {
        document.getElementById('date').value = selectedTimeSlotData.startDate;
        document.getElementById('start_time').value = selectedTimeSlotData.startTime;
        document.getElementById('end_date').value = selectedTimeSlotData.endDate;
        document.getElementById('end_time').value = selectedTimeSlotData.endTime;
        
        if (typeof checkAvailability === 'function') {
            checkAvailability();
        }
        
        showSuccessMessage('Selected time slot has been applied to the booking form.');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('availableRoomsModal'));
        modal.hide();
        
        const bookingForm = document.getElementById('booking-form');
        bookingForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Calendar Functions
function getCalendarConfig() {
    return {
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
        },
        events: "/bookings",
        dateClick: handleDateClick,
        eventContent: formatEventContent,
        eventDidMount: handleEventDidMount,
        eventClick: handleEventClick
    };
}

function handleEventDidMount(info) {
    const functionType = info.event.extendedProps.function_type;
    const color = functionTypeColors[functionType] || "#6c757d";
    info.el.style.backgroundColor = color;
    info.el.style.borderColor = color;
    info.el.style.color = "#fff";
}

function formatEventContent(info) {
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
}

async function handleDateClick(info) {
    const selectedDate = info.dateStr;
    
    document.getElementById('selectedDate').textContent = formatDateForDisplay(selectedDate);
    
    const modal = new bootstrap.Modal(document.getElementById('availableRoomsModal'));
    modal.show();
    
    const timeSlots = generateTimeSlots(selectedDate);
    const container = document.getElementById('timeSlotsContainer');
    
    try {
        container.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        `;
        
        const availabilityPromises = timeSlots.map(async (slot) => {
            const availableRooms = await fetchAvailableRooms(slot.startDateTime, slot.endDateTime);
            return { slot, availableRooms };
        });
        
        const results = await Promise.all(availabilityPromises);
        
        const timeSlotsHtml = results.map(({ slot, availableRooms }, index) => {
            return createTimeSlotCard(slot, availableRooms, index > 0);
        }).join('');
        
        container.innerHTML = timeSlotsHtml;
        
    } catch (error) {
        console.error('Error loading room availability:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading room availability. Please try again.
            </div>
        `;
    }
}

// Event Click Handler
function handleEventClick(info) {
    const event = info.event;
    const props = event.extendedProps;

    document.getElementById("printConfirmation").onclick = function() {
        const bookingId = info.event.id;
        window.open(`/bookings/${bookingId}/print`, '_blank');
    };

    document.getElementById("viewFoodMenu").onclick = function() {
        const bookingId = info.event.id;
        const bookingDate = new Date(info.event.start).toISOString().split('T')[0];
        window.open(`/food-menu?date=${bookingDate}&booking_id=${bookingId}`, '_blank');
    };

    document.getElementById('modalTitle').textContent = `Booking Details - ${event.title}`;
    document.getElementById('modalBody').innerHTML = `
        <p><strong>Function Type:</strong> ${props.function_type || 'N/A'}</p>
        <p><strong>Contact Number:</strong> ${props.contact_number || 'N/A'}</p>
        <p><strong>Room Numbers:</strong> ${props.room_numbers || 'N/A'}</p>
        <p><strong>Guest Count:</strong> ${props.guest_count || 'N/A'}</p>
        <p><strong>Description:</strong> ${props.name || 'N/A'}</p>
        <p><strong>Start Time:</strong> ${new Date(event.start).toLocaleString()}</p>
        <p><strong>End Time:</strong> ${event.end ? new Date(event.end).toLocaleString() : 'N/A'}</p>
    `;

    handlePaymentHistory(props);

    const modal = new bootstrap.Modal(document.getElementById("eventModal"));
    modal.show();

    setupEditHandler(info);
}

// Payment History Handler
function handlePaymentHistory(props) {
    const payments = props.advancePayments || [];
    const paymentHistoryBody = document.getElementById("paymentHistoryBody");
    
    if (payments.length > 0 || props.advance_payment) {
        const paymentData = payments.length > 0 ? payments : [{
            id: null,
            amount: props.advance_payment,
            billNumber: props.bill_number || 'N/A',
            date: props.advance_date || new Date().toISOString(),
            method: props.payment_method || 'N/A',
            isVerified: false,
            verifiedAt: null,
            verifiedBy: null
        }];
        
        paymentHistoryBody.innerHTML = '';
        
        const userRole = document.querySelector('meta[name="user-role"]')?.content;
        const isAdmin = userRole === 'admin';
        
        paymentData.forEach((payment, index) => {
            const paymentRecord = document.createElement('div');
            paymentRecord.className = 'payment-record';
            
            const header = document.createElement('div');
            header.className = 'form-check d-flex align-items-center justify-content-between';
            
            const headerLeft = document.createElement('div');
            headerLeft.className = 'd-flex align-items-center';
            
            if (isAdmin && !payment.isVerified && payment.id) {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input payment-checkbox';
                checkbox.id = `payment-${payment.id}`;
                checkbox.addEventListener('change', function() {
                    togglePaymentVerification(payment.id);
                });
                
                headerLeft.appendChild(checkbox);
            }
            
            const title = document.createElement('h6');
            title.className = 'mb-0 ms-2';
            title.textContent = `Payment #${index + 1}`;
            headerLeft.appendChild(title);
            
            header.appendChild(headerLeft);
            
            if (payment.isVerified) {
                const verificationDiv = document.createElement('div');
                verificationDiv.className = 'text-success ms-2 d-flex align-items-center';
                
                const badge = document.createElement('span');
                badge.className = 'badge bg-success me-2';
                badge.textContent = 'Verified';
                
                const verificationInfo = document.createElement('small');
                verificationInfo.className = 'text-muted';
                const verifiedDate = payment.verifiedAt ? new Date(payment.verifiedAt).toLocaleDateString() : 'N/A';
                verificationInfo.textContent = `on ${verifiedDate} by ${payment.verifiedBy || 'Admin'}`;
                
                verificationDiv.appendChild(badge);
                verificationDiv.appendChild(verificationInfo);
                header.appendChild(verificationDiv);
            }
            
            paymentRecord.appendChild(header);
            
            const details = document.createElement('div');
            details.className = 'payment-details mt-2';
            
            const amount = document.createElement('p');
            amount.className = 'mb-1';
            amount.textContent = `Amount: Rs. ${parseFloat(payment.amount).toFixed(2)}`;
            
            const billNumber = document.createElement('p');
            billNumber.className = 'mb-1';
            billNumber.textContent = `Bill Number: ${payment.billNumber}`;
            
            const date = document.createElement('p');
            date.className = 'mb-1';
            date.textContent = `Date: ${new Date(payment.date).toLocaleDateString()}`;
            
            const method = document.createElement('p');
            method.className = 'mb-1';
            method.textContent = `Method: ${payment.method}`;
            
            details.appendChild(amount);
            details.appendChild(billNumber);
            details.appendChild(date);
            details.appendChild(method);
            
            paymentRecord.appendChild(details);
            paymentHistoryBody.appendChild(paymentRecord);
        });
    } else {
        paymentHistoryBody.innerHTML = '<p>No payment history available</p>';
    }
}

function togglePaymentVerification(paymentId) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const checkbox = document.getElementById(`payment-${paymentId}`);
    
    if (checkbox) {
        checkbox.disabled = true;
    }
    
    fetch(`/booking-payments/${paymentId}/toggle-verification`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage("Payment verification updated successfully!");
            
            // Update the UI without page reload
            if (checkbox) {
                const paymentRecord = checkbox.closest('.payment-record');
                if (data.payment.is_verified) {
                    // Add verification badge
                    const header = paymentRecord.querySelector('.form-check');
                    const verificationDiv = document.createElement('div');
                    verificationDiv.className = 'text-success ms-2 d-flex align-items-center';
                    verificationDiv.innerHTML = `
                        <span class="badge bg-success me-2">Verified</span>
                        <small class="text-muted">on ${new Date(data.payment.verified_at).toLocaleDateString()} by ${data.payment.verified_by}</small>
                    `;
                    header.appendChild(verificationDiv);
                    checkbox.style.display = 'none';
                } else {
                    // Remove verification badge
                    const verificationDiv = paymentRecord.querySelector('.text-success');
                    if (verificationDiv) verificationDiv.remove();
                    checkbox.style.display = 'inline-block';
                    checkbox.disabled = false;
                }
            }
            
        } else {
            showErrorMessage(data.message || 'An error occurred');
            if (checkbox) {
                checkbox.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Failed to update verification status');
        if (checkbox) {
            checkbox.disabled = false;
        }
    });
}

// Edit Functions
function setupEditHandler(info) {
    document.getElementById("editEvent").onclick = function() {
        populateEditForm(info);
        const editModal = new bootstrap.Modal(document.getElementById("editModal"));
        editModal.show();
    };

    document.getElementById('updatePayment').addEventListener('change', function() {
        const paymentFields = document.getElementById('paymentFields');
        paymentFields.style.display = this.checked ? 'block' : 'none';
        
        const fields = paymentFields.querySelectorAll('input, select');
        fields.forEach(field => {
            field.required = this.checked;
        });
    });

    document.getElementById('updateDateTime').addEventListener('change', function() {
        const dateTimeFields = document.getElementById('dateTimeFields');
        dateTimeFields.style.display = this.checked ? 'block' : 'none';
        
        const fields = dateTimeFields.querySelectorAll('input');
        fields.forEach(field => {
            field.required = this.checked;
        });
    });

    setupEditFormSubmission(info);
}

async function checkRoomAvailabilityForEdit(startDate, endDate, currentBookingId) {
    try {
        const response = await fetch(`/available-rooms?date=${startDate}&endDate=${endDate}&excludeBooking=${currentBookingId}`);
        if (!response.ok) throw new Error('Failed to fetch room availability');
        return await response.json();
    } catch (error) {
        console.error('Error checking room availability:', error);
        return [];
    }
}

async function populateEditForm(info) {
    document.getElementById("editFunctionType").value = info.event.extendedProps.function_type;
    document.getElementById("editName").value = info.event.extendedProps.name || "";
    document.getElementById("editContactNumber").value = info.event.extendedProps.contact_number;
    document.getElementById("editGuestCount").value = info.event.extendedProps.guest_count;
    
    const startDate = new Date(info.event.start).toISOString();
    const endDate = info.event.end ? new Date(info.event.end).toISOString() : startDate;

    const editStart = document.getElementById("editStart");
    const editEnd = document.getElementById("editEnd");
    if (editStart) editStart.value = new Date(info.event.start).toISOString().slice(0, 16);
    if (editEnd) editEnd.value = info.event.end ? new Date(info.event.end).toISOString().slice(0, 16) : '';

    const availableRooms = await checkRoomAvailabilityForEdit(startDate, endDate, info.event.id);
    const roomCheckboxes = document.querySelectorAll("#editRoomNumbers input[type='checkbox']");
    
    const selectedRooms = info.event.extendedProps.room_numbers 
        ? JSON.parse(info.event.extendedProps.room_numbers) 
        : [];

    roomCheckboxes.forEach(checkbox => {
        const label = checkbox.parentElement;
        checkbox.checked = false;
        checkbox.disabled = false;
        label.style.backgroundColor = '';
        label.style.borderColor = '';
        label.style.opacity = '1';
    });

    roomCheckboxes.forEach(checkbox => {
        const label = checkbox.parentElement;
        
        if (!availableRooms.includes(checkbox.value) && !selectedRooms.includes(checkbox.value)) {
            label.style.backgroundColor = '#ffebee';
            label.style.borderColor = '#ffcdd2';
            label.title = 'This room is booked by another reservation';
        }

        if (selectedRooms.includes(checkbox.value)) {
            checkbox.checked = true;
            label.style.backgroundColor = '#e3f2fd';
        }
    });
}

function setupEditFormSubmission(info) {
    document.getElementById("editForm").onsubmit = async function(e) {
        e.preventDefault();

        const addNewPayment = document.getElementById('updatePayment').checked;
        const updateDateTime = document.getElementById('updateDateTime').checked;

        const updatedData = {
            name: document.getElementById("editName").value,
            function_type: document.getElementById("editFunctionType").value,
            contact_number: document.getElementById("editContactNumber").value,
            guest_count: document.getElementById("editGuestCount").value,
            room_numbers: Array.from(document.querySelectorAll("#editRoomNumbers input:checked"))
                .map(checkbox => checkbox.value)
        };

        if (updateDateTime) {
            updatedData.start = document.getElementById("editStart").value;
            updatedData.end = document.getElementById("editEnd").value;
        }

        if (addNewPayment) {
            updatedData.advance_payment = document.getElementById("editAdvancePayment").value;
            updatedData.bill_number = document.getElementById("editBillNumber").value;
            updatedData.advance_date = document.getElementById("editAdvanceDate").value;
            updatedData.payment_method = document.getElementById("editPaymentMethod").value;
        }

        try {
            await axios.put(`/bookings/${info.event.id}`, updatedData, {
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
            });

            showSuccessMessage("Booking updated successfully!");
            
            // Close edit modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
            if (editModal) editModal.hide();
            
            // Close event modal
            const eventModal = bootstrap.Modal.getInstance(document.getElementById("eventModal"));
            if (eventModal) eventModal.hide();
            
            // Refresh calendar and recent bookings without page reload
            await Promise.all([
                calendar.refetchEvents(),
                loadRecentBookings(),
                loadLogDetails()
            ]);

        } catch (error) {
            console.error("Error updating booking:", error.response || error.message);
            showErrorMessage("Failed to update booking. Please try again.");
        }
    };
}

// Room Availability Check for Form
async function checkAvailability() {
    const startDate = document.getElementById('date');
    const startTime = document.getElementById('start_time');
    const endDate = document.getElementById('end_date');
    const endTime = document.getElementById('end_time');
    const roomSection = document.getElementById('roomNumbersSection');
    const roomCheckboxes = document.querySelectorAll('input[name="room_number[]"]');
    const messageDiv = document.getElementById('availableRoomsMessage');

    if (!startDate.value || !startTime.value) {
        roomSection.style.display = 'none';
        return;
    }

    try {
        const start = `${startDate.value}T${startTime.value}`;
        const end = (endDate.value && endTime.value) ? 
                   `${endDate.value}T${endTime.value}` : 
                   start;

        const response = await fetch(`/available-rooms?date=${start}&endDate=${end}`);
        const availableRooms = await response.json();

        roomSection.style.display = 'block';

        roomCheckboxes.forEach(checkbox => {
            const isAvailable = availableRooms.includes(checkbox.value);
            checkbox.disabled = !isAvailable;
            checkbox.checked = false;
            const label = checkbox.parentElement;
            label.style.opacity = isAvailable ? '1' : '0.5';
        });

        messageDiv.textContent = `${availableRooms.length} rooms available for selected dates`;
        messageDiv.style.color = availableRooms.length > 0 ? 'green' : 'red';

    } catch (error) {
        console.error('Error checking room availability:', error);
        messageDiv.textContent = 'Error checking room availability';
        messageDiv.style.color = 'red';
    }
}

// Log Functions
async function loadLogDetails() {
    try {
        const response = await axios.get('/booking-logs');
        totalLogs = response.data;
        displayPage(currentPage);
        setupPagination();
    } catch (error) {
        console.error('Error loading log details:', error);
    }
}

function displayPage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = totalLogs.slice(start, end);
    const tableBody = document.getElementById('logTableBody');
    tableBody.innerHTML = '';

    pageData.forEach(log => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${log.function_type}</td>
            <td>${new Date(log.created_at).toLocaleString()}</td>
            <td>${new Date(log.updated_at).toLocaleString()}</td>
            <td>${log.user_name || 'N/A'}</td>
            <td>Rs. ${parseFloat(log.advance_payment).toFixed(2)}</td>
            <td>${log.guest_count}</td>
            <td>${new Date(log.start).toLocaleString()}</td>
            <td>${log.end ? new Date(log.end).toLocaleString() : 'N/A'}</td>
            <td>${getStatusBadge(log.created_at, log.updated_at)}</td>
        `;
        tableBody.appendChild(row);
    });
}

function setupPagination() {
    const totalPages = Math.ceil(totalLogs.length / rowsPerPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }

    pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
        </li>
    `;
}

function changePage(page) {
    if (page < 1 || page > Math.ceil(totalLogs.length / rowsPerPage)) return;
    currentPage = page;
    displayPage(currentPage);
    setupPagination();
}

function getStatusBadge(created, updated) {
    if (created === updated) {
        return '<span class="badge bg-success">Created</span>';
    }
    return '<span class="badge bg-warning">Updated</span>';
}

// Recent Bookings Functions
async function loadRecentBookings() {
    const container = document.getElementById('recentBookingsContent');
    
    container.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
    `;

    try {
        const response = await axios.get('/bookings/recent', {
            params: {
                limit: currentLimit,
                days: currentDays
            }
        });
        const bookings = response.data;
        
        if (bookings.length === 0) {
            const timeText = currentDays === 1 ? 'today' : 
                           currentDays === 7 ? 'last 7 days' : 
                           currentDays === 30 ? 'last 30 days' : `last ${currentDays} days`;
            container.innerHTML = `
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h4>No Recent Bookings</h4>
                    <p>No bookings found in the ${timeText}.</p>
                </div>
            `;
            return;
        }

        const hasMoreBookings = bookings.length === currentLimit;
        let bookingsHtml = bookings.map(booking => createBookingCard(booking)).join('');
        
        if (hasMoreBookings && currentLimit < 20) {
            bookingsHtml += `
                <div class="text-center mt-3">
                    <button class="btn btn-outline-light btn-sm" onclick="showMoreBookings()" id="showMoreBtn">
                        <i class="fas fa-chevron-down me-1"></i>
                        Show More (${currentLimit + 5} total)
                    </button>
                </div>
            `;
        }

        container.innerHTML = bookingsHtml;
        
    } catch (error) {
        console.error('Error loading recent bookings:', error);
        container.innerHTML = `
            <div class="no-bookings">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Error Loading Bookings</h4>
                <p>Please try refreshing the page.</p>
            </div>
        `;
    }
}

function showMoreBookings() {
    currentLimit += 5;
    loadRecentBookings();
}

function refreshBookings() {
    currentLimit = 5;
    loadRecentBookings();
}

function createBookingCard(booking) {
    const statusColor = functionTypeColors[booking.function_type] || "#6c757d";
    const statusBadge = booking.isNew ? 'new-badge' : (booking.isUpdated ? 'updated-badge' : '');
    const statusText = booking.isNew ? 'NEW' : (booking.isUpdated ? 'UPDATED' : 'ACTIVE');
    
    return `
        <div class="booking-card" style="--status-color: ${statusColor}">
            <div class="booking-header">
                <div>
                    <div class="function-type">${booking.function_type}</div>
                    <div class="booking-time">
                        <i class="fas fa-clock detail-icon"></i>
                        ${booking.time_ago}
                    </div>
                </div>
                <span class="status-badge ${statusBadge}">${statusText}</span>
            </div>
            
            <div class="booking-details">
                <div class="detail-item">
                    <i class="fas fa-user detail-icon"></i>
                    <span>${booking.user_name}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-phone detail-icon"></i>
                    <span>${booking.contact_number}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-users detail-icon"></i>
                    <span>${booking.guest_count}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-door-open detail-icon"></i>
                    <span>${Array.isArray(booking.room_numbers) ? booking.room_numbers.join(', ') : booking.room_numbers}</span>
                </div>
            </div>
            
            <div class="detail-item" style="margin-bottom: 10px;">
                <i class="fas fa-calendar detail-icon"></i>
                <span>${booking.formatted_start}${booking.formatted_end ? ' - ' + booking.formatted_end : ''}</span>
            </div>
            
            <div class="detail-item" style="margin-bottom: 15px;">
                <i class="fas fa-money-bill detail-icon"></i>
                <span>Rs. ${parseInt(booking.advance_payment).toLocaleString()}</span>
            </div>
            
            <div style="font-size: 0.9rem; opacity: 0.9; line-height: 1.4;">
                ${booking.name}
            </div>
            
            <div class="booking-actions">
                <button class="action-btn" onclick="viewBookingFromWidget(${booking.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn" onclick="editBookingFromWidget(${booking.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>
        </div>
    `;
}

function viewBookingFromWidget(bookingId) {
    const events = calendar.getEvents();
    const event = events.find(e => e.id == bookingId);
    
    if (event) {
        handleEventClick({ event: event });
    } else {
        showErrorMessage('Booking details not found in calendar. Please refresh the page.');
    }
}

function editBookingFromWidget(bookingId) {
    const events = calendar.getEvents();
    const event = events.find(e => e.id == bookingId);
    
    if (event) {
        handleEventClick({ event: event });
        setTimeout(() => {
            document.getElementById('editEvent').click();
        }, 500);
    } else {
        showErrorMessage('Booking not found in calendar. Please refresh the page.');
    }
}

// Manual Refresh Function
function manualRefresh() {
    showMessage('Refreshing calendar data...', 'info');
    
    Promise.all([
        calendar.refetchEvents(),
        loadRecentBookings(),
        loadLogDetails()
    ]).then(() => {
        showSuccessMessage('Calendar data refreshed successfully!');
    }).catch(error => {
        console.error('Refresh error:', error);
        showErrorMessage('Failed to refresh calendar data');
    });
}

// Form Submission - Updated to avoid page reload
document.getElementById("booking-form").addEventListener("submit", async function (e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton.disabled) {
        return;
    }
    
    submitButton.disabled = true;
    const originalText = submitButton.textContent;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
    
    const formData = new FormData(e.target);
    const start = `${formData.get("date")}T${formData.get("start_time")}`;
    const endDate = formData.get("end_date") ? formData.get("end_date") : formData.get("date");
    const end = formData.get("end_time") ? `${endDate}T${formData.get("end_time")}` : null;

    try {
        await axios.post("/bookings", {
            start: start,
            end: end,
            advance_payment: formData.get("advance_payment"),
            bill_number: formData.get("bill_number"),
            advance_date: formData.get("advance_date"),
            payment_method: formData.get("payment_method"),
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

        showSuccessMessage("Booking successful!");
        
        // Reset form
        this.reset();
        document.getElementById('roomNumbersSection').style.display = 'none';
        
        // Update recent bookings to show new booking
        currentDays = 1;
        currentLimit = 5;
        
        const dayFilters = document.querySelectorAll('.day-filter');
        dayFilters.forEach(btn => btn.classList.remove('active'));
        const todayBtn = document.querySelector('[data-days="1"]');
        if (todayBtn) todayBtn.classList.add('active');
        
        // Refresh recent bookings and calendar without page reload
        await Promise.all([
            loadRecentBookings(),
            calendar.refetchEvents(),
            loadLogDetails()
        ]);
        
    } catch (error) {
        console.error("Error making booking:", error.response || error.message);
        
        if (error.response && error.response.status === 409) {
            showErrorMessage("A similar booking was just created. Please check your recent bookings.");
            currentDays = 1;
            currentLimit = 5;
            const dayFilters = document.querySelectorAll('.day-filter');
            dayFilters.forEach(btn => btn.classList.remove('active'));
            const todayBtn = document.querySelector('[data-days="1"]');
            if (todayBtn) todayBtn.classList.add('active');
            loadRecentBookings();
        } else {
            showErrorMessage("Failed to make booking. Please try again.");
        }
    } finally {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    }
});

// Initialize everything when DOM is loaded - UPDATED without auto-refresh
document.addEventListener("DOMContentLoaded", function() {
    // Initialize calendar
    loadLogDetails();
    const calendarEl = document.getElementById("calendar");
    calendar = new FullCalendar.Calendar(calendarEl, getCalendarConfig());
    calendar.render();
    
    // Initialize recent bookings
    loadRecentBookings();
    
    // Set up day filter event listeners
    const dayFilters = document.querySelectorAll('.day-filter');
    dayFilters.forEach(button => {
        button.addEventListener('click', function() {
            dayFilters.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentDays = parseInt(this.dataset.days);
            currentLimit = 5;
            loadRecentBookings();
        });
    });
    
    // Set up form availability checking
    const startDate = document.getElementById('date');
    const startTime = document.getElementById('start_time');
    const endDate = document.getElementById('end_date');
    const endTime = document.getElementById('end_time');
    
    [startDate, startTime, endDate, endTime].forEach(element => {
        if (element) {
            element.addEventListener('change', checkAvailability);
        }
    });
    
    // REMOVED: Auto-refresh interval that was causing page refreshes
    // setInterval(loadRecentBookings, 60000); // This line has been removed
    
    calendar.on('eventsSet', function(events) {
        console.log('Calendar events loaded:', events);
    });
});
</script>
</body>
</html>