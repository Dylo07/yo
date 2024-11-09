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

    <!-- Form to Add Stock -->
    <form action="{{ route('stock.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="item" class="form-label">Item</label>
            <select name="item_id" id="item" class="form-select">
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
            <label for="stock_date" class="form-label">Date</label>
            <input type="date" name="stock_date" id="stock_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="stock_level" class="form-label">Stock Level</label>
            <input type="number" name="stock_level" id="stock_level" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Stock</button>
    </form>

    <!-- Form to View Stock for a Specific Month -->
    <form action="{{ route('stock.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $currentMonth == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select">
                    @for($i = now()->year; $i >= 2000; $i--)
                        <option value="{{ $i }}" {{ $currentYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">View Stock</button>
            </div>
        </div>
    </form>

    <!-- Stock Overview Section -->
    <h3 class="mt-5">Stock Overview for {{ DateTime::createFromFormat('!m', $currentMonth)->format('F') }} {{ $currentYear }}</h3>

    @foreach($groups as $group)
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
                                $inventory = $item->inventory->firstWhere('stock_date', $date);
                            @endphp
                            <td>
                                @if($date <= $currentDate)
                                    {{ $inventory->stock_level ?? 0 }}
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
@endsection
