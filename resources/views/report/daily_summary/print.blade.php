<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Summary - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 10px; /* Reduced font size */
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            margin-bottom: 3px;
            font-size: 16px;
        }
        .header p {
            margin-top: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px 4px; /* Reduced padding */
            text-align: left;
            font-size: 9px; /* Reduced font size for table content */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px; /* Reduced font size for headers */
            white-space: nowrap; /* Prevent header text wrapping */
        }
        .totals td {
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
        }
        @media print {
            @page {
                size: landscape;
                margin: 0.5cm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            /* Define fixed width for each column to optimize space */
            .date-column { width: 8%; }
            .bill-column { width: 5%; }
            .numeric-column { width: 5%; }
            .service-column { width: 7%; }
            .description-column { width: 14%; }
            .status-column { width: 5%; }
            .verified-column { width: 4%; }
        }
        /* Compress description text */
        .description-cell {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Sales Summary</h1>
        <p>Date: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="date-column">Date/Time</th>
                <th class="bill-column">Bill #</th>
                <th class="numeric-column">Rooms</th>
                <th class="numeric-column">Pool</th>
                <th class="numeric-column">Arrack</th>
                <th class="numeric-column">Beer</th>
                <th class="numeric-column">Other</th>
                <th class="numeric-column">Service</th>
                <th class="description-column">Description</th>
                <th class="numeric-column">Total</th>
                <th class="numeric-column">Cash</th>
                <th class="numeric-column">Card</th>
                <th class="numeric-column">Bank</th>
                <th class="status-column">Status</th>
                <th class="verified-column">Verified</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRooms = 0;
                $totalSwimming = 0;
                $totalArrack = 0;
                $totalBeer = 0;
                $totalOther = 0;
                $totalServiceCharge = 0;
                $totalAmount = 0;
                $totalCash = 0;
                $totalCard = 0;
                $totalBank = 0;
            @endphp

            @foreach($sales as $sale)
                <tr>
                    <td class="date-column">{{ substr($sale['datetime'], 0, 60) }}</td>
                    <td class="bill-column">{{ $sale['id'] }}</td>
                    <td class="numeric-column">{{ $sale['rooms'] > 0 ? number_format($sale['rooms'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['swimming_pool'] > 0 ? number_format($sale['swimming_pool'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['arrack'] > 0 ? number_format($sale['arrack'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['beer'] > 0 ? number_format($sale['beer'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['other'] > 0 ? number_format($sale['other'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['service_charge'] > 0 ? number_format($sale['service_charge'], 2) : '' }}</td>
                    <td class="description-column description-cell">{{ $sale['description'] }}</td>
                    <td class="numeric-column">{{ number_format($sale['total'], 2) }}</td>
                    <td class="numeric-column">{{ $sale['cash_payment'] ? number_format($sale['cash_payment'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['card_payment'] ? number_format($sale['card_payment'], 2) : '' }}</td>
                    <td class="numeric-column">{{ $sale['bank_payment'] ? number_format($sale['bank_payment'], 2) : '' }}</td>
                    <td class="status-column">{{ ucfirst($sale['status']) }}</td>
                    <td class="verified-column">{{ $sale['verified'] ? 'Yes' : 'No' }}</td>
                </tr>

                @php
                    $totalRooms += $sale['rooms'];
                    $totalSwimming += $sale['swimming_pool'];
                    $totalArrack += $sale['arrack'];
                    $totalBeer += $sale['beer'];
                    $totalOther += $sale['other'];
                    $totalServiceCharge += $sale['service_charge'];
                    $totalAmount += $sale['total'];
                    $totalCash += $sale['cash_payment'] ?? 0;
                    $totalCard += $sale['card_payment'] ?? 0;
                    $totalBank += $sale['bank_payment'] ?? 0;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="2" style="text-align: right;"><strong>Total</strong></td>
                <td>{{ $totalRooms > 0 ? number_format($totalRooms, 2) : '' }}</td>
                <td>{{ $totalSwimming > 0 ? number_format($totalSwimming, 2) : '' }}</td>
                <td>{{ $totalArrack > 0 ? number_format($totalArrack, 2) : '' }}</td>
                <td>{{ $totalBeer > 0 ? number_format($totalBeer, 2) : '' }}</td>
                <td>{{ $totalOther > 0 ? number_format($totalOther, 2) : '' }}</td>
                <td>{{ $totalServiceCharge > 0 ? number_format($totalServiceCharge, 2) : '' }}</td>
                <td></td>
                <td><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                <td>{{ $totalCash > 0 ? number_format($totalCash, 2) : '' }}</td>
                <td>{{ $totalCard > 0 ? number_format($totalCard, 2) : '' }}</td>
                <td>{{ $totalBank > 0 ? number_format($totalBank, 2) : '' }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Printed on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print();" style="padding: 8px 16px; font-size: 14px; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close();" style="padding: 8px 16px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>