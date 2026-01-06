@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><u>Welfare Funds</u></h4>
                        <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Current Balance Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
                <div class="card-body text-center py-5">
                    <h5 class="text-white-50 mb-2">Total Balance</h5>
                    <h1 class="display-3 text-white fw-bold mb-0">
                        Rs {{ number_format($currentBalance, 2) }}
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Add and Deduct Forms -->
    <div class="row mb-4">
        <!-- Add Amount Form -->
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Amount</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('welfare-fund.add') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="add_amount" class="form-label fw-bold">Amount (Rs)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" 
                                   id="add_amount" name="amount" placeholder="Enter amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label fw-bold">Description</label>
                            <input type="text" class="form-control" id="add_description" name="description" 
                                   placeholder="e.g., Sold waste items, Tree fruits" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Deduct Amount Form -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-minus-circle"></i> Remove Amount</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('welfare-fund.deduct') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="deduct_amount" class="form-label fw-bold">Amount (Rs)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" 
                                   id="deduct_amount" name="amount" placeholder="Enter amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="deduct_description" class="form-label fw-bold">Description</label>
                            <input type="text" class="form-control" id="deduct_description" name="description" 
                                   placeholder="e.g., Staff welfare expense" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg w-100">
                            <i class="fas fa-minus"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><u>Summary of Month</u></h5>
                        <form action="{{ route('welfare-fund.index') }}" method="GET" class="d-flex align-items-center">
                            <select name="month" class="form-select form-select-sm me-2" style="width: auto;" onchange="this.form.submit()">
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h6 class="text-success mb-1">Total Added</h6>
                                <h3 class="text-success fw-bold mb-0">Rs {{ number_format($monthlyAdded, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h6 class="text-danger mb-1">Total Deducted</h6>
                                <h3 class="text-danger fw-bold mb-0">Rs {{ number_format($monthlyDeducted, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-{{ $monthlyNet >= 0 ? 'primary' : 'warning' }} bg-opacity-10 rounded">
                                <h6 class="text-{{ $monthlyNet >= 0 ? 'primary' : 'warning' }} mb-1">Net Change</h6>
                                <h3 class="text-{{ $monthlyNet >= 0 ? 'primary' : 'warning' }} fw-bold mb-0">
                                    {{ $monthlyNet >= 0 ? '+' : '' }}Rs {{ number_format($monthlyNet, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Details -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <h5 class="mb-0"><u>Log Details</u></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>
                                            @if($log->type == 'add')
                                                <span class="badge bg-success">Added</span>
                                            @else
                                                <span class="badge bg-danger">Deducted</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold {{ $log->type == 'add' ? 'text-success' : 'text-danger' }}">
                                            {{ $log->type == 'add' ? '+' : '-' }}Rs {{ number_format($log->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No transactions yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                    <div class="card-footer">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
