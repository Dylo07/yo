<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bottle Stock History - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
            gap: 20px;
        }
        
        .summary-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        
        .summary-box.issued {
            background-color: #fee2e2;
            border-color: #dc2626;
        }
        
        .summary-box.added {
            background-color: #d1fae5;
            border-color: #059669;
        }
        
        .summary-box h3 {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-box .value {
            font-size: 32px;
            font-weight: bold;
        }
        
        .summary-box.issued .value {
            color: #dc2626;
        }
        
        .summary-box.added .value {
            color: #059669;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #f3f4f6;
        }
        
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #333;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-issued {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .badge-added {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .badge-bill {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .text-muted {
            color: #9ca3af;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .summary {
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
        
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .print-btn {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        .print-btn:hover {
            background-color: #1d4ed8;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">🖨️ Print Report</button>
    </div>

    <div class="header">
        <h1>Water Bottle Stock History</h1>
        <p>
            {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} 
            @if($startDate != $endDate)
                - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
            @endif
        </p>
        <p style="font-size: 12px; margin-top: 8px;">Generated: {{ \Carbon\Carbon::now()->format('M d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box issued">
            <h3>Total Issued</h3>
            <div class="value">-{{ $totalIssued }}</div>
            <p style="margin-top: 5px; font-size: 11px; color: #666;">bottles</p>
        </div>
        <div class="summary-box added">
            <h3>Total Added</h3>
            <div class="value">+{{ $totalAdded }}</div>
            <p style="margin-top: 5px; font-size: 11px; color: #666;">bottles</p>
        </div>
    </div>

    @if($stockHistory->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 8%;">Time</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 15%;">Room/Note</th>
                    <th style="width: 12%;">Bill #</th>
                    <th style="width: 20%;">Description</th>
                    <th style="width: 15%;">By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockHistory as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('h:i A') }}</td>
                        <td>
                            @if($record->stock > 0)
                                <span class="badge badge-added">Added</span>
                            @else
                                <span class="badge badge-issued">Issued</span>
                            @endif
                        </td>
                        <td style="font-weight: 600;">
                            {{ $record->stock > 0 ? '+' : '' }}{{ $record->stock }}
                        </td>
                        <td>
                            @if($record->notes)
                                {{ str_replace('Room: ', '', $record->notes) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($record->sale_id)
                                <span class="badge badge-bill">BILL #{{ $record->sale_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($record->sale && $record->sale->description)
                                {{ $record->sale->description }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $record->user->name ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #9ca3af;">
            <p style="font-size: 16px;">No stock activity found for the selected date range.</p>
        </div>
    @endif

    <div class="footer">
        <p>Hotel Soba Lanka - Water Bottle Stock Management System</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
