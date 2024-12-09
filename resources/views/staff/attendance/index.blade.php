@extends('layouts.app')

@section('content')
<div class="container">
   <div class="row justify-content-center">
       <div class="col-md-12">
           <!-- Current Day Attendance Card -->
           <div class="card mb-4">
               <div class="card-header">
                   Staff Attendance - {{ date('d M Y') }}
                   <a href="{{ route('staff.attendance.report') }}" class="btn btn-info float-right">View Report</a>
               </div>

               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-bordered">
                           <thead>
                               <tr>
                                   <th>Staff Code</th>
                                   <th>Name</th>
                                   <th>Check In</th>
                                   <th>Check Out</th>
                                   <th>Status</th>
                                   <th>Action</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($staff as $member)
                               <tr>
                                   <td>{{ $member->staff_code }}</td>
                                   <td>{{ $member->name }}</td>
                                   <td>
                                       {{ isset($attendances[$member->id]) ? $attendances[$member->id]->check_in : '-' }}
                                   </td>
                                   <td>
                                       {{ isset($attendances[$member->id]) ? $attendances[$member->id]->check_out : '-' }}
                                   </td>
                                   <td>
                                       @if(isset($attendances[$member->id]))
                                           <span class="badge badge-{{ $attendances[$member->id]->status == 'present' ? 'success' : 'warning' }}">
                                               {{ $attendances[$member->id]->status }}
                                           </span>
                                       @else
                                           <span class="badge badge-secondary">Not Marked</span>
                                       @endif
                                   </td>
                                   <td>
                                       @if(!isset($attendances[$member->id]))
                                           <button class="btn btn-primary btn-sm mark-attendance" 
                                               data-staff-id="{{ $member->id }}">
                                               Mark Check In
                                           </button>
                                       @elseif(!isset($attendances[$member->id]->check_out))
                                           <button class="btn btn-info btn-sm mark-checkout" 
                                               data-staff-id="{{ $member->id }}">
                                               Mark Check Out
                                           </button>
                                       @endif
                                   </td>
                               </tr>
                               @endforeach
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>

           <!-- Punch History Card -->
           <div class="card">
               <div class="card-header">
                   Recent Punch History
               </div>
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-bordered">
                           <thead>
                               <tr>
                                   <th>Date</th>
                                   <th>Staff Code</th>
                                   <th>Name</th>
                                   <th>Device ID</th>
                                   <th>Check In</th>
                                   <th>Check Out</th>
                                   <th>Status</th>
                               </tr>
                           </thead>
                           <tbody>
                               @forelse($punchHistory as $punch)
                               <tr>
                                   <td>{{ $punch->date }}</td>
                                   <td>{{ $punch->staff->staff_code }}</td>
                                   <td>{{ $punch->staff->name }}</td>
                                   <td>{{ $punch->device_id ?? 'Manual' }}</td>
                                   <td>{{ $punch->check_in }}</td>
                                   <td>{{ $punch->check_out ?? '-' }}</td>
                                   <td>
                                       <span class="badge badge-{{ $punch->status == 'present' ? 'success' : 'warning' }}">
                                           {{ $punch->status }}
                                       </span>
                                   </td>
                               </tr>
                               @empty
                               <tr>
                                   <td colspan="7" class="text-center">No punch history found</td>
                               </tr>
                               @endforelse
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
       </div>
   </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
   $.ajaxSetup({
       headers: {
           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
   });

   $('.mark-attendance').on('click', function(e) {
       e.preventDefault();
       let button = $(this);
       let staffId = button.data('staff-id');
       let currentTime = new Date().toTimeString().slice(0,5);

       $.ajax({
           url: '{{ route("staff.attendance.store") }}',
           type: 'POST',
           data: {
               staff_id: staffId,
               check_in: currentTime,
               date: '{{ $currentDate }}'
           },
           success: function(response) {
               location.reload();
           },
           error: function(xhr, status, error) {
               console.error('Error:', error);
               alert('Error marking attendance. Please try again.');
           }
       });
   });

   $('.mark-checkout').on('click', function(e) {
       e.preventDefault();
       let button = $(this);
       let staffId = button.data('staff-id');
       let currentTime = new Date().toTimeString().slice(0,5);

       $.ajax({
           url: '{{ route("staff.attendance.checkout") }}',
           type: 'POST',
           data: {
               staff_id: staffId,
               check_out: currentTime,
               date: '{{ $currentDate }}'
           },
           success: function(response) {
               location.reload();
           },
           error: function(xhr, status, error) {
               console.error('Error:', error);
               alert('Error marking checkout. Please try again.');
           }
       });
   });
});
</script>
@endpush
@endsection