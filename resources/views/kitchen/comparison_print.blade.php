<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen vs Sales Comparison - Print</title>
    <style>
        /* A4 Print Styles */
        @page {
            size: A4 landscape; /* Changed to landscape for better side-by-side view */
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
            background: white;
        }

        .print-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        /* Header */
        .print-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .print-header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
            color: #2563eb;
        }

        .print-header p {
            font-size: 8pt;
            color: #666;
        }

        /* Date Info */
        .date-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 6px;
            background: #f3f4f6;
            border-radius: 3px;
        }

        .date-info div {
            flex: 1;
        }

        .date-info label {
            font-weight: bold;
            font-size: 8pt;
            display: block;
            margin-bottom: 2px;
        }

        .date-info span {
            font-size: 9pt;
        }

        /* Comparison Table - FIXED FOR PRINT */
        .comparison-wrapper {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-top: 10px;
        }

        .comparison-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 2px solid #000;
        }

        .section-header {
            padding: 6px;
            font-weight: bold;
            font-size: 10pt;
            text-align: center;
            color: white;
        }

        .section-header.sales {
            background-color: #2563eb;
        }

        .section-header.kitchen {
            background-color: #059669;
        }

        .section-content {
            padding: 8px;
            min-height: 300px;
        }

        /* Category Blocks */
        .category-block {
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .category-header {
            padding: 4px 6px;
            font-weight: bold;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-header.sales {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .category-header.kitchen {
            background-color: #d1fae5;
            color: #065f46;
        }

        .category-items {
            padding: 4px 6px;
            background: white;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8pt;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            flex: 1;
            padding-right: 8px;
        }

        .item-quantity {
            font-weight: bold;
            min-width: 50px;
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-yellow {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Empty State */
        .empty-state {
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 8pt;
        }

        .empty-state i {
            font-size: 20pt;
            display: block;
            margin-bottom: 8px;
        }

        /* Footer */
        .print-footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 7pt;
            color: #6b7280;
        }

        /* Summary info */
        .summary-info {
            margin-bottom: 8px;
            padding: 4px;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 8pt;
        }

        /* Print-specific rules */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-container {
                width: 100%;
                max-width: none;
            }

            /* CRITICAL: Maintain side-by-side layout in print */
            .comparison-wrapper {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }

            .comparison-section {
                display: table-cell !important;
                width: 50% !important;
                vertical-align: top !important;
                page-break-inside: auto;
            }

            .category-block {
                page-break-inside: avoid;
            }

            .section-content {
                page-break-inside: auto;
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
                background-color: #dbeafe !important;
                color: #1e40af !important;
            }

            .category-header.kitchen {
                background-color: #d1fae5 !important;
                color: #065f46 !important;
            }
        }

        /* No data message */
        .no-data-message {
            padding: 15px;
            text-align: center;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 4px;
            margin: 8px 0;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="print-header">
            <h1>🔍 Kitchen vs Sales Comparison</h1>
            <p>Compare daily sales with main kitchen stock issues</p>
        </div>

        <!-- Date Information -->
        <div class="date-info">
            <div>
                <label>Start Date:</label>
                <span>{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</span>
            </div>
            <div>
                <label>End Date:</label>
                <span>{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</span>
            </div>
            <div>
                <label>Date Range:</label>
                <span>
                    @if($startDate === $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                        <span class="badge badge-yellow">1 day</span>
                    @else
                        {{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} day(s)
                    @endif
                </span>
            </div>
            <div>
                <label>Last Updated:</label>
                <span>{{ now()->format('M d, Y H:i') }}</span>
            </div>
        </div>

        <!-- Comparison Sections -->
        <div class="comparison-wrapper">
            <!-- Daily Sales Section -->
            <div class="comparison-section">
                <div class="section-header sales">
                    Daily Sales
                </div>
                <div class="section-content">
                    @if(empty($dailySalesData['by_category']))
                        <div class="empty-state">
                            <i>📊</i>
                            <p><strong>No sales recorded</strong></p>
                            <p>for the selected date range</p>
                        </div>
                    @else
                        <!-- Sales Summary -->
                        <div class="summary-info">
                            <strong>Total Items:</strong> {{ $dailySalesData['total_items'] }} &nbsp;|&nbsp;
                            <strong>Total Bills:</strong> {{ $dailySalesData['total_sales'] }}
                        </div>

                        @foreach($dailySalesData['by_category'] as $categoryId => $category)
                            <div class="category-block">
                                <div class="category-header sales">
                                    <span>{{ $category['name'] }}</span>
                                    <span class="badge badge-blue">{{ $category['total'] }} items</span>
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
                            <i>🍳</i>
                            <p><strong>No kitchen issues</strong></p>
                            <p>for the selected date range</p>
                        </div>
                    @else
                        <!-- Kitchen Summary -->
                        <div class="summary-info">
                            <strong>Total Quantity:</strong> {{ number_format($mainKitchenData['total_quantity'], 2) }} &nbsp;|&nbsp;
                            <strong>Transactions:</strong> {{ $mainKitchenData['total_transactions'] }}
                        </div>

                        @foreach($mainKitchenData['by_category'] as $categoryId => $category)
                            <div class="category-block">
                                <div class="category-header kitchen">
                                    <span>{{ $category['name'] }}</span>
                                    <span class="badge badge-green">{{ number_format($category['total_quantity'], 2) }}</span>
                                </div>
                                <div class="category-items">
                                    @foreach($category['items'] as $item)
                                        <div class="item-row">
                                            <span class="item-name">{{ $item['name'] }}</span>
                                            <span class="item-quantity">{{ number_format($item['quantity'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="print-footer">
            <p>Generated by Kitchen Management System | {{ now()->format('l, F j, Y \a\t g:i A') }}</p>
            <p>This is a system-generated report</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };

        // Close window after printing or canceling
        window.onafterprint = function() {
            // Optionally close the window after printing
            // window.close();
        };
    </script>
</body>
</html>