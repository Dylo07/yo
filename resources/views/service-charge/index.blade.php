@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Service Charge Processing</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('service-charge.points') }}" class="btn btn-light btn-sm">
                                Manage Points
                            </a>
                            <select id="month" class="form-select form-select-sm bg-dark text-white border-secondary" 
                                    style="width: auto; min-width: 200px;">
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}" {{ ($monthParam ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Previous Month's Total S/C</h6>
                                    <h4 class="text-primary">Rs. {{ number_format($prevMonthSales, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Current Month's Damages</h6>
                                    <h4 class="text-danger">Rs. {{ number_format($currentMonthDamages, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Balance S/C</h6>
                                    <h4 class="text-success">Rs. {{ number_format($totalGivingSC, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Points</th>
                                    <th>Ratio</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $index => $employee)
                                @php
                                    $employeePoints = $points->where('person_id', $employee->id)->first();
                                    $pointValue = $employeePoints ? $employeePoints->points : 0;
                                    $ratio = $totalPoints > 0 ? ($pointValue / $totalPoints) : 0;
                                    $amount = $totalGivingSC * $ratio;
                                    $existingServiceCharge = $serviceCharges->where('person_id', $employee->id)->first();
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td class="text-center">{{ $pointValue }}</td>
                                    <td class="text-center">{{ number_format($ratio * 100, 2) }}%</td>
                                    <td class="text-end">Rs. {{ number_format($amount, 2) }}</td>
                                    <td class="text-center">
                                        @if(!$existingServiceCharge)
                                            @if($pointValue > 0)
                                                <button class="btn btn-primary btn-sm generate-sc"
                                                        data-person="{{ $employee->id }}"
                                                        data-name="{{ $employee->name }}">
                                                    Generate S/C
                                                </button>
                                            @else
                                                <span class="text-muted">No Points</span>
                                            @endif
                                        @else
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('service-charge.print', $existingServiceCharge->id) }}"
                                                   class="btn btn-info btn-sm">
                                                    Print
                                                </a>
                                                <span class="badge bg-success">Generated</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No staff members found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($staff->count() > 0)
                            <tfoot class="bg-dark text-white">
                                <tr>
                                    <th class="text-center">Total</th>
                                    <th>-</th>
                                    <th class="text-center">{{ $totalPoints }}</th>
                                    <th class="text-center">100%</th>
                                    <th class="text-end">Rs. {{ number_format($totalGivingSC, 2) }}</th>
                                    <th class="text-center">-</th>
                                </tr>
                            </tfoot>
                            @endif
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
    // Generate service charge
    $('.generate-sc').click(function() {
        const personId = $(this).data('person');
        const personName = $(this).data('name');
        const month = $('#month').val();
        const button = $(this);

        if (!confirm(`Generate service charge for ${personName}?`)) {
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url: '{{ route("service-charge.generate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                person_id: personId,
                month: month
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                    button.prop('disabled', false).html('Generate S/C');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error generating service charge';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
                button.prop('disabled', false).html('Generate S/C');
            }
        });
    });

    // Month change
    $('#month').change(function() {
        const selectedMonth = $(this).val();
        window.location.href = '{{ route("service-charge.index") }}?month=' + selectedMonth;
    });
});
</script>
@endpush
@endsection