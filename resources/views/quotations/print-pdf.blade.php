<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->id }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 11px;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #2ea022;
            padding-bottom: 15px;
            overflow: hidden;
        }
        .logo-section {
            float: left;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .company-info {
            float: right;
            text-align: right;
        }
        .company-info h2 {
            margin: 0 0 5px;
            color: #2ea022;
            font-size: 15px;
            font-weight: 600;
        }
        .company-contact {
            font-size: 10px;
            line-height: 1.4;
            color: #555;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        /* Client Info Section */
        .client-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 12px 15px;
        }
        .client-row {
            margin-bottom: 5px;
        }
        .client-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 100px;
        }
        .client-value {
            display: inline-block;
        }
        .date-badge {
            background: #2ea022;
            color: white;
            padding: 6px 15px;
            font-size: 11px;
            display: inline-block;
            margin-bottom: 15px;
        }

        /* Menu Section */
        .menu-section {
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        .menu-header {
            background: #2ea022;
            color: white;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }
        .menu-content {
            padding: 15px 20px;
        }
        .menu-category {
            margin-bottom: 12px;
        }
        .menu-category-title {
            font-weight: 700;
            color: #2ea022;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #e8e8e8;
            padding-bottom: 3px;
        }
        .menu-category-items {
            font-size: 10px;
            color: #444;
            line-height: 1.6;
            white-space: pre-line;
            padding-left: 5px;
        }

        /* Pricing Table */
        .pricing-section {
            margin: 20px 0;
        }
        .pricing-header {
            background: #333;
            color: white;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 600;
        }
        table.pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 10px;
        }
        .pricing-table th {
            background-color: #f5f5f5;
            color: #333;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        .pricing-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .pricing-table tfoot td {
            padding: 8px;
            font-weight: 600;
        }
        .total-row td {
            background: #f8f9fa;
            border-top: 2px solid #2ea022;
            font-size: 12px;
            color: #2ea022;
        }
        
        /* Comments */
        .comments-section {
            margin-top: 25px;
            padding: 12px 15px;
            background-color: #fafafa;
            border-left: 3px solid #2ea022;
            font-size: 10px;
        }
        .comments-section h4 {
            color: #333;
            margin: 0 0 8px 0;
            font-size: 11px;
        }
        .comments-section ul {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }
        .comments-section li {
            margin-bottom: 4px;
            color: #555;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #888;
            font-size: 9px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .footer p {
            margin: 3px 0;
        }
        .thank-you {
            color: #2ea022;
            font-weight: 600;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="logo-section">
            <img src="{{ $logoPath }}" alt="Soba Lanka Logo" class="logo">
        </div>
        <div class="company-info">
            <h2>Soba Lanka Holiday Resort (PVT) LTD</h2>
            <div class="company-contact">
                Balawattala Road, Meliripura<br>
                071 71 52 955 | sobalanka.com<br>
                sobalankahotel@gmail.com
            </div>
        </div>
    </div>

    <div class="date-badge">
        Quotation Date: {{ $quotation->quotation_date->format('jS F, Y') }}
    </div>

    <div class="client-section">
        <div class="client-row">
            <span class="client-label">Client Name:</span>
            <span class="client-value">{{ $quotation->client_name }}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Address:</span>
            <span class="client-value">{{ $quotation->client_address }}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Event Date:</span>
            <span class="client-value"><strong>{{ $quotation->schedule->format('jS F, Y') }}</strong></span>
        </div>
    </div>

@if($quotation->menu_items && count($quotation->menu_items) > 0)
    <div class="menu-section">
        <div class="menu-header">MENU PACKAGE</div>
        <div class="menu-content">
            @php
                $menuOrder = [
                    'welcome_drink' => 'Welcome Drink',
                    'evening_snack' => 'Evening Snack', 
                    'dinner' => 'Dinner',
                    'live_bbq' => 'Live BBQ Experience',
                    'bed_tea' => 'Bed Tea',
                    'breakfast' => 'Breakfast',
                    'morning_snack' => 'Morning Snack',
                    'lunch' => 'Lunch',
                    'desserts' => 'Desserts'
                ];
            @endphp
            
            @foreach($menuOrder as $menuKey => $menuLabel)
                @if(isset($quotation->menu_items[$menuKey]) && !empty($quotation->menu_items[$menuKey]['content']))
                <div class="menu-category">
                    <div class="menu-category-title">{{ $menuLabel }}</div>
                    <div class="menu-category-items">{{ $quotation->menu_items[$menuKey]['content'] }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

    <div class="pricing-section">
        <div class="pricing-header">PRICING DETAILS</div>
        <table class="pricing-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 15%;">Pax</th>
                    <th class="text-right" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="text-right">{{ isset($item['pricePerItem']) ? number_format((float)$item['pricePerItem'], 2) : '-' }}</td>
                    <td class="text-right">{{ isset($item['pax']) ? $item['pax'] : '-' }}</td>
                    <td class="text-right">{{ isset($item['quantity']) ? $item['quantity'] : '-' }}</td>
                    <td class="text-right">{{ isset($item['amount']) ? number_format((float)$item['amount'], 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Service Charge</td>
                    <td class="text-right">{{ number_format((float)$quotation->service_charge, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Grand Total (LKR)</strong></td>
                    <td class="text-right"><strong>Rs. {{ number_format((float)$quotation->total_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="comments-section">
        <h4>Terms & Conditions:</h4>
        <ul>
            @foreach($quotation->comments as $comment)
            <li>• {{ $comment }}</li>
            @endforeach
        </ul>
    </div>

    <div class="footer">
        <p>If you have any questions about this quotation, please contact us.</p>
        <p>This is a computer-generated quotation; therefore, no signature is required.</p>
        <p class="thank-you">Thank You for Choosing Soba Lanka!</p>
    </div>
</body>
</html>
