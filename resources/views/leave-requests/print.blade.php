<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request - {{ $leaveRequest->person->name }} - {{ $leaveRequest->start_date->format('F j, Y') }}</title>
    <style>
        @media print {
            #buttons { display: none; }
            @page { margin: 0.5cm; }
        }

        body { 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        #wrapper {
            position: relative;
            padding: 20px;
        }

        .background-logo {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            width: 200px;
            height: auto;
        }

        #receipt-header {
            text-align: left;
            margin-bottom: 30px;
        }

        #receipt-header h2 {
            margin-bottom: 5px;
        }

        .leave-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background: transparent;
        }

        .leave-details tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .leave-details th,
        .leave-details td {
            padding: 8px 0;
            background: transparent !important;
            vertical-align: top;
            line-height: 1.4;
        }

        .leave-details th {
            text-align: left;
            width: 100px;
            font-weight: normal;
        }

        .status-badge, .leave-type-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .leave-type-badge {
            background-color: #17a2b8;
            color: white;
        }

        .duration-badge {
            background-color: #6f42c1;
            color: white;
        }

        .time-badge {
            background-color: #28a745;
            color: white;
        }

        .reason-section {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            font-size: 12px;
        }

        .reason-section h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #333;
        }

        .admin-remarks {
            margin: 15px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 12px;
        }

        .admin-remarks h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #856404;
        }

        .time-period-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #e7f3ff;
            border-left: 3px solid #007bff;
            font-size: 12px;
        }

        .time-period-section h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #0056b3;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin: 50px 0 30px;
            padding-top: 20px;
        }

        .signature-field {
            flex: 1;
            text-align: center;
            margin: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 12px;
        }

        #receipt-footer {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
        }

        .resort-name {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
            color: rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 100%;
            text-align: center;
            z-index: -1;
        }

        #buttons {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            margin: 0 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background-color: #4CAF50;
            color: white;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .time-visual {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px;
            background-color: #f1f3f4;
            border-radius: 5px;
        }

        .time-point {
            text-align: center;
            flex: 1;
        }

        .time-arrow {
            flex: 0.5;
            text-align: center;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <img class="background-logo" src="{{ asset('image/lg.png') }}" alt="Watermark Logo">

        <div id="receipt-header">
            <h2>Leave Request</h2>
            <p>Request ID: #{{ $leaveRequest->id }}</p>
        </div>

        <table class="leave-details">
            <tr>
                <th>Staff</th>
                <td>{{ $leaveRequest->person->name }}
                    @if($leaveRequest->person->staffCode)
                        <br><small>{{ $leaveRequest->person->staffCode->staff_code }}</small>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Leave Type</th>
                <td>
                    <span class="leave-type-badge">{{ $leaveRequest->formatted_leave_type }}</span>
                </td>
            </tr>
            <tr>
                <th>Duration Type</th>
                <td>
                    @if($leaveRequest->is_datetime_based)
                        <span class="duration-badge">Specific Time</span>
                    @else
                        <span class="duration-badge">Full Day(s)</span>
                    @endif
                </td>
            </tr>
            @if($leaveRequest->is_datetime_based)
                <tr>
                    <th>Start Time</th>
                    <td>{{ $leaveRequest->start_datetime->format('M j, Y') }}<br>
                        <span class="time-badge">{{ $leaveRequest->start_datetime->format('g:i A') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>End Time</th>
                    <td>{{ $leaveRequest->end_datetime->format('M j, Y') }}<br>
                        <span class="time-badge">{{ $leaveRequest->end_datetime->format('g:i A') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Total Hours</th>
                    <td>{{ $leaveRequest->hours }} hours
                        @if($leaveRequest->hours < 8)
                            <br><small>(Half Day)</small>
                        @endif
                    </td>
                </tr>
            @else
                <tr>
                    <th>Start Date</th>
                    <td>{{ $leaveRequest->start_date->format('M j, Y') }}</td>
                </tr>
                <tr>
                    <th>End Date</th>
                    <td>{{ $leaveRequest->end_date->format('M j, Y') }}</td>
                </tr>
            @endif
            <tr>
                <th>Duration</th>
                <td>{{ $leaveRequest->formatted_duration }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="status-badge status-{{ $leaveRequest->status }}">
                        {{ ucfirst($leaveRequest->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Requested By</th>
                <td>{{ $leaveRequest->requestedBy->name }}</td>
            </tr>
            <tr>
                <th>Request Date</th>
                <td>{{ $leaveRequest->created_at->format('M j, Y') }}</td>
            </tr>
            @if($leaveRequest->approved_by)
            <tr>
                <th>{{ $leaveRequest->status === 'approved' ? 'Approved' : 'Rejected' }} By</th>
                <td>{{ $leaveRequest->approvedBy->name }}</td>
            </tr>
            @endif
        </table>

        @if($leaveRequest->is_datetime_based)
        <div class="time-period-section">
            <h4>Time Period Visual</h4>
            <div class="time-visual">
                <div class="time-point">
                    <div><strong>START</strong></div>
                    <div>{{ $leaveRequest->start_datetime->format('M j') }}</div>
                    <div>{{ $leaveRequest->start_datetime->format('g:i A') }}</div>
                </div>
                <div class="time-arrow">→</div>
                <div class="time-point">
                    <div><strong>END</strong></div>
                    <div>{{ $leaveRequest->end_datetime->format('M j') }}</div>
                    <div>{{ $leaveRequest->end_datetime->format('g:i A') }}</div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 10px;">
                <strong>Total: {{ $leaveRequest->formatted_duration }}</strong>
            </div>
        </div>
        @endif

        @if($leaveRequest->reason)
        <div class="reason-section">
            <h4>Reason</h4>
            <p style="margin: 0; font-size: 11px;">{{ $leaveRequest->reason }}</p>
        </div>
        @endif

        @if($leaveRequest->admin_remarks)
        <div class="admin-remarks">
            <h4>Admin Remarks</h4>
            <p style="margin: 0; font-size: 11px;">{{ $leaveRequest->admin_remarks }}</p>
        </div>
        @endif

        <div class="resort-name">
            Hotel Soba Lanka
        </div>

        <div class="signature-row">
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">Employee</div>
                <div class="signature-title">Signature</div>
            </div>
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">HR</div>
                <div class="signature-title">Approval</div>
            </div>
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">Manager</div>
                <div class="signature-title">Approval</div>
            </div>
        </div>

        <div id="receipt-footer">
            <p>Generated on {{ now()->format('F j, Y g:i A') }}</p>
        </div>

        <div id="buttons">
            <button class="btn btn-print" type="button" onclick="window.print(); return false;">
                Print
            </button>
            <a href="{{ route('leave-requests.index') }}">
                <button class="btn btn-back">
                    Back
                </button>
            </a>
        </div>
    </div>
</body>
</html>