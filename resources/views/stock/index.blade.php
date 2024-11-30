@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Grocery Store Stock</h2>



     <!-- Form to Add New Category -->
     <form action="{{ route('categories.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="category_name" class="form-label">Add New Category</label>
            <input type="text" name="name" id="category_name" class="form-control" placeholder="Enter category name" required>
        </div>
        <button type="submit" class="btn btn-success">Add Category</button>
    </form>

    



    <!-- Show only the selected category -->
    @php
        $selectedGroup = request('category_id') ? $groups->firstWhere('id', request('category_id')) : null;
    @endphp

    <!-- Category Selection Dropdown -->
    <form action="{{ route('stock.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="category_filter" class="form-label">Select Category</label>
                <select name="category_id" id="category_filter" class="form-select" onchange="this.form.submit()" required>
                    <option value="">Please select a category</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('category_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <input type="hidden" name="year" value="{{ $currentYear }}">
        </div>
    </form>

    @if($selectedGroup)
        <!-- Stock Table for Selected Category -->
        <h4>{{ $selectedGroup->name }}</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    @for($i = 1; $i <= 31; $i++)
                        <th>{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($selectedGroup->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        @for($i = 1; $i <= 31; $i++)
                            @php
                                $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
                                $inventory = $item->inventory->firstWhere('stock_date', $date);
                                
                                if ($inventory) {
                                    $displayStock = $inventory->stock_level;
                                } else {
                                    $previousInventory = $item->inventory
                                        ->where('stock_date', '<', $date)
                                        ->sortByDesc('stock_date')
                                        ->first();
                                    
                                    if ($i === 1 && !$inventory && $previousInventory) {
                                        $displayStock = $previousInventory->stock_level;
                                    } else {
                                        $displayStock = $previousInventory ? $previousInventory->stock_level : '-';
                                    }
                                }
                                
                                $stockLogs = $logs->where('item_id', $item->id)
                                                 ->filter(function($log) use ($date) {
                                                     return $log->created_at->format('Y-m-d') === $date;
                                                 });
                                
                                $additions = $stockLogs->where('action', 'add')->sum('quantity');
                                $removals = $stockLogs->where('action', 'remove')->sum('quantity');
                            @endphp
                            <td>
                                @if($date <= now()->toDateString())
                                    <div>{{ $displayStock }}</div>
                                    @if($additions)
                                        <div class="text-success">+{{ $additions }}</div>
                                    @endif
                                    @if($removals)
                                        <div class="text-danger">-{{ $removals }}</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Stock Update Section -->
        <h3>Update Stock</h3>
        <form action="{{ route('stock.update') }}" method="POST" class="mb-4">
            @csrf
            <div class="mb-3">
                <label for="item" class="form-label">Item</label>
                <select name="item_id" id="item" class="form-select" required>
                    @foreach($selectedGroup->items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" name="description" id="description" class="form-control" placeholder="Enter a description" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="action" value="add" class="btn btn-success">Add</button>
                <button type="submit" name="action" value="remove" class="btn btn-danger">Remove</button>
            </div>
        </form>

        <!-- Log Details Section with Date Picker -->
        <h3 class="mt-5">Stock Log Details</h3>
        <form action="{{ route('stock.index') }}" method="GET" class="mb-3">
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="log_date" class="form-label">Select Date</label>
                    <input type="date" id="log_date" name="log_date" class="form-control" 
                           value="{{ request('log_date', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}"
                           onchange="this.form.submit()">
                </div>
            </div>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Item</th>
                    <th>Action</th>
                    <th>Quantity</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $selectedDate = request('log_date', now()->toDateString());
                    $dayLogs = $logs->filter(function($log) use ($selectedDate) {
                        return $log->created_at->format('Y-m-d') === $selectedDate;
                    });
                @endphp
                @foreach($dayLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('H:i') }}</td>
                        <td>{{ $log->user->name }}</td>
                        <td>{{ $log->item->name }}</td>
                        <td>{{ ucfirst($log->action) }}</td>
                        <td>{{ $log->quantity }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @endforeach
                @if($dayLogs->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center">No stock movements on this date</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <div class="alert alert-info">
            Please select a category to view stock details
        </div>
    @endif
</div>

<style>
td {
    padding: 8px !important;
    font-size: 0.9em;
}
td div {
    line-height: 1.2;
}
.text-success {
    color: #28a745 !important;
    font-weight: bold;
}
.text-danger {
    color: #dc3545 !important;
    font-weight: bold;
}
</style>
@endsection