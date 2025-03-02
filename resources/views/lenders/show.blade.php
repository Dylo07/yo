@extends('layouts.app')

@section('content')
<div class="container">
    <h1>View Lender</h1>
    
    <div class="mb-3">
        <strong>Name:</strong> {{ $lender->name }}
    </div>
    <div class="mb-3">
        <strong>NIC Number:</strong> {{ $lender->nic_number }}
    </div>
    <div class="mb-3">
        <strong>Bill Number:</strong> {{ $lender->bill_number ?? 'N/A' }}
    </div>
    <div class="mb-3">
        <strong>Description:</strong> {{ $lender->description }}
    </div>
    <div class="mb-3">
        <strong>Amount:</strong> {{ $lender->amount }}
    </div>
    <div class="mb-3">
        <strong>Date:</strong> {{ $lender->date }}
    </div>

    <div class="mb-3">
        @if($lender->bill_number)
        <a href="{{ url('cashier/showRecipt/' . $lender->bill_number) }}" 
           class="btn btn-info" target="_blank">
            <i class="fa fa-print"></i> Print Bill
        </a>
        @endif
        <a href="{{ route('lenders.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection