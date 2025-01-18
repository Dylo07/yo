@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Staff Manual Attendance</h3>
                <div>
                    <a href="{{ route('attendance.manual.report') }}" class="btn btn-info">View Report</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="alert-container"></div>

            @if(Auth::user()->checkAdmin())
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> As an admin, you can mark attendance for previous dates.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Attendance Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $member)
                            <tr id="staff-row-{{ $member->id }}">
                                <td>{{ $member->name }}</td>
                                <td id="status-cell-{{ $member->id }}">
                                    @if(isset($attendances[$member->id]))
                                        <span class="badge badge-{{ $attendances[$member->id]->status == 'present' ? 'success' : ($attendances[$member->id]->status == 'half' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($attendances[$member->id]->status) }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Not Marked</span>
                                    @endif
                                </td>
                                <td id="remarks-cell-{{ $member->id }}">
                                    {{ isset($attendances[$member->id]) ? $attendances[$member->id]->remarks : '-' }}
                                </td>
                                <td>
                                    <div class="input-group">
                                    @if(Auth::user()->checkAdmin())
    <input type="date" 
           class="form-control form-control-sm attendance-date" 
           id="date-{{ $member->id }}"
           value="{{ Carbon\Carbon::now()->format('Y-m-d') }}" 
           max="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
@else
    <input type="hidden" 
           id="date-{{ $member->id }}" 
           value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
@endif
                                        <input type="text" class="form-control form-control-sm remarks-input" 
                                            id="remarks-{{ $member->id }}" placeholder="Remarks">
                                        <div class="input-group-append">
                                            <button onclick="markAttendance({{ $member->id }}, 'present')" 
                                                class="btn btn-sm btn-success">Present</button>
                                            <button onclick="markAttendance({{ $member->id }}, 'half')" 
                                                class="btn btn-sm btn-warning">Half Day</button>
                                            <button onclick="markAttendance({{ $member->id }}, 'absent')" 
                                                class="btn btn-sm btn-danger">Absent</button>
                                        </div>
                                    </div>
                                </td>
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
    .btn-sm { padding: 0.25rem 0.5rem; }
    .badge { font-size: 90%; }
    .form-control-sm { height: calc(1.5em + 0.5rem + 2px); }
    .alert { margin-bottom: 1rem; }
</style>
@endpush

@push('scripts')
<script>
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    `;
    alertContainer.innerHTML = alert;
    setTimeout(() => {
        const alertElement = alertContainer.firstChild;
        if (alertElement) alertElement.remove();
    }, 3000);
}

async function markAttendance(personId, status) {
    const dateInput = document.getElementById(`date-${personId}`);
    const remarksInput = document.getElementById(`remarks-${personId}`);
    const statusCell = document.getElementById(`status-cell-${personId}`);
    const remarksCell = document.getElementById(`remarks-cell-${personId}`);

    try {
        const response = await fetch('{{ route("attendance.manual.mark") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                person_id: personId,
                status: status,
                remarks: remarksInput.value,
                attendance_date: dateInput.value // This should now properly pass to the controller
            })
        });

        const data = await response.json();

        if (response.status === 403) {
            showAlert(data.message, 'danger');
            return;
        }

        if (data.success) {
            statusCell.innerHTML = data.status_badge;
            remarksCell.textContent = remarksInput.value || '-';
            showAlert(data.message, 'success');
            remarksInput.value = '';
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while marking attendance', 'danger');
    }
}
</script>
@endpush
@endsection