@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Attendance Report</h3>
                <div>
                    <a href="{{ route('attendance.manual.index') }}" class="btn btn-primary">Back to Attendance</a>
                    <button onclick="window.print()" class="btn btn-secondary">Print Report</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filter Form -->
            <form action="{{ route('attendance.manual.report') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Staff Member</label>
                            <select name="staff_member" class="form-control">
                                <option value="">All Staff</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}" {{ request('staff_member') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Filter Report</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>Present Days</h5>
                            <h2>{{ $summary['total_present'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>Half Days</h5>
                            <h2>{{ $summary['total_half'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5>Absent Days</h5>
                            <h2>{{ $summary['total_absent'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>Total Records</h5>
                            <h2>{{ array_sum($summary) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @if(!$selectedStaffMember)
                                @foreach($staff as $member)
                                    <th>{{ $member->name }}</th>
                                @endforeach
                            @else
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Marked By</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dates as $date)
                            <tr>
                                <td>{{ $date instanceof Carbon\Carbon ? $date->format('Y-m-d') : $date }}</td>
                                @if(!$selectedStaffMember)
                                    @foreach($staff as $member)
                                        <td>
                                            @php
                                                $attendance = ($attendances[$date instanceof Carbon\Carbon ? $date->format('Y-m-d') : $date] ?? collect())
                                                    ->firstWhere('person_id', $member->id);
                                            @endphp
                                            @if($attendance)
                                                <span class="badge badge-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'half' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Not Marked</span>
                                            @endif
                                        </td>
                                    @endforeach
                                @else
                                    @php
                                        $attendance = ($attendances[$date instanceof Carbon\Carbon ? $date->format('Y-m-d') : $date] ?? collect())->first();
                                    @endphp
                                    <td>
                                        <span class="badge badge-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'half' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->remarks ?? '-' }}</td>
                                    <td>{{ $attendance->markedBy->name ?? '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .badge {
        font-size: 85%;
        padding: 5px 10px;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
        vertical-align: middle;
    }
    @media print {
        .btn, form { display: none !important; }
        .card { border: none !important; }
    }
</style>
@endpush
@endsection