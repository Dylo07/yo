<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu - {{ $date->format('F j, Y') }}</title>
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            width: 297mm;
            height: 210mm;
            position: relative;
        }
        
        .menu-card {
            width: 100%;
            height: 100%;
            padding: 15mm;
            box-sizing: border-box;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 10mm;
            margin-bottom: 10mm;
        }
        
        .date-display {
            font-size: 28pt;
            font-weight: bold;
        }
        
        .date-info {
            font-size: 12pt;
            margin-top: 5mm;
        }
        
        .booking-info {
            text-align: right;
        }
        
        .function-type {
            font-size: 16pt;
            font-weight: bold;
        }
        
        .guest-count {
            font-size: 14pt;
            margin-top: 2mm;
        }
        
        .time-info {
            font-size: 12pt;
            margin-top: 2mm;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 3fr 3fr 3fr;
            grid-template-rows: 1fr;
            height: calc(100% - 25mm);
            border: 2px solid #000;
        }
        
        .section {
            border-right: 2px solid #000;
            position: relative;
        }
        
        .section:last-child {
            border-right: none;
        }
        
        .section-header {
            height: 15mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 14pt;
        }
        
        .section-content {
            padding: 5mm;
            white-space: pre-line;
            height: calc(100% - 25mm);
            overflow: hidden;
        }
        
        .meal-header {
            height: 15mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 14pt;
        }
        
        .meal-content {
            padding: 5mm;
            white-space: pre-line;
        }
        
        .circle-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 8mm;
            height: 8mm;
            border-radius: 50%;
            background-color: #333;
            color: white;
            font-weight: bold;
            margin-right: 2mm;
        }
        
        .not-included {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            font-style: italic;
            background-color: #f8f8f8;
        }
        
        .info-section {
            display: grid;
            grid-template-rows: repeat(3, 1fr);
            height: 100%;
        }
        
        .info-block {
            padding: 5mm;
            border-bottom: 2px solid #000;
        }
        
        .info-block:last-child {
            border-bottom: none;
        }
        
        .info-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 12mm;
            height: 12mm;
            border-radius: 50%;
            background-color: #333;
            color: white;
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 3mm;
        }
        
        .function-details {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .function-dates {
            font-size: 10pt;
            margin-top: 3mm;
        }
        
        .dinner-grid {
            display: grid;
            grid-template-rows: 1fr 1fr;
            height: 100%;
        }
        
        .corner-date {
            position: absolute;
            top: 5mm;
            left: 5mm;
            background-color: #000;
            color: white;
            padding: 3mm 5mm;
            font-weight: bold;
            font-size: 12pt;
        }
        
        .corner-number {
            font-size: 10pt;
            text-align: center;
        }
        
        .colored-block {
            background-color: #f0f8ff;
        }
    </style>
</head>
<body>
    <div class="corner-date">
        <div>{{ $date->format('jS F') }}</div>
        <div class="corner-number">1</div>
    </div>
    
    <div class="menu-card">
        <div class="header">
            <div>
                <div class="date-display">{{ $date->format('jS F') }}</div>
                <div class="date-info">{{ $date->format('l, F j, Y') }}</div>
            </div>
            <div class="booking-info">
                <div class="function-type">{{ $booking->function_type }}</div>
                <div class="guest-count">{{ $booking->guest_count }}</div>
                <div class="time-info">
                    {{ $booking->start->format('h:i A') }} - 
                    {{ $booking->end ? $booking->end->format('h:i A') : 'N/A' }}
                </div>
            </div>
        </div>
        
        <div class="menu-grid">
            <!-- Info section -->
            <div class="section">
                <div class="info-section">
                    <!-- Function Info -->
                    <div class="info-block">
                        <div class="info-circle">4</div>
                        <div class="function-details">{{ $booking->function_type }}</div>
                        <div class="function-details">{{ $booking->guest_count }}</div>
                        <div class="function-dates">
                            <div>IN</div>
                            <div>{{ $booking->start->format('h:i A - M j') }}</div>
                            <div>OUT</div>
                            <div>{{ $booking->end ? $booking->end->format('h:i A - M j') : 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <!-- Guest Count -->
                    <div class="info-block colored-block">
                        <div class="info-circle" style="background-color: #6b46c1;">2</div>
                        <div class="function-details">{{ $booking->guest_count }}</div>
                    </div>
                    
                    <!-- Food Content Notes -->
                    <div class="info-block">
                        <div class="info-circle">9</div>
                        <div style="font-size: 10pt; color: #666; font-style: italic; margin-top: 3mm;">
                            Content in the food - this is where you will see additional notes about the menu
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Breakfast -->
            <div class="section">
                <div class="meal-header">
                    <span class="circle-number" style="background-color: #ec4899;">5</span>
                    Breakfast
                </div>
                @if($menu->shouldShowMeal('breakfast'))
                    <div class="meal-content">
                        {{ $menu->breakfast ?? 'No breakfast menu specified' }}
                    </div>
                @else
                    <div class="not-included">Not included</div>
                @endif
            </div>
            
            <!-- Lunch -->
            <div class="section">
                <div class="meal-header">
                    <span class="circle-number" style="background-color: #3b82f6;">6</span>
                    Lunch
                </div>
                @if($menu->shouldShowMeal('lunch'))
                    <div class="meal-content">
                        {{ $menu->lunch ?? 'No lunch menu specified' }}
                    </div>
                @else
                    <div class="not-included">Not included</div>
                @endif
            </div>
            
            <!-- Evening Snack & Dinner -->
            <div class="section">
                <div class="dinner-grid">
                    <!-- Evening Snack -->
                    <div>
                        <div class="meal-header">
                            <span class="circle-number" style="background-color: #8b5cf6;">7</span>
                            Evening Snack
                        </div>
                        @if($menu->shouldShowMeal('evening_snack'))
                            <div class="meal-content">
                                {{ $menu->evening_snack ?? 'No evening snack menu specified' }}
                            </div>
                        @else
                            <div class="not-included">Not included</div>
                        @endif
                    </div>
                    
                    <!-- Dinner -->
                    <div style="border-top: 2px solid #000;">
                        <div class="meal-header">
                            <span class="circle-number" style="background-color: #eab308;">8</span>
                            Dinner
                        </div>
                        @if($menu->shouldShowMeal('dinner'))
                            <div class="meal-content">
                                {{ $menu->dinner ?? 'No dinner menu specified' }}
                            </div>
                        @else
                            <div class="not-included">Not included</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>