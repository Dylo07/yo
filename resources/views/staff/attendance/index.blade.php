@extends('layouts.app')

<style>
    .table td {
        white-space: nowrap;
        font-size: 12px;
        padding: 6px !important;
        text-align: center;
        border: 1px solid #e0e0e0;
    }
    .table th {
        white-space: nowrap;
        font-size: 12px;
        padding: 8px !important;
        text-align: center;
        background-color: #f3f4f6;
        border: 1px solid #e0e0e0;
        font-weight: bold;
    }
    .staff-info {
        text-align: left;
        font-weight: bold;
        vertical-align: middle !important;
        background-color: #f8f9fa;
        border-right: 2px solid #dee2e6 !important;
    }
    .staff-row {
        border-top: 2px solid #dee2e6;
    }
    .staff-separator {
        background-color: #e9ecef;
        height: 10px;
    }
    .time-cell {
        font-family: monospace;
        color: #333;
    }
    .table-responsive {
        overflow-x: auto;
        margin-top: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: bold;
        padding: 12px 20px;
    }
    .morning-time {
        color: #2563eb;
    }
    .evening-time {
        color: #dc2626;
    }
    .absent {
        color: #6c757d; /* Grey for absent */
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Attendance Manager -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Attendance Manager</span>
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
                <div class="card-body">
                    <form action="{{ route('staff.attendance.import') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <input type="hidden" name="month" value="{{ request('month', now()->format('Y-m')) }}">
                        
                        <div class="col-md-6">
                            <label class="form-label">Excel File</label>
                            <input type="file" name="attendance_file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                Import Attendance
                            </button>
                        </div>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Attendance Table -->
            <div class="card mt-4">
                <div class="card-header">
                    Monthly Attendance - {{ request('month', now()->format('F Y')) }}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Staff Code</th>
                                    <th style="width: 150px;">Name</th>
                                    @for($i = 1; $i <= $selectedDate->daysInMonth; $i++)
                                        <th>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staff as $member)
                                    @php
                                        $maxRows = 1;
                                        foreach(range(1, $selectedDate->daysInMonth) as $i) {
                                            $key = $member->id . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                            if($attendances->has($key)) {
                                                $times = array_filter(explode(' ', $attendances->get($key)->first()->raw_data));
                                                $maxRows = max($maxRows, count($times));
                                            }
                                        }
                                    @endphp

                                    @for($row = 0; $row < $maxRows; $row++)
                                        <tr class="{{ $row === 0 ? 'staff-row' : '' }}">
                                            @if($row === 0)
                                                <td rowspan="{{ $maxRows }}" class="staff-info">{{ $member->staff_code }}</td>
                                                <td rowspan="{{ $maxRows }}" class="staff-info">{{ $member->name }}</td>
                                            @endif
                                            @for($i = 1; $i <= $selectedDate->daysInMonth; $i++)
                                                @php
                                                    $key = $member->id . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                                    if($row === 0 && !$attendances->has($key)) {
                                                        echo "<td class='time-cell'><span class='absent'>Absent</span></td>";
                                                    } elseif($attendances->has($key)) {
                                                        $times = array_filter(explode(' ', $attendances->get($key)->first()->raw_data));
                                                        if(isset($times[$row])) {
                                                            $time = trim($times[$row]);
                                                            $hour = (int)substr($time, 0, 2);
                                                            echo "<td class='time-cell'><span class='" . ($hour < 12 ? 'morning-time' : 'evening-time') . "'>$time</span></td>";
                                                        } else {
                                                            echo "<td class='time-cell'></td>";
                                                        }
                                                    } else {
                                                        echo "<td class='time-cell'></td>";
                                                    }
                                                @endphp
                                            @endfor
                                        </tr>
                                    @endfor
                                    <tr class="staff-separator">
                                        <td colspan="{{ $selectedDate->daysInMonth + 2 }}"></td>
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
