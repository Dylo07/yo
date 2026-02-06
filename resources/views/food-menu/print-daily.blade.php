<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Food Menu - {{ $date->format('F j, Y') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        
        .page {
            width: 194mm;
            margin: 0 auto;
            padding: 5mm;
            page-break-after: always;
        }
        
        .page:last-child {
            page-break-after: auto;
        }
        
        /* Header - Simple */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 2mm;
            border-bottom: 2px solid #000;
            margin-bottom: 3mm;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
        }
        
        .header .date {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .wedding-tag {
            background: #000;
            color: #fff;
            padding: 1mm 3mm;
            font-size: 9pt;
            margin-left: 2mm;
        }
        
        /* Info Row - Compact */
        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 2mm;
            margin-bottom: 2mm;
            font-size: 10pt;
            border: 1px solid #000;
            padding: 2mm;
        }
        
        .info-row span {
            margin-right: 4mm;
        }
        
        .info-row strong {
            font-weight: bold;
        }
        
        /* Details Row */
        .details-row {
            display: flex;
            gap: 3mm;
            margin-bottom: 3mm;
            font-size: 10pt;
            border: 1px solid #999;
            padding: 2mm;
            background: #f5f5f5;
        }
        
        .details-row .detail {
            flex: 1;
        }
        
        .details-row .detail-label {
            font-weight: bold;
            font-size: 8pt;
        }
        
        /* Menu Table - Simple Sequential */
        .menu-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        
        .menu-table th {
            background: #333;
            color: #fff;
            padding: 3mm;
            text-align: left;
            font-size: 12pt;
        }
        
        .menu-table td {
            border: 1px solid #999;
            padding: 3mm;
            vertical-align: top;
        }
        
        .menu-table .num {
            width: 10mm;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            background: #eee;
        }
        
        .menu-table .name {
            width: 35mm;
            font-weight: bold;
            font-size: 11pt;
            background: #f9f9f9;
        }
        
        .menu-table .time {
            width: 20mm;
            text-align: center;
            font-size: 10pt;
        }
        
        .menu-table .content {
            font-size: 11pt;
            line-height: 1.4;
        }
        
        /* Footer */
        .footer {
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
        }
        
        /* No bookings message */
        .no-bookings {
            text-align: center;
            padding: 20mm;
            font-size: 14pt;
        }
        
        @media print {
            .page {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    @if($bookingsWithMenus->count() == 0)
    <div class="page">
        <div class="header">
            <h1>DAILY FOOD MENU</h1>
            <div class="date">{{ $date->format('l, F j, Y') }}</div>
        </div>
        <div class="no-bookings">
            No bookings found for this date.
        </div>
    </div>
    @else
    @foreach($bookingsWithMenus as $index => $item)
    @php
        $booking = $item['booking'];
        $menu = $item['menu'];
        $isWedding = $booking->function_type == 'Wedding';
    @endphp
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>FOOD MENU @if($isWedding)<span class="wedding-tag">WEDDING</span>@endif</h1>
            <div class="date">{{ $date->format('l, F j, Y') }}</div>
        </div>
        
        <!-- Info Row -->
        <div class="info-row">
            <span><strong>Function:</strong> {{ $booking->function_type }}</span>
            <span><strong>Guests:</strong> {{ $booking->guest_count }}</span>
            <span><strong>Check In:</strong> {{ $booking->start->format('M j, Y h:i A') }}</span>
            <span><strong>Check Out:</strong> {{ $booking->end ? $booking->end->format('M j, Y h:i A') : 'N/A' }}</span>
            <span><strong>Rooms:</strong> {{ is_array($booking->room_numbers) ? implode(', ', $booking->room_numbers) : $booking->room_numbers }}</span>
        </div>
        
        <!-- Details Row -->
        @if($booking->bites_details || $booking->other_details || $booking->name)
        <div class="details-row">
            <div class="detail">
                <span class="detail-label">Bites:</span> {{ $booking->bites_details ?: 'N/A' }}
            </div>
            <div class="detail">
                <span class="detail-label">Other:</span> {{ $booking->other_details ?: 'N/A' }}
            </div>
            <div class="detail">
                <span class="detail-label">Description:</span> {{ $booking->name ?: 'N/A' }}
            </div>
        </div>
        @endif
        
        @if($isWedding)
        <!-- WEDDING MENU TABLE -->
        <table class="menu-table">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th class="name">Item</th>
                    <th class="content">Menu Details</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="num">1</td><td class="name">Welcome Drink</td><td class="content">{{ $menu->wedding_welcome_drink ?: '-' }}</td></tr>
                <tr><td class="num">2</td><td class="name">Appetizer</td><td class="content">{{ $menu->wedding_appetizer ?: '-' }}</td></tr>
                <tr><td class="num">3</td><td class="name">Shooters</td><td class="content">{{ $menu->wedding_shooters ?: '-' }}</td></tr>
                <tr><td class="num">4</td><td class="name">Salad Bar</td><td class="content">{{ $menu->wedding_salad_bar ?: '-' }}</td></tr>
                <tr><td class="num">5</td><td class="name">Salad Dressing</td><td class="content">{{ $menu->wedding_salad_dressing ?: '-' }}</td></tr>
                <tr><td class="num">6</td><td class="name">Soup</td><td class="content">{{ $menu->wedding_soup ?: '-' }}</td></tr>
                <tr><td class="num">7</td><td class="name">Bread Corner</td><td class="content">{{ $menu->wedding_bread_corner ?: '-' }}</td></tr>
                <tr><td class="num">8</td><td class="name">Rice & Noodle</td><td class="content">{{ $menu->wedding_rice_noodle ?: '-' }}</td></tr>
                <tr><td class="num">9</td><td class="name">Meat Items</td><td class="content">{{ $menu->wedding_meat_items ?: '-' }}</td></tr>
                <tr><td class="num">10</td><td class="name">Seafood Items</td><td class="content">{{ $menu->wedding_seafood_items ?: '-' }}</td></tr>
                <tr><td class="num">11</td><td class="name">Vegetables</td><td class="content">{{ $menu->wedding_vegetables ?: '-' }}</td></tr>
                <tr><td class="num">12</td><td class="name">Condiments</td><td class="content">{{ $menu->wedding_condiments ?: '-' }}</td></tr>
                <tr><td class="num">13</td><td class="name">Desserts</td><td class="content">{{ $menu->wedding_desserts ?: '-' }}</td></tr>
                <tr><td class="num">14</td><td class="name">Beverages</td><td class="content">{{ $menu->wedding_beverages ?: '-' }}</td></tr>
            </tbody>
        </table>
        
        @else
        <!-- REGULAR MENU TABLE -->
        <table class="menu-table">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th class="name">Meal</th>
                    <th class="time">Time</th>
                    <th class="content">Menu Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="num">1</td>
                    <td class="name">Welcome Drink</td>
                    <td class="time">-</td>
                    <td class="content">{{ $menu->welcome_drink ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">2</td>
                    <td class="name">Evening Snack</td>
                    <td class="time">{{ $menu->evening_snack_time ? $menu->getFormattedTime('evening_snack_time') : '-' }}</td>
                    <td class="content">{{ $menu->evening_snack ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">3</td>
                    <td class="name">Dinner</td>
                    <td class="time">{{ $menu->dinner_time ? $menu->getFormattedTime('dinner_time') : '-' }}</td>
                    <td class="content">{{ $menu->dinner ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">4</td>
                    <td class="name">Dessert (Dinner)</td>
                    <td class="time">-</td>
                    <td class="content">{{ $menu->dessert_after_dinner ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">5</td>
                    <td class="name">Bed Tea</td>
                    <td class="time">{{ $menu->bed_tea_time ? $menu->getFormattedTime('bed_tea_time') : '-' }}</td>
                    <td class="content">{{ $menu->bed_tea ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">6</td>
                    <td class="name">Breakfast</td>
                    <td class="time">{{ $menu->breakfast_time ? $menu->getFormattedTime('breakfast_time') : '-' }}</td>
                    <td class="content">{{ $menu->breakfast ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">7</td>
                    <td class="name">Dessert (Breakfast)</td>
                    <td class="time">-</td>
                    <td class="content">{{ $menu->dessert_after_breakfast ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">8</td>
                    <td class="name">Lunch</td>
                    <td class="time">{{ $menu->lunch_time ? $menu->getFormattedTime('lunch_time') : '-' }}</td>
                    <td class="content">{{ $menu->lunch ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="num">9</td>
                    <td class="name">Dessert (Lunch)</td>
                    <td class="time">-</td>
                    <td class="content">{{ $menu->dessert_after_lunch ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <div>Booking ID: #{{ $booking->id }}</div>
            <div>Page {{ $index + 1 }} of {{ $bookingsWithMenus->count() }}</div>
            <div>Generated: {{ now()->format('M j, Y h:i A') }}</div>
        </div>
    </div>
    @endforeach
    @endif
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
