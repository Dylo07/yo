@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Hotel Expenses</h1>
    <div class="mb-3">
        <a href="{{ route('costs.create') }}" class="btn btn-primary">Add New Expense</a>
        <a href="{{ route('groups.create') }}" class="btn btn-info">Add New Category</a>
        <a href="{{ route('persons.create') }}" class="btn btn-secondary">Add New Person/Shop</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Month Selector -->
    <form action="{{ route('costs.index') }}" method="GET" class="mb-3">
        <div class="form-group row">
            <label for="month" class="col-form-label col-sm-2">Select Month</label>
            <div class="col-sm-4">
                <input type="month" name="month" id="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Summary of Expenses of the Month -->
    <h3>Summary of Expenses of the Month</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Category</th>
                <th>Person/Shop</th>
                <th>Expense</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthlyGroupedCosts as $group => $persons)
                <tr>
                    <td colspan="4"><strong>{{ $group }}</strong></td>
                </tr>
                @foreach ($persons as $person => $data)
                    <tr>
                        <td></td>
                        <td colspan="3"><strong>{{ $person }}</strong></td>
                    </tr>
                    @foreach ($data['costs'] as $cost)
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($cost->amount, 2) }}</td>
                            <td>{{ $cost->cost_date }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td colspan="2" class="text-end"><strong>Total for {{ $person }}</strong></td>
                        <td><strong>{{ number_format($data['total'], 2) }}</strong></td>
                    </tr>
                @endforeach
            @endforeach
            <tr>
                <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                <td><strong>{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Summary of Expenses of the Day -->
    <h3>Summary of Expenses of the Day</h3>

    <!-- Date Selector -->
    <form action="{{ route('costs.index') }}" method="GET" class="mb-3">
        <div class="form-group row">
            <label for="date" class="col-form-label col-sm-2">Select Date</label>
            <div class="col-sm-4">
                <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}">
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    @if ($dailyGroupedCosts->isEmpty())
        <p>No expenses found for the selected date.</p>
    @else
        @foreach ($dailyGroupedCosts as $group => $persons)
            <h5><strong>{{ $group }}</strong></h5>
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th>Person/Shop</th>
                        <th>Expense</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($persons as $person => $data)
                        <tr>
                            <td colspan="2"><strong>{{ $person }}</strong></td>
                        </tr>
                        @foreach ($data['costs'] as $cost)
                            <tr>
                                <td></td>
                                <td>{{ number_format($cost->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-end"><strong>Total for {{ $person }}</strong></td>
                            <td><strong>{{ number_format($data['total'], 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    <!-- Log Details -->
    <h3>Stock Log Details</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Category</th>
                <th>Person/Shop</th>
                <th>Expense</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logDetails as $log)
                <tr>
                    <td>{{ $log['date'] }}</td>
                    <td>{{ $log['user'] }}</td>
                    <td>{{ $log['category'] }}</td>
                    <td>{{ $log['person_shop'] }}</td>
                    <td>{{ number_format($log['expense'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No log details available for the selected date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
