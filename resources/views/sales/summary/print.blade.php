{{-- resources/views/sales/summary/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
        }
        .report-title {
            font-size: 18px;
            margin: 10px 0;
        }
        .date-range {
            font-size: 14px;
            margin-bottom: 20px;
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
            background-color: #f5f5f5;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            border: 1px solid #ddd;
            padding: 10px;
            width: 23%;
            text-align: center;
        }
        .stat-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="report-title">{{ $title }}</div>
        <div class="date-range">Generated on: {{ now()->format('d M Y H:i:s') }}</div>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-title">Total Sales</div>
            <div>Rs. {{ number_format($summaryData->sum('total_revenue'), 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Total Items</div>
            <div>{{ number_format($summaryData->sum('total_quantity')) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Average Sale</div>
            <div>Rs. {{ number_format($summaryData->avg('total_revenue'), 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Categories</div>
            <div>{{ $summaryData->pluck('category_name')->unique()->count() }}</div>
        </div>
    </div>

    <!-- Category Summary -->
    <h3>Category Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Total Items</th>
                <th>Total Revenue</th>
                <th>Average Price</th>
                <th>% of Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRevenue = $summaryData->sum('total_revenue');
                $categorySummary = $summaryData->groupBy('category_name')
                    ->map(function($items) use ($totalRevenue) {
                        return [
                            'quantity' => $items->sum('total_quantity'),
                            'revenue' => $items->sum('total_revenue'),
                            'average' => $items->avg('average_price'),
                            'percentage' => ($items->sum('total_revenue') / $totalRevenue) * 100
                        ];
                    });
            @endphp
            @foreach($categorySummary as $category => $stats)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ number_format($stats['quantity']) }}</td>
                    <td>Rs. {{ number_format($stats['revenue'], 2) }}</td>
                    <td>Rs. {{ number_format($stats['average'], 2) }}</td>
                    <td>{{ number_format($stats['percentage'], 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detailed Sales Summary -->
    <h3>Detailed Sales Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Menu ID</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Total Revenue</th>
                <th>Average Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $item)
                <tr>
                    <td>{{ $item->menu_id }}</td>
                    <td>{{ $item->menu_name }}</td>
                    <td>{{ $item->category_name }}</td>
                    <td>{{ number_format($item->total_quantity) }}</td>
                    <td>Rs. {{ number_format($item->total_revenue, 2) }}</td>
                    <td>Rs. {{ number_format($item->total_revenue / $item->total_quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by {{ config('app.name') }} Sales Summary System</p>
        <p>Page {PAGENO} of {nb}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>