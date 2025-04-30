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
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .header p {
            margin-top: 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .totals td {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
        @media print {
            @page {
                size: landscape;
            }
            body {
                padding: 0;
                margin: 0.5cm;
            }
            .no-print {
                display: none;
            }
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
                <th>Date/Time</th>
                <th>Bill Number</th>
                <th>Rooms</th>
                <th>Swimming Pool</th>
                <th>Arrack</th>
                <th>Beer</th>
                <th>Other</th>
                <th>Service Charge</th>
                <th>Description</th>
                <th>Total</th>
                <th>Cash Payment</th>
                <th>Card Payment</th>
                <th>Bank Payment</th>
                <th>Status</th>
                <th>Verified</th>
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
                    <td>{{ $sale['datetime'] }}</td>
                    <td>{{ $sale['id'] }}</td>
                    <td>{{ number_format($sale['rooms'], 2) }}</td>
                    <td>{{ number_format($sale['swimming_pool'], 2) }}</td>
                    <td>{{ number_format($sale['arrack'], 2) }}</td>
                    <td>{{ number_format($sale['beer'], 2) }}</td>
                    <td>{{ number_format($sale['other'], 2) }}</td>
                    <td>{{ number_format($sale['service_charge'], 2) }}</td>
                    <td>{{ $sale['description'] }}</td>
                    <td>{{ number_format($sale['total'], 2) }}</td>
                    <td>{{ $sale['cash_payment'] ? number_format($sale['cash_payment'], 2) : '' }}</td>
                    <td>{{ $sale['card_payment'] ? number_format($sale['card_payment'], 2) : '' }}</td>
                    <td>{{ $sale['bank_payment'] ? number_format($sale['bank_payment'], 2) : '' }}</td>
                    <td>{{ ucfirst($sale['status']) }}</td>
                    <td>{{ $sale['verified'] ? 'Yes' : 'No' }}</td>
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
                <td>{{ number_format($totalRooms, 2) }}</td>
                <td>{{ number_format($totalSwimming, 2) }}</td>
                <td>{{ number_format($totalArrack, 2) }}</td>
                <td>{{ number_format($totalBeer, 2) }}</td>
                <td>{{ number_format($totalOther, 2) }}</td>
                <td>{{ number_format($totalServiceCharge, 2) }}</td>
                <td></td>
                <td>{{ number_format($totalAmount, 2) }}</td>
                <td>{{ number_format($totalCash, 2) }}</td>
                <td>{{ number_format($totalCard, 2) }}</td>
                <td>{{ number_format($totalBank, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Printed on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close();" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>