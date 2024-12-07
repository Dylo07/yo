@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Service Charge Summary</h5>
                        <select name="month" class="form-select-dark w-auto" onchange="this.form.submit()" form="month-form">
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <form id="month-form" action="{{ route('home') }}" method="GET"></form>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="alert alert-dark shadow-sm text-center">
                        <h4 class="mb-0">Total S/C for {{ Carbon\Carbon::parse($selectedMonth)->format('F Y') }}: 
                            <span class="fw-bold">Rs {{ number_format($serviceCharge, 2) }}</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-black text-white p-3">
                    <h5 class="mb-0">Dashboard</h5>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    <div class="row g-4">
                        @foreach([
                            ['route' => 'management', 'title' => 'Management', 'icon' => 'management'],
                            ['route' => 'cashier', 'title' => 'Cashier', 'icon' => 'cashier'],
                            ['route' => 'inventory', 'title' => 'Beer & Soft Drink Stock', 'icon' => 'bottle'],
                            ['route' => 'report', 'title' => 'Report', 'icon' => 'report', 'admin' => true],
                            ['url' => '/calendar', 'title' => 'Booking Calendar', 'icon' => 'calendar'],
                            ['url' => '/stock', 'title' => 'Grocery Item Store', 'icon' => 'food'],
                            ['url' => '/inv-inventory', 'title' => 'Physical Item Inventory', 'icon' => 'inv'],
                            ['url' => '/costs', 'title' => 'Daily Expense', 'icon' => 'expense'],
                            ['url' => '/tasks', 'title' => 'Daily Tasks', 'icon' => 'task'],
                            ['url' => '/rooms/availability', 'title' => 'Room Availability', 'icon' => 'room']
                        ] as $item)
                            @if(!isset($item['admin']) || (isset($item['admin']) && Auth::user()->checkAdmin()))
                                <div class="col-lg-4 col-md-6">
                                    <a href="{{ isset($item['route']) ? route($item['route']) : $item['url'] }}" 
                                       class="text-decoration-none">
                                        <div class="card h-100 menu-card">
                                            <div class="card-body text-center p-4">
                                                <img class="mb-3" width="60" src="{{ asset('image/' . $item['icon'] . '.svg') }}" alt="{{ $item['title'] }}"/>
                                                <h5 class="text-dark mb-0">{{ $item['title'] }}</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <h5 class="mb-0">Today's Booked Rooms</h5>
        <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
            <input type="date" 
                   name="date" 
                   class="form-control form-control-sm me-2 dark-input" 
                   value="{{ request('date', date('Y-m-d')) }}">
            <button type="submit" class="btn btn-sm btn-outline-light">Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Room Name</th>
                        <th>Guest In</th>
                        <th>Guest Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookedRooms as $booking)
                        <tr>
                            <td class="fw-bold">{{ $booking->room->name }}</td>
                            <td>{{ $booking->guest_in_time->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $booking->guest_out_time ? $booking->guest_out_time->format('Y-m-d H:i:s') : 'Not checked out' }}</td>
                            <td>
                                @if($booking->stay_day_count > 1)
                                    <span class="badge bg-warning text-dark">{{ $booking->stay_status }}</span>
                                @else
                                    <span class="badge bg-dark">{{ $booking->stay_status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No rooms booked for selected date
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    <div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center p-3">
        <h5 class="mb-0">Pending Tasks</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-outline-light">
            Add Task
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Date Added</th>
                        <th width="30%">Task</th>
                        <th>Category</th>
                        <th>Person Incharge</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->user }}</td>
                        <td>{{ $task->date_added }}</td>
                        <td class="task-cell">{{ $task->task }}</td>
                        <td>{{ $task->taskCategory->name }}</td>
                        <td>{{ $task->person_incharge }}</td>
                        <td>
                            @if($task->priority_order == 'High')
                                <span class="badge bg-danger">High</span>
                            @elseif($task->priority_order == 'Medium')
                                <span class="badge bg-warning text-dark">Medium</span>
                            @else
                                <span class="badge bg-success">Low</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_done" value="1">
                                <button type="submit" class="btn btn-sm btn-success">
                                    Complete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No pending tasks found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


<!-- Add this after the Pending Tasks section in resources/views/home.blade.php -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-black text-white p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0">Salary Advances</h5>
                    <select class="form-select form-select-sm bg-dark text-white border-secondary" 
                            style="width: auto; min-width: 200px;"
                            onchange="window.location.href='{{ route('home') }}?period=' + this.value">
                        @foreach($periods as $index => $period)
                            <option value="{{ $index }}" {{ $selectedPeriod == $index ? 'selected' : '' }}>
                                {{ $period['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 me-3 text-warning">Total: Rs. {{ number_format($totalAdvance, 2) }}</h6>
                <a href="{{ route('costs.create') }}" class="btn btn-sm btn-outline-light">Add Advance</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Number of Advances</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaryAdvances->groupBy('person.name') as $employeeName => $advances)
                        <tr>
                            <td><strong>{{ $employeeName }}</strong></td>
                            <td class="text-end text-danger fw-bold">Rs. {{ number_format($advances->sum('amount'), 2) }}</td>
                            <td class="text-center">{{ $advances->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No salary advances found for this period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end text-warning"><strong>Rs. {{ number_format($totalAdvance, 2) }}</strong></td>
                        <td class="text-center"><strong>{{ $salaryAdvances->count() }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</div>

<style>
.bg-black {
    background-color: #000000;
}

.form-select-dark {
    background-color: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    border-radius: 0.25rem;
}

.form-select-dark option {
    background-color: #000000;
    color: white;
}

.dark-input {
    background-color: rgba(255,255,255,0.9);
    border: 1px solid rgba(255,255,255,0.2);
}

.menu-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    border: 1px solid rgba(0,0,0,0.2);
}

.card {
    border-radius: 0.5rem;
    overflow: hidden;
}

.alert-dark {
    background-color: rgba(0,0,0,0.05);
    border-color: rgba(0,0,0,0.1);
    color: #000000;
}

.table-dark {
    background-color: #000000;
}

.task-cell {
    background-color: #ffffcc;
    font-size: 1.1em;
    font-weight: bold;
}

.table td, .table th {
    vertical-align: middle;
}
</style>
@endsection