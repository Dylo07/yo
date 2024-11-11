@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Stock Management</h2>

    <!-- Form to Add New Category -->
    <form action="{{ route('categories.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="category_name" class="form-label">Add New Category</label>
            <input type="text" name="name" id="category_name" class="form-control" placeholder="Enter category name" required>
        </div>
        <button type="submit" class="btn btn-success">Add Category</button>
    </form>

    <!-- Form to Add New Item -->
    <form action="{{ route('items.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="item_name" class="form-label">Add New Item</label>
            <input type="text" name="name" id="item_name" class="form-control" placeholder="Enter item name" required>
        </div>
        <div class="mb-3">
            <label for="item_category" class="form-label">Select Category</label>
            <select name="group_id" id="item_category" class="form-select" required>
                <option value="">Select a category</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Add Item</button>
    </form>

    <!-- Stock Update Section -->
    <h3>Update Today’s Stock</h3>
    <form action="{{ route('stock.update') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="item" class="form-label">Item</label>
            <select name="item_id" id="item" class="form-select" required>
                @foreach($groups as $group)
                    @if($group->items->isNotEmpty())
                        <optgroup label="{{ $group->name }}">
                            @foreach($group->items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <optgroup label="{{ $group->name }}">
                            <option disabled>No items available</option>
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
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

    <!-- Stock Overview Section -->
    <h3 class="mt-5">Stock Overview for {{ DateTime::createFromFormat('!m', $currentMonth)->format('F') }} {{ $currentYear }}</h3>

    <!-- Category Selection Dropdown -->
    <form action="{{ route('stock.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="category_filter" class="form-label">Select Category</label>
                <select name="category_id" id="category_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
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

    <!-- Stock Table -->
    @foreach($groups as $group)
        @if(!request('category_id') || request('category_id') == $group->id)
            <h4>{{ $group->name }}</h4>
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
                    @foreach($group->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            @for($i = 1; $i <= 31; $i++)
                                @php
                                    $currentDate = now()->toDateString();
                                    $date = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                                    // Get stock for this date
                                    $inventory = $item->inventory->firstWhere('stock_date', $date);

                                    // If no stock exists for this date, propagate the most recent stock
                                    if (!$inventory) {
                                        $previousInventory = $item->inventory->where('stock_date', '<', $date)->sortByDesc('stock_date')->first();
                                        $displayStock = $previousInventory ? $previousInventory->stock_level : '-';
                                    } else {
                                        $displayStock = $inventory->stock_level;
                                    }
                                @endphp
                                <td>
                                    @if($date <= $currentDate)
                                        {{ $displayStock }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <!-- Log Details Section -->
    <h3 class="mt-5">Stock Log Details</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Item</th>
                <th>Action</th>
                <th>Quantity</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $log->item->name }}</td>
                    <td>{{ ucfirst($log->action) }}</td>
                    <td>{{ $log->quantity }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
