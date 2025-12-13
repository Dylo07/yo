@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Manage Staff Categories</h3>
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

            <form action="{{ route('attendance.manual.bulk-update-categories') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Person ID</th>
                                <th>Staff Name</th>
                                <th>Current Category</th>
                                <th>New Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staff as $member)
                                <tr>
                                    <td>{{ $member->id }}</td>
                                    <td>{{ $member->name }}</td>
                                    <td>
                                        @if($member->staffCategory)
                                            <span class="badge badge-info">
                                                {{ ucfirst(str_replace('_', ' ', $member->staffCategory->category)) }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="categories[{{ $member->id }}]" class="form-control">
                                            <option value="">-- Select Category --</option>
                                            @foreach($categories as $value => $name)
                                                <option value="{{ $value }}" {{ $member->staffCategory && $member->staffCategory->category == $value ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Categories</button>
                </div>
            </form>
            
            <!-- Manage Category Types Section -->
            <div class="mt-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-tags"></i> Manage Category Types</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add New Category Form -->
                        <div class="mb-4">
                            <h6>Add New Category</h6>
                            <form action="{{ route('attendance.manual.store-category-type') }}" method="POST" class="row g-3">
                                @csrf
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control" placeholder="Category Name (e.g., Security)" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="slug" class="form-control" placeholder="Slug (e.g., security)" required pattern="[a-z_]+" title="Lowercase letters and underscores only">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Add Category</button>
                                </div>
                            </form>
                        </div>

                        <!-- Existing Categories Table -->
                        <h6>Existing Categories</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Staff Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryTypes as $category)
                                        @php
                                            $staffCount = \App\Models\StaffCategory::where('category', $category->slug)->count();
                                        @endphp
                                        <tr>
                                            <td>{{ $category->sort_order }}</td>
                                            <td>
                                                <form action="{{ route('attendance.manual.update-category-type', $category->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm d-inline-block" style="width: 150px;">
                                                    <input type="hidden" name="sort_order" value="{{ $category->sort_order }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
                                                </form>
                                            </td>
                                            <td><code>{{ $category->slug }}</code></td>
                                            <td>
                                                <span class="badge {{ $staffCount > 0 ? 'badge-info' : 'badge-secondary' }}">{{ $staffCount }} staff</span>
                                            </td>
                                            <td>
                                                @if($staffCount == 0)
                                                    <form action="{{ route('attendance.manual.delete-category-type', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <span class="text-muted" title="Cannot delete - staff assigned"><i class="fas fa-lock"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Note: Categories with staff assigned cannot be deleted. Reassign staff first.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection