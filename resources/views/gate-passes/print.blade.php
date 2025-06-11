{{-- resources/views/gate-passes/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass - {{ $gatePass->gate_pass_number }} - {{ $gatePass->person->name }}</title>
    <style>
        @media print {
            #buttons { display: none; }
            @page { margin: 0.5cm; }
        }

        body { 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            max-width: 400px; /* Same width as leave request */
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

        .gate-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background: transparent;
        }

        .gate-details tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .gate-details th,
        .gate-details td {
            padding: 8px 0;
            background: transparent !important;
            vertical-align: top;
            line-height: 1.4;
        }

        .gate-details th {
            text-align: left;
            width: 100px; /* Same width as leave request */
            font-weight: normal;
        }

        .status-badge, .purpose-badge {
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

        .status-approved, .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-returned {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-rejected {
            background-color: #f1f3f4;
            color: #495057;
        }

        .purpose-badge {
            background-color: #17a2b8;
            color: white;
        }

        .emergency-badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .time-section {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            font-size: 12px;
        }

        .time-section h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #333;
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

        .security-info {
            margin: 15px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 12px;
        }

        .security-info h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #856404;
        }

        /* Signature styles - same as leave request */
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
            font-size: 16px; /* Same as leave request */
            color: rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 2px; /* Same as leave request */
            width: 100%;
            text-align: center;
            z-index: -1;
        }

        #buttons {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px; /* Same as leave request */
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
            <h2>Gate Pass</h2>
            <p>Pass ID: {{ $gatePass->gate_pass_number }}</p>
            @if($gatePass->emergency_pass)
                <span class="emergency-badge">Emergency Pass</span>
            @endif
        </div>

        <table class="gate-details">
            <tr>
                <th>Staff</th>
                <td>{{ $gatePass->person->name }}
                    @if($gatePass->person->staffCode)
                        <br><small>{{ $gatePass->person->staffCode->staff_code }}</small>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td>
                    <span class="purpose-badge">{{ $gatePass->formatted_purpose }}</span>
                </td>
            </tr>
            <tr>
                <th>Destination</th>
                <td>{{ $gatePass->destination ?: 'Not specified' }}</td>
            </tr>
            <tr>
                <th>Exit Time</th>
                <td>{{ $gatePass->exit_time->format('M j, Y g:i A') }}</td>
            </tr>
            <tr>
                <th>Return By</th>
                <td>{{ $gatePass->expected_return->format('M j, Y g:i A') }}</td>
            </tr>
            <tr>
                <th>Duration</th>
                <td>{{ $gatePass->formatted_duration }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="status-badge status-{{ $gatePass->status }}">
                        {{ ucfirst($gatePass->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Contact</th>
                <td>{{ $gatePass->contact_number ?: 'Not provided' }}</td>
            </tr>
            @if($gatePass->vehicle_number)
            <tr>
                <th>Vehicle</th>
                <td>{{ $gatePass->vehicle_number }}</td>
            </tr>
            @endif
            <tr>
                <th>Requested By</th>
                <td>{{ $gatePass->requestedBy->name }}</td>
            </tr>
            <tr>
                <th>Request Date</th>
                <td>{{ $gatePass->created_at->format('M j, Y') }}</td>
            </tr>
            @if($gatePass->approved_by)
            <tr>
                <th>{{ $gatePass->status === 'approved' ? 'Approved' : 'Processed' }} By</th>
                <td>{{ $gatePass->approvedBy->name }}</td>
            </tr>
            @endif
            @if($gatePass->actual_return)
            <tr>
                <th>Returned</th>
                <td>{{ $gatePass->actual_return->format('M j, Y g:i A') }}</td>
            </tr>
            @endif
        </table>

        <!-- Time Period Visual -->
        <div class="time-section">
            <h4>Time Schedule</h4>
            <div class="time-visual">
                <div class="time-point">
                    <div><strong>EXIT</strong></div>
                    <div>{{ $gatePass->exit_time->format('M j') }}</div>
                    <div>{{ $gatePass->exit_time->format('g:i A') }}</div>
                </div>
                <div class="time-arrow">→</div>
                <div class="time-point">
                    <div><strong>RETURN</strong></div>
                    <div>{{ $gatePass->expected_return->format('M j') }}</div>
                    <div>{{ $gatePass->expected_return->format('g:i A') }}</div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 10px;">
                <strong>Duration: {{ $gatePass->formatted_duration }}</strong>
            </div>
        </div>

        @if($gatePass->reason)
        <div class="reason-section">
            <h4>Reason</h4>
            <p style="margin: 0; font-size: 11px;">{{ $gatePass->reason }}</p>
        </div>
        @endif

        @if($gatePass->items_carried)
        <div class="security-info">
            <h4>Items Carried</h4>
            <p style="margin: 0; font-size: 11px;">{{ $gatePass->items_carried }}</p>
        </div>
        @endif

        @if($gatePass->admin_remarks)
        <div class="admin-remarks">
            <h4>Admin Remarks</h4>
            <p style="margin: 0; font-size: 11px;">{{ $gatePass->admin_remarks }}</p>
        </div>
        @endif

        <div class="resort-name">
            Hotel Soba Lanka
        </div>

        <div class="signature-row">
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">Staff</div>
                <div class="signature-title">Signature</div>
            </div>
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">Security</div>
                <div class="signature-title">Out</div>
            </div>
            <div class="signature-field">
                <div class="signature-line"></div>
                <div class="signature-title">Security</div>
                <div class="signature-title">In</div>
            </div>
        </div>

        <div id="receipt-footer">
            <p>Generated on {{ now()->format('F j, Y g:i A') }}</p>
        </div>

        <div id="buttons">
            <button class="btn btn-print" type="button" onclick="window.print(); return false;">
                Print
            </button>
            <a href="{{ route('gate-passes.index') }}">
                <button class="btn btn-back">
                    Back
                </button>
            </a>
        </div>
    </div>

    <script>
        // Update time remaining if viewing active pass
        @if(in_array($gatePass->status, ['approved', 'active']) && !$gatePass->actual_return)
        function updateTimeRemaining() {
            const returnTime = new Date('{{ $gatePass->expected_return->format('Y-m-d H:i:s') }}');
            const now = new Date();
            const diff = returnTime - now;
            
            if (diff > 0) {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                // Find duration display and update it
                const durationElement = document.querySelector('.time-section div:last-child strong');
                if (durationElement) {
                    if (hours > 0) {
                        durationElement.innerHTML = `Time Remaining: ${hours}h ${minutes}m`;
                    } else {
                        durationElement.innerHTML = `Time Remaining: ${minutes} minutes`;
                    }
                }
            } else {
                // Overdue
                const durationElement = document.querySelector('.time-section div:last-child strong');
                if (durationElement) {
                    durationElement.innerHTML = `<span style="color: #dc3545;">OVERDUE</span>`;
                }
            }
        }
        
        // Update every minute
        setInterval(updateTimeRemaining, 60000);
        updateTimeRemaining(); // Initial call
        @endif
    </script>
</body>
</html>