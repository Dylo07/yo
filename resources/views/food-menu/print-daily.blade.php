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
            width: 100%;
            border-collapse: collapse;
        }
        
        .menu-grid td, .menu-grid th {
            border: 2px solid #000;
            padding: 8px;
        }
        
        .menu-header {
            font-weight: bold;
            text-align: center;
            background-color: #f5f5f5;
        }
        
        .function-info {
            width: 100px;
            background-color: #f5f5f5;
        }
        
        .function-guest-count {
            font-weight: bold;
            font-size: 14pt;
        }
        
        .check-in-out {
            font-size: 9pt;
            margin-top: 8px;
        }
        
        .check-label {
            font-weight: bold;
        }
        
        .date-suffix {
            font-size: 8pt;
            vertical-align: super;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1 class="date-title">
            {{ $date->format('j') }}<span class="date-suffix">{{ $date->format('S') }}</span> {{ $date->format('F') }}
        </h1>
    </div>
    
    <table class="menu-grid">
        <thead>
            <tr>
                <th class="function-info"></th>
                <th class="menu-header">Breakfast</th>
                <th class="menu-header">Lunch</th>
                <th class="menu-header">Snack</th>
                <th class="menu-header">Dinner</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookingsWithMenus as $item)
                <tr>
                    <!-- Function Info -->
                    <td class="function-info">
                        <div class="function-guest-count">{{ $item['booking']->guest_count }}</div>
                        <div class="check-in-out">
                            <div>
                                <span class="check-label">IN</span>
                                {{ $item['booking']->start->format('g:i A j') }}
                                {{ $item['booking']->start->format('M') }}
                            </div>
                            <div>
                                <span class="check-label">OUT</span>
                                @if($item['booking']->end)
                                    {{ $item['booking']->end->format('g:i A j') }}
                                    {{ $item['booking']->end->format('M') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </td>
                    
                    <!-- Breakfast -->
                    <td>{{ $item['menu']->breakfast ?: '-' }}</td>
                    
                    <!-- Lunch -->
                    <td>{{ $item['menu']->lunch ?: '-' }}</td>
                    
                    <!-- Evening Snack (renamed to Snack) -->
                    <td>{{ $item['menu']->evening_snack ?: '-' }}</td>
                    
                    <!-- Dinner -->
                    <td>{{ $item['menu']->dinner ?: '-' }}</td>
                </tr>
            @endforeach
            
            <!-- Empty row at the bottom -->
            <tr>
                <td class="function-info"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>