<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Food Menu - {{ $date->format('F j, Y') }}</title>
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            width: 100%;
            height: 100%;
        }
        
        .page-header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 2px solid #000;
        }
        
        .date-title {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
        }
        
        .date-number {
            position: relative;
            display: inline-block;
            margin-right: 5px;
        }
        
        .date-number-top {
            font-size: 12pt;
            position: absolute;
            top: -10px;
            right: -10px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: 120px repeat(4, 1fr);
            width: 100%;
            border-collapse: collapse;
        }
        
        .menu-header, .menu-cell {
            border: 2px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .menu-header {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        .function-info {
            background-color: #f5f5f5;
            text-align: left;
            padding: 8px;
            display: flex;
            flex-direction: column;
        }
        
        .function-name {
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 5px;
        }
        
        .guest-count {
            font-weight: bold;
        }
        
        .check-in-out {
            display: flex;
            flex-direction: column;
            font-size: 9pt;
            margin-top: 5px;
        }
        
        .check-label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        .menu-content {
            white-space: pre-line;
            text-align: left;
            padding: 5px;
            min-height: 100px;
            vertical-align: top;
        }
        
        .not-included {
            color: #999;
            font-style: italic;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        @media print {
            .menu-grid {
                page-break-inside: avoid;
            }
            
            .function-row {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1 class="date-title">
            <span class="date-number">{{ $date->format('j') }}<sup class="date-number-top">th</sup></span> 
            {{ $date->format('F') }}
        </h1>
    </div>
    
    <div class="menu-grid">
        <!-- Headers -->
        <div class="menu-cell" style="background-color: #f5f5f5;"></div>
        <div class="menu-header">Breakfast</div>
        <div class="menu-header">Lunch</div>
        <div class="menu-header">Snack</div>
        <div class="menu-header">Dinner</div>
        
        @foreach($bookingsWithMenus as $item)
            <!-- Function row -->
            <div class="function-info">
                <div class="function-name">{{ $item['booking']->guest_count }}</div>
                <div class="check-in-out">
                    <div>
                        <span class="check-label">IN</span>
                        {{ $item['booking']->start->format('g:i A j\t\h F') }}
                    </div>
                    <div>
                        <span class="check-label">OUT</span>
                        {{ $item['booking']->end ? $item['booking']->end->format('g:i A j\t\h F') : 'N/A' }}
                    </div>
                </div>
            </div>
            
            <!-- Breakfast cell -->
            <div class="menu-cell">
                @if($item['menu']->shouldShowMeal('breakfast') && $item['menu']->breakfast)
                    <div class="menu-content">{{ $item['menu']->breakfast }}</div>
                @else
                    <div class="not-included">-</div>
                @endif
            </div>
            
            <!-- Lunch cell -->
            <div class="menu-cell">
                @if($item['menu']->shouldShowMeal('lunch') && $item['menu']->lunch)
                    <div class="menu-content">{{ $item['menu']->lunch }}</div>
                @else
                    <div class="not-included">-</div>
                @endif
            </div>
            
            <!-- Evening Snack cell -->
            <div class="menu-cell">
                @if($item['menu']->shouldShowMeal('evening_snack') && $item['menu']->evening_snack)
                    <div class="menu-content">{{ $item['menu']->evening_snack }}</div>
                @else
                    <div class="not-included">-</div>
                @endif
            </div>
            
            <!-- Dinner cell -->
            <div class="menu-cell">
                @if($item['menu']->shouldShowMeal('dinner') && $item['menu']->dinner)
                    <div class="menu-content">{{ $item['menu']->dinner }}</div>
                @else
                    <div class="not-included">-</div>
                @endif
            </div>
        @endforeach
        
        <!-- Add empty row at bottom if needed -->
        <div class="menu-cell" style="background-color: #f5f5f5;"></div>
        <div class="menu-cell"></div>
        <div class="menu-cell"></div>
        <div class="menu-cell"></div>
        <div class="menu-cell"></div>
    </div>
</body>
</html>