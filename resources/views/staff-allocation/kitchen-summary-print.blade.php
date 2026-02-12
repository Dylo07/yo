<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Summary - Print</title>
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
            font-family: 'Arial', sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: #000;
            background: white;
        }

        .print-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        .print-header {
            text-align: center;
            margin-bottom: 4mm;
            padding-bottom: 2mm;
            border-bottom: 1.5pt solid #000;
        }

        .print-header h1 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .print-header .subtitle {
            font-size: 8pt;
            color: #555;
        }

        .date-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            padding: 2mm;
            background: #f5f5f5;
            font-size: 7pt;
            border: 0.5pt solid #ccc;
        }

        .date-info label {
            font-weight: bold;
            margin-right: 2px;
        }

        /* Summary Stats */
        .stats-row {
            display: flex;
            justify-content: space-around;
            margin-bottom: 4mm;
            gap: 2mm;
        }

        .stat-box {
            flex: 1;
            text-align: center;
            padding: 2mm;
            border: 0.5pt solid #ccc;
            border-radius: 1mm;
        }

        .stat-box.sales {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .stat-box.kitchen {
            background: #ecfdf5;
            border-color: #6ee7b7;
        }

        .stat-box .label {
            font-size: 6pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #666;
        }

        .stat-box .value {
            font-size: 11pt;
            font-weight: bold;
        }

        .stat-box.sales .value { color: #1d4ed8; }
        .stat-box.kitchen .value { color: #059669; }

        /* Two Column Layout */
        .comparison-wrapper {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 3mm 0;
        }

        .comparison-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1pt solid #000;
        }

        .section-header {
            padding: 2mm;
            font-weight: bold;
            font-size: 9pt;
            text-align: center;
            color: white;
        }

        .section-header.sales { background-color: #2563eb; }
        .section-header.kitchen { background-color: #059669; }

        .section-content {
            padding: 2mm;
        }

        .summary-info {
            margin-bottom: 2mm;
            padding: 1.5mm;
            background: #f5f5f5;
            font-size: 6.5pt;
            border: 0.5pt solid #ddd;
        }

        .category-block {
            margin-bottom: 2mm;
            border: 0.5pt solid #ccc;
            page-break-inside: avoid;
        }

        .category-header {
            padding: 1.5mm 2mm;
            font-weight: bold;
            font-size: 7.5pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 0.5pt solid #ccc;
        }

        .category-header.sales {
            background-color: #e0f2fe;
            color: #1e40af;
        }

        .category-header.kitchen {
            background-color: #d1fae5;
            color: #065f46;
        }

        .category-items {
            padding: 1mm 2mm;
            background: white;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8mm 0;
            font-size: 6.5pt;
            line-height: 1.3;
        }

        .item-row:not(:last-child) {
            border-bottom: 0.25pt solid #eee;
        }

        .item-name {
            flex: 1;
            padding-right: 2mm;
        }

        .item-summary {
            font-size: 5.5pt;
            color: #888;
            font-style: italic;
            margin-top: 0.3mm;
        }

        .item-quantity {
            font-weight: bold;
            min-width: 12mm;
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 0.5mm 1.5mm;
            border-radius: 1mm;
            font-size: 6pt;
            font-weight: bold;
            background: #e5e7eb;
            color: #374151;
        }

        .empty-state {
            padding: 5mm;
            text-align: center;
            color: #9ca3af;
            font-size: 7pt;
        }

        .print-footer {
            margin-top: 3mm;
            padding-top: 1.5mm;
            border-top: 0.5pt solid #ccc;
            text-align: center;
            font-size: 5.5pt;
            color: #666;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .comparison-wrapper {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            .comparison-section {
                display: table-cell !important;
                width: 50% !important;
                vertical-align: top !important;
            }

            .category-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .section-header.sales { background-color: #2563eb !important; color: white !important; }
            .section-header.kitchen { background-color: #059669 !important; color: white !important; }
            .category-header.sales { background-color: #e0f2fe !important; color: #1e40af !important; }
            .category-header.kitchen { background-color: #d1fae5 !important; color: #065f46 !important; }
            .stat-box.sales { background: #eff6ff !important; }
            .stat-box.kitchen { background: #ecfdf5 !important; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1>Kitchen Summary Report</h1>
            <div class="subtitle">Daily Sales & Main Kitchen Issues</div>
        </div>

        <div class="date-info">
            <div>
                <label>Start:</label>
                <span>{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</span>
            </div>
            <div>
                <label>End:</label>
                <span>{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</span>
            </div>
            <div>
                <label>Range:</label>
                <span>
                    @if($startDate === $endDate)
                        1 day
                    @else
                        {{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} days
                    @endif
                </span>
            </div>
            <div>
                <label>Printed:</label>
                <span>{{ now()->format('M d, Y H:i') }}</span>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-row">
            <div class="stat-box sales">
                <div class="label">Sales Items</div>
                <div class="value">{{ $dailySalesData['total_items'] }}</div>
            </div>
            <div class="stat-box sales">
                <div class="label">Total Bills</div>
                <div class="value">{{ $dailySalesData['total_sales'] }}</div>
            </div>
            <div class="stat-box kitchen">
                <div class="label">Kitchen Qty</div>
                <div class="value">{{ number_format($mainKitchenData['total_quantity'], 1) }}</div>
            </div>
            <div class="stat-box kitchen">
                <div class="label">Transactions</div>
                <div class="value">{{ $mainKitchenData['total_transactions'] }}</div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="comparison-wrapper">
            <!-- Daily Sales -->
            <div class="comparison-section">
                <div class="section-header sales">Daily Sales</div>
                <div class="section-content">
                    @if(empty($dailySalesData['by_category']))
                        <div class="empty-state"><p><strong>No sales recorded</strong></p></div>
                    @else
                        <div class="summary-info">
                            <strong>Items:</strong> {{ $dailySalesData['total_items'] }} |
                            <strong>Bills:</strong> {{ $dailySalesData['total_sales'] }}
                        </div>

                        @foreach($dailySalesData['by_category'] as $categoryId => $category)
                            <div class="category-block">
                                <div class="category-header sales">
                                    <span>{{ $category['name'] }}</span>
                                    <span class="badge">{{ $category['total'] }}</span>
                                </div>
                                <div class="category-items">
                                    @foreach($category['items'] as $item)
                                        <div class="item-row">
                                            <div class="item-name">
                                                {{ $item['name'] }}
                                                @if(!empty($item['item_summary']))
                                                    <div class="item-summary">{{ $item['item_summary'] }}</div>
                                                @endif
                                            </div>
                                            <span class="item-quantity">{{ number_format($item['quantity'], 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Main Kitchen Issues -->
            <div class="comparison-section">
                <div class="section-header kitchen">Main Kitchen Issues</div>
                <div class="section-content">
                    @if(empty($mainKitchenData['by_category']))
                        <div class="empty-state"><p><strong>No kitchen issues</strong></p></div>
                    @else
                        <div class="summary-info">
                            <strong>Qty:</strong> {{ number_format($mainKitchenData['total_quantity'], 1) }} |
                            <strong>Transactions:</strong> {{ $mainKitchenData['total_transactions'] }}
                        </div>

                        @foreach($mainKitchenData['by_category'] as $categoryId => $category)
                            <div class="category-block">
                                <div class="category-header kitchen">
                                    <span>{{ $category['name'] }}</span>
                                    <span class="badge">{{ number_format($category['total_quantity'], 1) }}</span>
                                </div>
                                <div class="category-items">
                                    @foreach($category['items'] as $item)
                                        <div class="item-row">
                                            <span class="item-name">{{ $item['name'] }}</span>
                                            <span class="item-quantity">{{ number_format($item['quantity'], 1) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="print-footer">
            <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
