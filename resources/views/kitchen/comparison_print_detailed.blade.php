<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen vs Sales - Detailed Print</title>
    <style>
        @page { size: A4 portrait; margin: 5mm 6mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 6.5pt; line-height: 1.15; color: #000; }

        .header { text-align: center; margin-bottom: 2mm; padding-bottom: 1mm; border-bottom: 1pt solid #000; }
        .header h1 { font-size: 10pt; margin: 0; }
        .header .sub { font-size: 7pt; font-weight: bold; color: #555; margin-top: 0.5mm; }
        .meta { font-size: 6pt; margin-top: 0.5mm; }

        .cols { display: table; width: 100%; table-layout: fixed; border-spacing: 2mm 0; }
        .col { display: table-cell; width: 50%; vertical-align: top; }

        .col-title { font-size: 7.5pt; font-weight: bold; text-align: center; padding: 1mm 0; border-bottom: 0.75pt solid #000; margin-bottom: 1mm; }

        .cat { margin-bottom: 1.5mm; page-break-inside: avoid; }
        .cat-head { font-size: 6.5pt; font-weight: bold; padding: 0.5mm 0; border-bottom: 0.5pt solid #999; display: flex; justify-content: space-between; }
        .cat-items { padding-left: 1mm; }

        .row { display: flex; justify-content: space-between; padding: 0.3mm 0; font-size: 6pt; }
        .row:not(:last-child) { border-bottom: 0.25pt dotted #ccc; }
        .row .name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 1mm; }
        .row .qty { font-weight: bold; min-width: 10mm; text-align: right; }
        .row .cost { font-size: 5.5pt; color: #666; min-width: 16mm; text-align: right; padding-right: 1mm; }

        .recipe { font-size: 5pt; color: #666; font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 2mm; }
        .cat-summary { font-size: 5pt; color: #555; font-style: italic; padding: 0.3mm 0; border-bottom: 0.25pt solid #ddd; }
        .cat-cost { font-size: 5.5pt; color: #666; font-weight: normal; }

        .bill-detail { font-size: 5pt; color: #444; padding-left: 2mm; line-height: 1.2; }
        .bill-detail span { display: inline-block; margin-right: 1.5mm; white-space: nowrap; }
        .bill-num { color: #0066cc; font-weight: bold; }

        .log-detail { font-size: 5pt; color: #444; padding-left: 2mm; line-height: 1.2; }
        .log-detail span { display: inline-block; margin-right: 1.5mm; white-space: nowrap; }
        .log-time { color: #006600; }
        .log-qty { font-weight: bold; }

        .grand-summary { margin-top: 2mm; padding: 1mm; border: 0.75pt solid #000; page-break-inside: avoid; }
        .grand-summary-title { font-size: 7pt; font-weight: bold; margin-bottom: 0.5mm; border-bottom: 0.5pt solid #999; padding-bottom: 0.5mm; }
        .grand-summary-body { font-size: 5.5pt; line-height: 1.3; word-spacing: 1mm; }
        .grand-summary-body .ing { white-space: nowrap; display: inline-block; margin-right: 2mm; margin-bottom: 0.3mm; }
        .grand-summary-body .ing-qty { font-weight: bold; }

        .footer { margin-top: 1.5mm; text-align: right; font-size: 5pt; color: #888; }

        @media print {
            .cols { display: table !important; width: 100% !important; }
            .col { display: table-cell !important; width: 50% !important; }
            .cat { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kitchen vs Sales Comparison</h1>
        <div class="sub">— Detailed View —</div>
        <div class="meta">
            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            @if($startDate !== $endDate)
                ({{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} days)
            @endif
            &nbsp;|&nbsp; Sales: {{ $dailySalesData['total_items'] }} items / {{ $dailySalesData['total_sales'] }} bills
            &nbsp;|&nbsp; Issues: {{ number_format($mainKitchenData['total_quantity'], 1) }} qty / {{ $mainKitchenData['total_transactions'] }} txns
            @if(($mainKitchenData['total_cost'] ?? 0) > 0)
                / Rs {{ number_format($mainKitchenData['total_cost'], 0) }}
            @endif
            &nbsp;|&nbsp; {{ now()->format('M d H:i') }}
        </div>
    </div>

    <div class="cols">
        <!-- Daily Sales with Bill Details -->
        <div class="col">
            <div class="col-title">Daily Sales ({{ $dailySalesData['total_items'] }})</div>
            @if(empty($dailySalesData['by_category']))
                <div style="text-align:center; padding:3mm; color:#999; font-size:6pt;">No sales</div>
            @else
                @foreach($dailySalesData['by_category'] as $categoryId => $category)
                    <div class="cat">
                        <div class="cat-head">
                            <span>{{ $category['name'] }}</span>
                            <span>{{ $category['total'] }}</span>
                        </div>
                        @if(!empty($category['category_summary']))
                            <div class="cat-summary">{{ $category['category_summary'] }}</div>
                        @endif
                        <div class="cat-items">
                            @foreach($category['items'] as $item)
                                <div class="row">
                                    <span class="name">{{ $item['name'] }}</span>
                                    <span class="qty">{{ number_format($item['quantity'], 0) }}</span>
                                </div>
                                @if(!empty($item['item_summary']))
                                    <div class="recipe">{{ $item['item_summary'] }}</div>
                                @endif
                                @if(!empty($item['bills']))
                                    <div class="bill-detail">
                                        @foreach($item['bills'] as $billId => $billQty)
                                            <span><span class="bill-num">#{{ $billId }}</span>×{{ $billQty }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if(!empty($grandIngredientSummary))
                    <div class="grand-summary">
                        <div class="grand-summary-title">Total Ingredients Summary</div>
                        <div class="grand-summary-body">
                            @foreach(preg_split('/\s{2,}/', $grandIngredientSummary) as $ing)
                                @if(preg_match('/^(.+?)\s+([\d,.]+)$/', trim($ing), $m))
                                    <span class="ing">{{ $m[1] }} <span class="ing-qty">{{ $m[2] }}</span></span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Kitchen Issues with Date/Time Details -->
        <div class="col">
            <div class="col-title">{{ $issueFilterLabel ?? 'Main Kitchen' }} Issues ({{ number_format($mainKitchenData['total_quantity'], 1) }})</div>
            @if(empty($mainKitchenData['by_category']))
                <div style="text-align:center; padding:3mm; color:#999; font-size:6pt;">No issues</div>
            @else
                @foreach($mainKitchenData['by_category'] as $categoryId => $category)
                    <div class="cat">
                        <div class="cat-head">
                            <span>{{ $category['name'] }}</span>
                            <span>{{ number_format($category['total_quantity'], 1) }}@if(($category['total_cost'] ?? 0) > 0) <span class="cat-cost">Rs {{ number_format($category['total_cost'], 0) }}</span>@endif</span>
                        </div>
                        <div class="cat-items">
                            @foreach($category['items'] as $item)
                                <div class="row">
                                    <span class="name">{{ $item['name'] }}</span>
                                    @if(($item['total_cost'] ?? 0) > 0)
                                        <span class="cost">Rs {{ number_format($item['total_cost'], 0) }}</span>
                                    @endif
                                    <span class="qty">{{ number_format($item['quantity'], 1) }}</span>
                                </div>
                                @if(!empty($item['logs']))
                                    <div class="log-detail">
                                        @foreach($item['logs'] as $log)
                                            <span><span class="log-time">{{ $log['time'] }}</span> <span class="log-qty">×{{ number_format($log['qty'], 1) }}</span></span>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="footer">{{ now()->format('Y-m-d H:i') }}</div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
