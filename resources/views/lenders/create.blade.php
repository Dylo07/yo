@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Lender</h1>
    
    <form action="{{ route('lenders.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="nic_number" class="form-label">NIC Number <span class="text-danger">*</span></label>
            <input type="text" name="nic_number" class="form-control" value="{{ old('nic_number') }}" required>
            @error('nic_number')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="bill_number" class="form-label">Bill Number</label>
            <input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}">
            @error('bill_number')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" name="amount" step="0.01" class="form-control" value="{{ old('amount') }}">
            @error('amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') }}">
            @error('date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Create</button>
        <a href="{{ route('lenders.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection