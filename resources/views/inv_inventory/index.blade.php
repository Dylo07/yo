@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Physical Item Inventory</h2>

    <!-- Form to Add New Category -->
    <form action="{{ route('inv_inventory.categories.store') }}" method="POST" class="mb-4">
    @csrf
        <div class="mb-3">
            <label for="category_name" class="form-label">Add New Category</label>
            <input type="text" name="name" id="category_name" class="form-control" placeholder="Enter category name" required>
        </div>
        <button type="submit" class="btn btn-success">Add Category</button>
    </form>

    <!-- Form to Add New Product -->
    <form action="{{ route('inv_inventory.products.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="product_name" class="form-label">Add New Product</label>
            <input type="text" name="name" id="product_name" class="form-control" placeholder="Enter product name" required>
        </div>
        <div class="mb-3">
            <label for="product_category" class="form-label">Select Category</label>
            <select name="category_id" id="product_category" class="form-select" required>
                <option value="">Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Add Product</button>
    </form>

    <!-- Stock Update Section -->
    <h3>Update Today’s Stock</h3>
    <form action="{{ route('inv_inventory.update') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="product" class="form-label">Product</label>
            <select name="product_id" id="product" class="form-select" required>
                @foreach($categories as $category)
                    @if($category->products->isNotEmpty())
                        <optgroup label="{{ $category->name }}">
                            @foreach($category->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <optgroup label="{{ $category->name }}">
                            <option disabled>No products available</option>
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" step="0.1" min="0.1" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" class="form-control" placeholder="Enter a description" required>
        </div>
        <input type="hidden" name="action" value="add">
        <div class="d-flex gap-2">
            <button type="submit" name="action" value="add" class="btn btn-success">Add</button>
            <button type="submit" name="action" value="remove" class="btn btn-danger">Remove</button>
        </div>
    </form>

    <!-- Month and Year Selection Form -->
    <form action="{{ route('inv_inventory.index') }}" method="GET" class="mb-4">
        <div class="row">
            <!-- Category Filter -->
            <div class="col-md-3">
                <label for="category_filter" class="form-label">Select Category</label>
                <select name="category_id" id="category_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Month Selection -->
            <div class="col-md-3">
                <label for="month" class="form-label">Select Month</label>
                <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        @php
                            $monthNum = str_pad($m, 2, '0', STR_PAD_LEFT);
                            $dateObj = DateTime::createFromFormat('!m', $monthNum);
                        @endphp
                        <option value="{{ $monthNum }}" {{ $currentMonth == $monthNum ? 'selected' : '' }}>
                            {{ $dateObj->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Year Selection -->
            <div class="col-md-3">
                <label for="year" class="form-label">Select Year</label>
                <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                    @foreach(range($currentYear - 5, $currentYear + 5) as $y)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <!-- Stock Overview Section -->
    <h3 class="mt-5">Stock Overview for {{ DateTime::createFromFormat('!m', $currentMonth)->format('F') }} {{ $currentYear }}</h3>

    @php
        use Carbon\Carbon;
        $daysInMonth = Carbon::create($currentYear, $currentMonth)->daysInMonth;
    @endphp

    @foreach($categories as $category)
        @if(!request('category_id') || request('category_id') == $category->id)
            <h4>{{ $category->name }}</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            <th>{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($category->products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            @for($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
                                    $inventory = $product->inventories->firstWhere('stock_date', $date);

                                    if ($inventory) {
                                        $displayStock = $inventory->stock_level;
                                    } else {
                                        $previousInventory = $product->inventories->where('stock_date', '<', $date)->sortByDesc('stock_date')->first();
                                        $displayStock = $previousInventory ? $previousInventory->stock_level : '-';
                                    }
                                @endphp
                                <td>
                                    @if($date <= now()->toDateString())
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
    <h3 class="mt-5">Inventory Log Details</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Product</th>
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
                    <td>{{ $log->product->name }}</td>
                    <td>{{ ucfirst($log->action) }}</td>
                    <td>{{ $log->quantity }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center">
    {{ $logs->links('pagination::bootstrap-4') }}
    </div>

    <!-- Link to Monthly Stock View -->
    <a href="{{ route('inv_inventory.monthly', ['month' => $currentMonth, 'year' => $currentYear]) }}" class="btn btn-primary mt-3">
        View Monthly Stock Details
    </a>
</div>
@endsection
