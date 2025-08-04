@extends('layouts.app')

<style>
    .table td {
        white-space: nowrap;
        font-size: 11px;
        padding: 4px 6px !important;
        text-align: center;
        border: 1px solid #e5e7eb;
        vertical-align: middle;
        position: relative;
        min-width: 50px;
    }
    
    .table th {
        white-space: nowrap;
        font-size: 12px;
        padding: 8px 6px !important;
        text-align: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: 1px solid #6366f1;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        min-width: 50px;
    }
    
    .date-header {
        background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
        color: white;
        font-weight: 700;
        border-bottom: 2px solid #3730a3;
        position: relative;
    }
    
    .punch-subheader {
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
        color: white;
        font-size: 10px;
        font-weight: 500;
        padding: 4px 2px !important;
        position: relative;
    }
    
    .punch-subheader.out-column {
        background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
    }
    
    .staff-info {
        text-align: left;
        font-weight: 600;
        vertical-align: middle !important;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-right: 3px solid #6366f1 !important;
        color: #374151;
        position: sticky;
        left: 0;
        z-index: 5;
        min-width: 120px;
    }
    
    .staff-row {
        border-top: 2px solid #6366f1;
    }
    
    .staff-separator {
        background: linear-gradient(90deg, #e5e7eb 0%, #f3f4f6 50%, #e5e7eb 100%);
        height: 8px;
        border: none;
    }
    
    .time-cell {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 10px;
        font-weight: 500;
        padding: 2px 4px !important;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .time-cell:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 20;
    }
    
    /* Color coding for punch times */
    .punch-early {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }
    
    .punch-morning {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #6366f1;
        border: 1px solid #8b5cf6;
    }
    
    .punch-afternoon {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
        border: 1px solid #f87171;
    }
    
    .punch-out-early {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #991b1b;
        border: 1px solid #ef4444;
        font-style: italic;
    }
    
    .absent {
        color: #9ca3af;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        font-style: italic;
        border: 1px dashed #d1d5db;
    }
    
    .table-responsive {
        overflow-x: auto;
        margin-top: 15px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        background: white;
    }
    
    .card {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 20px 25px;
        font-size: 18px;
    }
    
    .card-body {
        padding: 25px;
        background: #fafbfc;
    }
    
    .form-select, .form-control {
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    /* Legend */
    .legend {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .legend-item {
        display: inline-block;
        margin: 5px 10px;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }
    
    /* Day separators - EVERY DAY gets a separator */
    .day-boundary {
        border-right: 3px solid #dc2626 !important;
        box-shadow: 1px 0 0 0 #dc2626;
    }
    
    /* Week separators - even stronger */
    .week-separator {
        border-right: 5px solid #991b1b !important;
        box-shadow: 2px 0 4px rgba(153, 27, 27, 0.4);
    }
    
    /* Weekend styling */
    .weekend-column {
        background-color: #fef3f2 !important;
    }
    
    .today-column {
        background-color: #f0f9ff !important;
        box-shadow: inset 0 0 0 2px #0ea5e9;
    }
    
    /* Alternating day groups for better visibility */
    .date-group-1 { background-color: #f8fafc; }
    .date-group-2 { background-color: #f1f5f9; }
    .date-group-3 { background-color: #f8fafc; }
    .date-group-4 { background-color: #f1f5f9; }
    .date-group-5 { background-color: #f8fafc; }
    
    /* Month sections */
    .first-week { border-left: 4px solid #059669 !important; }
    .mid-month { border-left: 2px solid #6366f1 !important; }
    .end-month { border-right: 4px solid #dc2626 !important; }
    
    /* Alternating day backgrounds for cells */
    .odd-day { background-color: rgba(99, 102, 241, 0.03) !important; }
    .even-day { background-color: rgba(139, 92, 246, 0.03) !important; }
    
    /* Make all borders more visible */
    .table td, .table th {
        border: 1px solid #d1d5db !important;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .table td, .table th {
            font-size: 9px;
            padding: 2px 3px !important;
            min-width: 35px;
        }
        
        .staff-info {
            min-width: 80px;
        }
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Attendance Manager -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>🕒 Attendance Manager</span>
                    <div class="d-flex align-items-center">
                        <a href="/manual-attendance" class="btn btn-primary me-3">
                            ➕ Manual Attendance
                        </a>
                        <form method="GET" class="d-flex align-items-center">
                            <select name="month" class="form-select me-2" onchange="this.form.submit()">
                                @foreach(range(0, 11) as $m)
                                    @php
                                        $date = now()->subMonths($m);
                                        $value = $date->format('Y-m');
                                    @endphp
                                    <option value="{{ $value }}" 
                                            {{ request('month', now()->format('Y-m')) == $value ? 'selected' : '' }}>
                                        {{ $date->format('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Color Legend -->
                    <div class="legend">
                        <strong>📋 Time Color Legend:</strong>
                        <span class="legend-item punch-early">Before 8:00 AM</span>
                        <span class="legend-item punch-morning">8:00 AM - 12:00 PM</span>
                        <span class="legend-item punch-afternoon">After 12:00 PM</span>
                        <span class="legend-item punch-out-early">Out before 5:00 PM</span>
                        <span class="legend-item absent">Absent</span>
                    </div>

                    <form action="{{ route('staff.attendance.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        
                        <div class="col-md-6">
                            <label class="form-label">📁 Excel File</label>
                            <input type="file" name="attendance_file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">📅 Month</label>
                            <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}">
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                📤 Import Attendance
                            </button>
                        </div>
                    </form>
                    
                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            ✅ {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">
                            ❌ {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Attendance Table -->
            <div class="card mt-4">
                <div class="card-header">
                    📊 Monthly Attendance - {{ $selectedDate->format('F Y') }}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <!-- First header row: Date numbers with colspan=2 for IN/OUT -->
                                <tr>
                                    <th rowspan="2" style="width: 80px;">Staff Code</th>
                                    <th rowspan="2" style="width: 120px;">Name</th>
                                    @for($day = 1; $day <= $selectedDate->daysInMonth; $day++)
                                        @php
                                            $currentDate = $selectedDate->copy()->day($day);
                                            $isWeekend = $currentDate->isWeekend();
                                            $isToday = $currentDate->isToday();
                                            $dayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 6=Saturday
                                            
                                            // Determine styling classes
                                            $dateClasses = ['date-header'];
                                            $separatorClass = '';
                                            
                                            // Weekend styling
                                            if($isWeekend) {
                                                $dateClasses[] = 'weekend-column';
                                            }
                                            
                                            // Today styling
                                            if($isToday) {
                                                $dateClasses[] = 'today-column';
                                            }
                                            
                                            // Week separators (every Sunday)
                                            if($dayOfWeek === 0 && $day > 1) {
                                                $separatorClass = 'week-separator';
                                            }
                                            
                                            // Month sections
                                            if($day <= 7) {
                                                $dateClasses[] = 'first-week';
                                            } elseif($day >= 24) {
                                                $dateClasses[] = 'end-month';
                                            } else {
                                                $dateClasses[] = 'mid-month';
                                            }
                                            
                                            // Alternating groups for visual separation
                                            $groupNumber = ceil($day / 5);
                                            $dateClasses[] = 'date-group-' . ($groupNumber % 5 + 1);
                                        @endphp
                                        <th colspan="2" class="{{ implode(' ', $dateClasses) }} {{ $separatorClass }} day-boundary">
                                            <div style="display: flex; flex-direction: column; align-items: center;">
                                                <span style="font-size: 13px; font-weight: bold;">{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</span>
                                                <span style="font-size: 8px; opacity: 0.8;">{{ $currentDate->format('D') }}</span>
                                                @if($isToday)
                                                    <span style="font-size: 10px;">⭐</span>
                                                @endif
                                            </div>
                                        </th>
                                    @endfor
                                </tr>
                                
                                <!-- Second header row: IN/OUT labels -->
                                <tr>
                                    @for($day = 1; $day <= $selectedDate->daysInMonth; $day++)
                                        @php
                                            $currentDate = $selectedDate->copy()->day($day);
                                            $isWeekend = $currentDate->isWeekend();
                                            
                                            $inClasses = ['punch-subheader'];
                                            $outClasses = ['punch-subheader', 'out-column'];
                                            
                                            if($isWeekend) {
                                                $inClasses[] = 'weekend-column';
                                                $outClasses[] = 'weekend-column';
                                            }
                                            
                                            // Alternating groups
                                            $groupNumber = ceil($day / 5);
                                            $inClasses[] = 'date-group-' . ($groupNumber % 5 + 1);
                                            $outClasses[] = 'date-group-' . ($groupNumber % 5 + 1);
                                        @endphp
                                        <th class="{{ implode(' ', $inClasses) }}">
                                            <span style="font-weight: bold; color: #10b981;">→ IN</span>
                                        </th>
                                        <th class="{{ implode(' ', $outClasses) }} day-boundary">
                                            <span style="font-weight: bold; color: #ef4444;">← OUT</span>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staff as $member)
                                    <tr class="staff-row">
                                        <td class="staff-info">
                                            <strong>{{ $member->staff_code }}</strong>
                                        </td>
                                        <td class="staff-info">
                                            {{ $member->name }}
                                        </td>
                                        
                                        @for($day = 1; $day <= $selectedDate->daysInMonth; $day++)
                                            @php
                                                // Get attendance for this specific day
                                                $attendanceKey = $member->id . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                                $dayData = null;
                                                $inTime = null;
                                                $outTime = null;
                                                
                                                if($attendances->has($attendanceKey)) {
                                                    $dayData = $attendances->get($attendanceKey)->first();
                                                    
                                                    // Get in_time and out_time from the processed data
                                                    $inTime = $dayData['in_time'] ?? null;
                                                    $outTime = $dayData['out_time'] ?? null;
                                                    
                                                    // Fallback: if in_time/out_time don't exist, parse from raw_data
                                                    if (!$inTime && !$outTime && !empty($dayData['raw_data'])) {
                                                        $times = array_filter(explode(' ', trim($dayData['raw_data'])));
                                                        $inTime = isset($times[0]) ? trim($times[0]) : null;
                                                        $outTime = isset($times[1]) ? trim($times[1]) : null;
                                                    }
                                                }
                                                
                                                // Styling classes for visual separation
                                                $currentDate = $selectedDate->copy()->day($day);
                                                $isWeekend = $currentDate->isWeekend();
                                                $isToday = $currentDate->isToday();
                                                
                                                $cellClasses = ['time-cell'];
                                                
                                                if($isWeekend) {
                                                    $cellClasses[] = 'weekend-column';
                                                }
                                                
                                                if($isToday) {
                                                    $cellClasses[] = 'today-column';
                                                }
                                                
                                                // Alternating day styling
                                                if($day % 2 === 1) {
                                                    $cellClasses[] = 'odd-day';
                                                } else {
                                                    $cellClasses[] = 'even-day';
                                                }
                                                
                                                // Group styling
                                                $groupNumber = ceil($day / 5);
                                                $cellClasses[] = 'date-group-' . ($groupNumber % 5 + 1);
                                            @endphp
                                            
                                            <!-- IN Column -->
                                            <td class="{{ implode(' ', $cellClasses) }}
                                                @if($inTime)
                                                    @php
                                                        $hour = (int)substr($inTime, 0, 2);
                                                        if($hour < 8) {
                                                            echo ' punch-early';
                                                        } elseif($hour >= 8 && $hour < 12) {
                                                            echo ' punch-morning';
                                                        } else {
                                                            echo ' punch-afternoon';
                                                        }
                                                    @endphp
                                                @elseif(!$dayData)
                                                    absent
                                                @endif
                                            ">
                                                @if($inTime)
                                                    <strong>{{ $inTime }}</strong>
                                                @elseif(!$dayData)
                                                    Absent
                                                @endif
                                            </td>
                                            
                                            <!-- OUT Column -->
                                            <td class="{{ implode(' ', $cellClasses) }} day-boundary
                                                @if($outTime)
                                                    @php
                                                        $hour = (int)substr($outTime, 0, 2);
                                                        if($hour < 17) {
                                                            echo ' punch-out-early';
                                                        } else {
                                                            echo ' punch-morning';
                                                        }
                                                    @endphp
                                                @endif
                                            ">
                                                @if($outTime)
                                                    <strong>{{ $outTime }}</strong>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                    
                                    <!-- Separator row between staff members -->
                                    <tr class="staff-separator">
                                        <td colspan="{{ ($selectedDate->daysInMonth * 2) + 2 }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection