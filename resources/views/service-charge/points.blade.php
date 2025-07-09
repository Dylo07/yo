@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Service Charge Points</h5>
                    <a href="{{ route('service-charge.index') }}" class="btn btn-light btn-sm">
                        Back to Service Charge
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('service-charge.points.update-bulk') }}" method="POST" id="pointsForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staff as $index => $employee)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td width="200">
                                            <input type="number" 
                                                   name="points[{{ $employee->id }}]" 
                                                   class="form-control" 
                                                   value="{{ $points[$employee->id]->points ?? 0 }}"
                                                   min="0"
                                                   placeholder="Enter points">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No staff members found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($staff->count() > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <th class="text-center">Total</th>
                                        <th>-</th>
                                        <th class="text-center">
                                            <span id="totalPoints">{{ $points->sum('points') }}</span> Points
                                        </th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                        @if($staff->count() > 0)
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="resetPoints()">
                                    Reset All Points
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Points
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate total points on input change
    $('input[type="number"]').on('input', function() {
        let total = 0;
        $('input[type="number"]').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        $('#totalPoints').text(total);
    });

    // Form submission with confirmation
    $('#pointsForm').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to save these points?')) {
            this.submit();
        }
    });
});

function resetPoints() {
    if (confirm('Are you sure you want to reset all points to 0?')) {
        $('input[type="number"]').val(0);
        $('#totalPoints').text(0);
    }
}
</script>
@endpush
@endsection