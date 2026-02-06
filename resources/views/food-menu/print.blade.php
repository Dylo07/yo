<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu - {{ $date->format('F j, Y') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }
        
        .page {
            width: 190mm;
            min-height: 277mm;
            margin: 0 auto;
            padding: 8mm;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 5mm;
            border-bottom: 3px solid #2c3e50;
            margin-bottom: 5mm;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 4mm;
        }
        
        .logo-icon {
            width: 15mm;
            height: 15mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 3mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20pt;
            font-weight: bold;
        }
        
        .title-section h1 {
            font-size: 18pt;
            color: #2c3e50;
            margin-bottom: 1mm;
        }
        
        .title-section .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
        }
        
        .date-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3mm 5mm;
            border-radius: 2mm;
            text-align: center;
        }
        
        .date-badge .day {
            font-size: 24pt;
            font-weight: bold;
            line-height: 1;
        }
        
        .date-badge .month {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .date-badge .year {
            font-size: 9pt;
            opacity: 0.9;
        }
        
        /* Info Bar */
        .info-bar {
            display: flex;
            justify-content: space-between;
            background: #f8f9fa;
            border-radius: 2mm;
            padding: 4mm;
            margin-bottom: 5mm;
            border-left: 4px solid #667eea;
        }
        
        .info-item {
            text-align: center;
            flex: 1;
            border-right: 1px solid #dee2e6;
            padding: 0 3mm;
        }
        
        .info-item:last-child {
            border-right: none;
        }
        
        .info-item .label {
            font-size: 8pt;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        
        .info-item .value {
            font-size: 11pt;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .info-item .value.highlight {
            color: #667eea;
            font-size: 13pt;
        }
        
        /* Menu Grid */
        .menu-section-title {
            font-size: 12pt;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        
        .menu-section-title .icon {
            width: 6mm;
            height: 6mm;
            background: #667eea;
            border-radius: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 8pt;
        }
        
        /* Regular Menu Grid - 2 columns */
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3mm;
            margin-bottom: 4mm;
        }
        
        /* Wedding Menu Grid - 2 columns */
        .wedding-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3mm;
        }
        
        .menu-item {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 2mm;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .menu-item-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2mm 3mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #dee2e6;
        }
        
        .menu-item-title {
            font-weight: 600;
            font-size: 10pt;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        
        .menu-item-number {
            width: 5mm;
            height: 5mm;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .menu-item-time {
            font-size: 8pt;
            color: #6c757d;
            background: white;
            padding: 1mm 2mm;
            border-radius: 1mm;
        }
        
        .menu-item-content {
            padding: 2mm 3mm;
            font-size: 9pt;
            color: #495057;
            min-height: 12mm;
            white-space: pre-line;
        }
        
        .menu-item-content:empty::before,
        .menu-item-content .empty {
            content: '-';
            color: #adb5bd;
            font-style: italic;
        }
        
        /* Color variations for different meal types */
        .menu-item.welcome .menu-item-number { background: #06b6d4; }
        .menu-item.snack .menu-item-number { background: #8b5cf6; }
        .menu-item.dinner .menu-item-number { background: #f59e0b; }
        .menu-item.dessert .menu-item-number { background: #ec4899; }
        .menu-item.tea .menu-item-number { background: #10b981; }
        .menu-item.breakfast .menu-item-number { background: #f97316; }
        .menu-item.lunch .menu-item-number { background: #3b82f6; }
        
        /* Wedding specific colors */
        .menu-item.appetizer .menu-item-number { background: #ef4444; }
        .menu-item.shooters .menu-item-number { background: #a855f7; }
        .menu-item.salad .menu-item-number { background: #22c55e; }
        .menu-item.soup .menu-item-number { background: #eab308; }
        .menu-item.bread .menu-item-number { background: #d97706; }
        .menu-item.rice .menu-item-number { background: #0ea5e9; }
        .menu-item.meat .menu-item-number { background: #dc2626; }
        .menu-item.seafood .menu-item-number { background: #0891b2; }
        .menu-item.vegetables .menu-item-number { background: #16a34a; }
        .menu-item.condiments .menu-item-number { background: #ca8a04; }
        .menu-item.beverages .menu-item-number { background: #7c3aed; }
        
        /* Full width items */
        .menu-item.full-width {
            grid-column: span 2;
        }
        
        /* Footer */
        .footer {
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            color: #6c757d;
        }
        
        /* Wedding Badge */
        .wedding-badge {
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);
            color: white;
            padding: 1mm 3mm;
            border-radius: 2mm;
            font-size: 9pt;
            font-weight: 600;
            margin-left: 3mm;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .page {
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo-icon">🍽</div>
                <div class="title-section">
                    <h1>Food Menu @if($booking->function_type == 'Wedding')<span class="wedding-badge">Wedding</span>@endif</h1>
                    <div class="subtitle">{{ $date->format('l, F j, Y') }}</div>
                </div>
            </div>
            <div class="date-badge">
                <div class="day">{{ $date->format('d') }}</div>
                <div class="month">{{ $date->format('M') }}</div>
                <div class="year">{{ $date->format('Y') }}</div>
            </div>
        </div>
        
        <!-- Info Bar -->
        <div class="info-bar">
            <div class="info-item">
                <div class="label">Function Type</div>
                <div class="value highlight">{{ $booking->function_type }}</div>
            </div>
            <div class="info-item">
                <div class="label">Guest Count</div>
                <div class="value highlight">{{ $booking->guest_count }}</div>
            </div>
            <div class="info-item">
                <div class="label">Check In</div>
                <div class="value">{{ $booking->start->format('h:i A') }}</div>
            </div>
            <div class="info-item">
                <div class="label">Check Out</div>
                <div class="value">{{ $booking->end ? $booking->end->format('h:i A') : 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Rooms</div>
                <div class="value">{{ is_array($booking->room_numbers) ? implode(', ', $booking->room_numbers) : $booking->room_numbers }}</div>
            </div>
        </div>
        
        @if($booking->function_type == 'Wedding')
        <!-- ========== WEDDING MENU ========== -->
        <div class="menu-section-title">
            <span class="icon">🎊</span>
            Wedding Menu Items
        </div>
        
        <div class="wedding-grid">
            <!-- Welcome Drink -->
            <div class="menu-item welcome">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">1</span>
                        Welcome Drink
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_welcome_drink ?: '-' }}</div>
            </div>
            
            <!-- Appetizer -->
            <div class="menu-item appetizer">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">2</span>
                        Appetizer
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_appetizer ?: '-' }}</div>
            </div>
            
            <!-- Shooters -->
            <div class="menu-item shooters">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">3</span>
                        Shooters
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_shooters ?: '-' }}</div>
            </div>
            
            <!-- Salad Bar -->
            <div class="menu-item salad">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">4</span>
                        Salad Bar
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_salad_bar ?: '-' }}</div>
            </div>
            
            <!-- Salad Dressing -->
            <div class="menu-item salad">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">5</span>
                        Salad Dressing
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_salad_dressing ?: '-' }}</div>
            </div>
            
            <!-- Soup -->
            <div class="menu-item soup">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">6</span>
                        Soup
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_soup ?: '-' }}</div>
            </div>
            
            <!-- Bread Corner -->
            <div class="menu-item bread">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">7</span>
                        Bread Corner
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_bread_corner ?: '-' }}</div>
            </div>
            
            <!-- Rice & Noodle -->
            <div class="menu-item rice">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">8</span>
                        Rice & Noodle
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_rice_noodle ?: '-' }}</div>
            </div>
            
            <!-- Meat Items -->
            <div class="menu-item meat">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">9</span>
                        Meat Items
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_meat_items ?: '-' }}</div>
            </div>
            
            <!-- Seafood Items -->
            <div class="menu-item seafood">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">10</span>
                        Seafood Items
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_seafood_items ?: '-' }}</div>
            </div>
            
            <!-- Vegetables -->
            <div class="menu-item vegetables">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">11</span>
                        Vegetables
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_vegetables ?: '-' }}</div>
            </div>
            
            <!-- Condiments -->
            <div class="menu-item condiments">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">12</span>
                        Condiments
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_condiments ?: '-' }}</div>
            </div>
            
            <!-- Desserts -->
            <div class="menu-item dessert">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">13</span>
                        Desserts
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_desserts ?: '-' }}</div>
            </div>
            
            <!-- Beverages -->
            <div class="menu-item beverages">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">14</span>
                        Beverages
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->wedding_beverages ?: '-' }}</div>
            </div>
        </div>
        
        @else
        <!-- ========== REGULAR MENU ========== -->
        <div class="menu-section-title">
            <span class="icon">🍴</span>
            Daily Menu Items
        </div>
        
        <div class="menu-grid">
            <!-- Welcome Drink -->
            <div class="menu-item welcome full-width">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">1</span>
                        Welcome Drink
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->welcome_drink ?: '-' }}</div>
            </div>
            
            <!-- Evening Snack -->
            <div class="menu-item snack">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">2</span>
                        Evening Snack
                    </div>
                    @if($menu->evening_snack_time)
                        <span class="menu-item-time">{{ $menu->getFormattedTime('evening_snack_time') }}</span>
                    @endif
                </div>
                <div class="menu-item-content">{{ $menu->evening_snack ?: '-' }}</div>
            </div>
            
            <!-- Dinner -->
            <div class="menu-item dinner">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">3</span>
                        Dinner
                    </div>
                    @if($menu->dinner_time)
                        <span class="menu-item-time">{{ $menu->getFormattedTime('dinner_time') }}</span>
                    @endif
                </div>
                <div class="menu-item-content">{{ $menu->dinner ?: '-' }}</div>
            </div>
            
            <!-- Dessert (Dinner) -->
            <div class="menu-item dessert">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">4</span>
                        Dessert (Dinner)
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->dessert_after_dinner ?: '-' }}</div>
            </div>
            
            <!-- Bed Tea -->
            <div class="menu-item tea">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">5</span>
                        Bed Tea
                    </div>
                    @if($menu->bed_tea_time)
                        <span class="menu-item-time">{{ $menu->getFormattedTime('bed_tea_time') }}</span>
                    @endif
                </div>
                <div class="menu-item-content">{{ $menu->bed_tea ?: '-' }}</div>
            </div>
            
            <!-- Breakfast -->
            <div class="menu-item breakfast">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">6</span>
                        Breakfast
                    </div>
                    @if($menu->breakfast_time)
                        <span class="menu-item-time">{{ $menu->getFormattedTime('breakfast_time') }}</span>
                    @endif
                </div>
                <div class="menu-item-content">{{ $menu->breakfast ?: '-' }}</div>
            </div>
            
            <!-- Dessert (Breakfast) -->
            <div class="menu-item dessert">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">7</span>
                        Dessert (Breakfast)
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->dessert_after_breakfast ?: '-' }}</div>
            </div>
            
            <!-- Lunch -->
            <div class="menu-item lunch">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">8</span>
                        Lunch
                    </div>
                    @if($menu->lunch_time)
                        <span class="menu-item-time">{{ $menu->getFormattedTime('lunch_time') }}</span>
                    @endif
                </div>
                <div class="menu-item-content">{{ $menu->lunch ?: '-' }}</div>
            </div>
            
            <!-- Dessert (Lunch) -->
            <div class="menu-item dessert full-width">
                <div class="menu-item-header">
                    <div class="menu-item-title">
                        <span class="menu-item-number">9</span>
                        Dessert (Lunch)
                    </div>
                </div>
                <div class="menu-item-content">{{ $menu->dessert_after_lunch ?: '-' }}</div>
            </div>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <div>Booking ID: #{{ $booking->id }}</div>
            <div>Generated: {{ now()->format('M j, Y h:i A') }}</div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>