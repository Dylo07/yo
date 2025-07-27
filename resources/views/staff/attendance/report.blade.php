@extends('layouts.app')

<style>
    .report-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #6366f1;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
    }
    
    .table-modern {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .table-modern thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        padding: 15px;
        border: none;
        text-align: center;
    }
    
    .table-modern tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .attendance-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-present {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-late {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .badge-absent {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: border-color 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .hours-display {
        font-family: 'Consolas', 'Monaco', monospace;
        font-weight: bold;
        color: #059669;
    }
    
    .staff-code {
        font-family: 'Consolas', 'Monaco', monospace;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
        color: #475569;
    }
    
    @media print {
        .filter-section, .no-print {
            display: none !important;
        }
        
        .report-card {
            background: #6366f1 !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Report Header -->
            <div class="report-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2">📊 Attendance Report</h2>
                        <p class="mb-0 opacity-75">
                            Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                            ({{ $startDate->diffInDays($endDate) + 1 }} days)
                        </p>
                    </div>
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-light">
                            🖨️ Print Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section no-print">
                <form class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">📅 Start Date</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">📅 End Date</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-filter w-100">
                            🔍 Update Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Statistics Overview -->
            <div class="stats-grid">
                @php
                    $totalStaff = count($reportData);
                    $totalPresent = array_sum(array_column($reportData, 'present'));
                    $totalLate = array_sum(array_column($reportData, 'late'));
                    $totalAbsent = array_sum(array_column($reportData, 'absent'));
                    $totalHours = array_sum(array_column($reportData, 'total_hours'));
                    $avgAttendance = $totalStaff > 0 ? round((($totalPresent + $totalLate) / ($totalStaff * ($startDate->diffInDays($endDate) + 1))) * 100, 1) : 0;
                @endphp
                
                <div class="stat-card">
                    <div class="stat-number">{{ $totalStaff }}</div>
                    <div class="stat-label">Total Staff</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">{{ $avgAttendance }}%</div>
                    <div class="stat-label">Avg Attendance</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">{{ number_format($totalHours, 1) }}</div>
                    <div class="stat-label">Total Hours</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">{{ $totalLate }}</div>
                    <div class="stat-label">Late Arrivals</div>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="table-modern">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Staff Code</th>
                            <th>Employee Name</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Total Hours</th>
                            <th>Avg Hours/Day</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $staffId => $data)
                        @php
                            $attendancePercent = $data['total_days'] > 0 ? 
                                round((($data['present'] + $data['late']) / $data['total_days']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="staff-code">{{ $data['staff_code'] ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <strong>{{ $data['name'] }}</strong>
                            </td>
                            <td>{{ $data['total_days'] }}</td>
                            <td>
                                <span class="attendance-badge badge-present">
                                    {{ $data['present'] }}
                                </span>
                            </td>
                            <td>
                                <span class="attendance-badge badge-late">
                                    {{ $data['late'] }}
                                </span>
                            </td>
                            <td>
                                <span class="attendance-badge badge-absent">
                                    {{ $data['absent'] }}
                                </span>
                            </td>
                            <td>
                                <span class="hours-display">{{ $data['total_hours'] ?? 0 }}h</span>
                            </td>
                            <td>
                                <span class="hours-display">{{ $data['average_hours'] ?? 0 }}h</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                        <div class="progress-bar" 
                                             style="width: {{ $attendancePercent }}%; 
                                                    background: {{ $attendancePercent >= 90 ? '#10b981' : ($attendancePercent >= 75 ? '#f59e0b' : '#ef4444') }};">
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="color: {{ $attendancePercent >= 90 ? '#10b981' : ($attendancePercent >= 75 ? '#f59e0b' : '#ef4444') }};">
                                        {{ $attendancePercent }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                    <p>No attendance records found for the selected period</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Report Summary -->
            <div class="mt-4 p-3" style="background: #f8fafc; border-radius: 8px; border-left: 4px solid #6366f1;">
                <small class="text-muted">
                    📝 <strong>Report Generated:</strong> {{ now()->format('M d, Y \a\t h:i A') }} | 
                    <strong>Period:</strong> {{ $startDate->diffInDays($endDate) + 1 }} days | 
                    <strong>Staff Count:</strong> {{ count($reportData) }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection