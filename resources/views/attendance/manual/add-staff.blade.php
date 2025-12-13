@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Add Staff Member</h3>
                <div>
                    <a href="{{ route('attendance.manual.index') }}" class="btn btn-secondary">Back to Attendance</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('attendance.manual.add-staff') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="person_id">Select Person:</label>
                    <select name="person_id" id="person_id" class="form-control" required>
                        <option value="">-- Select Person --</option>
                        @foreach($availablePersons as $person)
                            <option value="{{ $person->id }}">{{ $person->id }} - {{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="staff_code">Staff Code:</label>
                    <input type="text" class="form-control" id="staff_code" name="staff_code" 
                           placeholder="e.g. EMP001" required>
                </div>
                
                <div class="form-group">
                    <label for="staff_category">Staff Category:</label>
                    <select name="staff_category" id="staff_category" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        @foreach(\App\Models\CategoryType::getActiveCategories() as $category)
                            <option value="{{ $category->slug }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Staff Member</button>
            </form>
        </div>
    </div>

    <!-- Remove Staff Section -->
    <div class="card mt-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-user-minus"></i> Remove Staff from Attendance</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Select a staff member to remove from the attendance system. This will deactivate their staff code but keep their records.</p>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Staff Code</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeStaff as $staff)
                            <tr>
                                <td>{{ $staff->id }}</td>
                                <td>{{ $staff->name }}</td>
                                <td><code>{{ $staff->staffCode->staff_code ?? 'N/A' }}</code></td>
                                <td>
                                    @if($staff->staffCategory)
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $staff->staffCategory->category)) }}</span>
                                    @else
                                        <span class="badge badge-secondary">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('attendance.manual.remove-staff') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove {{ $staff->name }} from attendance?');">
                                        @csrf
                                        <input type="hidden" name="person_id" value="{{ $staff->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No active staff members found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Note: Removing a staff member will deactivate their staff code. Their attendance records will be preserved.</small>
        </div>
    </div>
</div>
@endsection