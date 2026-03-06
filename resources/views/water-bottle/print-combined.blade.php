<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Rooms & Stock History - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 11px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        
        .container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .section {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title.rooms {
            color: #0dcaf0;
        }
        
        .section-title.stock {
            color: #343a40;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        thead {
            background-color: #f3f4f6;
        }
        
        th {
            padding: 6px 4px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #333;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .badge-info {
            background-color: #cffafe;
            color: #0891b2;
        }
        
        .text-muted {
            color: #9ca3af;
        }
        
        .summary-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 10px;
        }
        
        .summary-box strong {
            font-size: 16px;
            display: block;
            margin-top: 3px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .no-print {
            margin-bottom: 15px;
            text-align: center;
        }
        
        .print-btn {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }
        
        .print-btn:hover {
            background-color: #1d4ed8;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            .container {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">🖨️ Print Report</button>
    </div>

    <div class="header">
        <h1>Hotel Soba Lanka - Vehicle Rooms & Stock History</h1>
        <p>
            {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} 
            @if($startDate != $endDate)
                - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
            @endif
        </p>
        <p style="font-size: 10px; margin-top: 5px;">Generated: {{ \Carbon\Carbon::now()->format('M d, Y h:i A') }}</p>
    </div>

    <div class="container">
        <!-- Vehicle Rooms Section -->
        <div class="section">
            <div class="section-title rooms">
                <span>🚗</span> VEHICLE ROOMS
            </div>
            
            @if($vehicleRooms->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 20%;">Room</th>
                            <th style="width: 30%;">Vehicle No.</th>
                            <th style="width: 25%;">Check In</th>
                            <th style="width: 25%;">Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicleRooms as $vehicle)
                            @php
                                $rooms = is_string($vehicle->room_numbers) 
                                    ? json_decode($vehicle->room_numbers, true) 
                                    : $vehicle->room_numbers;
                                $rooms = is_array($rooms) ? $rooms : [];
                            @endphp
                            @foreach($rooms as $room)
                                <tr>
                                    <td><span class="badge badge-primary">{{ $room }}</span></td>
                                    <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                                    <td><small>{{ $vehicle->created_at->format('M d, h:i A') }}</small></td>
                                    <td>
                                        @if($vehicle->checkout_time)
                                            <small>{{ \Carbon\Carbon::parse($vehicle->checkout_time)->format('M d, h:i A') }}</small>
                                        @else
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                
                <div class="summary-box">
                    <div style="color: #666;">Total Vehicle Entries</div>
                    <strong style="color: #0891b2;">{{ $vehicleRooms->count() }}</strong>
                </div>
            @else
                <div style="text-align: center; padding: 30px; color: #9ca3af;">
                    <p>No vehicle room entries found for the selected date range.</p>
                </div>
            @endif
        </div>

        <!-- Stock History Section -->
        <div class="section">
            <div class="section-title stock">
                <span>📦</span> WATER BOTTLE STOCK HISTORY
            </div>
            
            @if($stockHistory->count() > 0)
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <div class="summary-box" style="flex: 1; background-color: #fee2e2;">
                        <div style="color: #666;">Total Issued</div>
                        <strong style="color: #dc2626;">-{{ $totalIssued }}</strong>
                    </div>
                    <div class="summary-box" style="flex: 1; background-color: #d1fae5;">
                        <div style="color: #666;">Total Added</div>
                        <strong style="color: #059669;">+{{ $totalAdded }}</strong>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Time</th>
                            <th style="width: 12%;">Type</th>
                            <th style="width: 10%;">Qty</th>
                            <th style="width: 15%;">Room</th>
                            <th style="width: 15%;">Bill #</th>
                            <th style="width: 20%;">Description</th>
                            <th style="width: 13%;">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockHistory as $record)
                            <tr>
                                <td><small>{{ \Carbon\Carbon::parse($record->created_at)->format('M d, h:i A') }}</small></td>
                                <td>
                                    @if($record->stock > 0)
                                        <span class="badge badge-success">Added</span>
                                    @else
                                        <span class="badge badge-danger">Issued</span>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">
                                    {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                                </td>
                                <td>
                                    @if($record->notes)
                                        <small>{{ str_replace('Room: ', '', $record->notes) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->sale_id)
                                        <span class="badge badge-info">{{ $record->sale_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->dailySalesSummary && $record->dailySalesSummary->description)
                                        <small>{{ $record->dailySalesSummary->description }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $record->user->name ?? 'Unknown' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 30px; color: #9ca3af;">
                    <p>No stock activity found for the selected date range.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Hotel Soba Lanka - Combined Report: Vehicle Rooms & Water Bottle Stock Management</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>
