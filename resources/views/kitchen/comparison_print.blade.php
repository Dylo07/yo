<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen vs Sales Comparison - Print</title>
    <style>
        /* A4 Portrait - Compact Layout */
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

        /* Compact Header */
        .print-header {
            text-align: center;
            margin-bottom: 4mm;
            padding-bottom: 2mm;
            border-bottom: 1.5pt solid #000;
        }

        .print-header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1mm;
            color: #000;
        }

        /* Compact Date Info */
        .date-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            padding: 2mm;
            background: #f5f5f5;
            font-size: 6.5pt;
            border: 0.5pt solid #ccc;
        }

        .date-info div {
            flex: 1;
        }

        .date-info label {
            font-weight: bold;
            display: inline;
            margin-right: 2px;
        }

        .date-info span {
            display: inline;
        }

        /* Side-by-Side Columns - Compact */
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
            background-color: #000;
        }

        .section-header.sales {
            background-color: #2563eb;
        }

        .section-header.kitchen {
            background-color: #059669;
        }

        .section-content {
            padding: 2mm;
        }

        /* Compact Summary */
        .summary-info {
            margin-bottom: 2mm;
            padding: 1.5mm;
            background: #f5f5f5;
            font-size: 6.5pt;
            border: 0.5pt solid #ddd;
        }

        /* Ultra-Compact Category Blocks */
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
            background: #f0f0f0;
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

        /* Ultra-Compact Item Rows */
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5mm 0;
            font-size: 6.5pt;
            line-height: 1.3;
        }

        .item-row:not(:last-child) {
            border-bottom: 0.25pt solid #f0f0f0;
        }

        .item-name {
            flex: 1;
            padding-right: 3mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .item-details {
            font-size: 5.5pt;
            color: #666;
            margin-top: 0.5mm;
        }

        .item-quantity {
            font-weight: bold;
            min-width: 12mm;
            text-align: right;
        }

        /* Compact Badge */
        .badge {
            display: inline-block;
            padding: 0.5mm 1.5mm;
            border-radius: 1mm;
            font-size: 6pt;
            font-weight: bold;
            background: #e5e7eb;
            color: #374151;
        }

        /* Empty State - Compact */
        .empty-state {
            padding: 5mm;
            text-align: center;
            color: #9ca3af;
            font-size: 7pt;
        }

        /* Minimal Footer */
        .print-footer {
            margin-top: 3mm;
            padding-top: 1.5mm;
            border-top: 0.5pt solid #ccc;
            text-align: center;
            font-size: 5.5pt;
            color: #666;
        }

        /* Print-specific optimizations */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-container {
                width: 100%;
                max-width: none;
            }

            /* Force table layout */
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

            /* Prevent breaks */
            .category-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .item-row {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Ensure colors print */
            .section-header.sales {
                background-color: #2563eb !important;
                color: white !important;
            }

            .section-header.kitchen {
                background-color: #059669 !important;
                color: white !important;
            }

            .category-header.sales {
                background-color: #e0f2fe !important;
                color: #1e40af !important;
            }

            .category-header.kitchen {
                background-color: #d1fae5 !important;
                color: #065f46 !important;
            }

            /* Remove unnecessary spacing */
            h1, h2, h3, p {
                orphans: 3;
                widows: 3;
            }
        }

        /* Hide emojis for print */
        .emoji {
            display: none;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Compact Header -->
        <div class="print-header">
            <h1>Kitchen vs Sales Comparison</h1>
        </div>

        <!-- Compact Date Information -->
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
                <label>Updated:</label>
                <span>{{ now()->format('M d, H:i') }}</span>
            </div>
        </div>

        <!-- Comparison Sections - Side by Side -->
        <div class="comparison-wrapper">
            <!-- Daily Sales Section -->
            <div class="comparison-section">
                <div class="section-header sales">
                    Daily Sales
                </div>
                <div class="section-content">
                    @if(empty($dailySalesData['by_category']))
                        <div class="empty-state">
                            <p><strong>No sales recorded</strong></p>
                        </div>
                    @else
                        <!-- Compact Summary -->
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
                                            <span class="item-name">{{ $item['name'] }}</span>
                                            <span class="item-quantity">{{ number_format($item['quantity'], 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Main Kitchen Issues Section -->
            <div class="comparison-section">
                <div class="section-header kitchen">
                    Main Kitchen Issues
                </div>
                <div class="section-content">
                    @if(empty($mainKitchenData['by_category']))
                        <div class="empty-state">
                            <p><strong>No kitchen issues</strong></p>
                        </div>
                    @else
                        <!-- Compact Summary -->
                        <div class="summary-info">
                            <strong>Qty:</strong> {{ number_format($mainKitchenData['total_quantity'], 1) }} | 
                            <strong>Trans:</strong> {{ $mainKitchenData['total_transactions'] }}
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
                                            <div style="flex: 1;">
                                                <div class="item-name">{{ $item['name'] }}</div>
                                                @if(isset($item['time']) && isset($item['user']))
                                                    <div class="item-details">{{ $item['time'] }} by {{ $item['user'] }}</div>
                                                @endif
                                            </div>
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

        <!-- Minimal Footer -->
        <div class="print-footer">
            <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>