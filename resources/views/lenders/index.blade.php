@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lenders List</h1>
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('lenders.create') }}" class="btn btn-primary">Add New Lender</a>
        
        <div class="btn-group">
            <a href="{{ route('lenders.index', ['filter' => 'unpaid']) }}" 
               class="btn btn-{{ $filter == 'unpaid' ? 'primary' : 'outline-primary' }}">
                Unpaid
            </a>
            <a href="{{ route('lenders.index', ['filter' => 'paid']) }}" 
               class="btn btn-{{ $filter == 'paid' ? 'primary' : 'outline-primary' }}">
                Paid
            </a>
            <a href="{{ route('lenders.index', ['filter' => 'all']) }}" 
               class="btn btn-{{ $filter == 'all' ? 'primary' : 'outline-primary' }}">
                All
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($lenders->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>NIC Number</th>
                    <th>Bill Number</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lenders as $lender)
                    <tr>
                        <td>{{ $lender->id }}</td>
                        <td>{{ $lender->name }}</td>
                        <td>{{ $lender->nic_number }}</td>
                        <td>{{ $lender->bill_number }}</td>
                        <td>{{ $lender->description }}</td>
                        <td>{{ $lender->amount }}</td>
                        <td>{{ $lender->date ? $lender->date->format('Y-m-d') : '' }}</td>
                        <td>
                            <div class="btn-group">
                                @if($lender->bill_number)
                                <a href="{{ url('cashier/showRecipt/' . $lender->bill_number) }}" 
                                   class="btn btn-sm btn-info">
                                    Print Bill
                                </a>
                                @endif
                                
                                <a href="{{ route('lenders.show', $lender->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('lenders.edit', $lender->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                
                                @if(!$lender->is_paid)
                                <a href="{{ route('lenders.mark-paid', $lender->id) }}" 
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Are you sure you want to mark this as paid?')">
                                    Mark Paid
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No applicable record found.</p>
    @endif
</div>
@endsection