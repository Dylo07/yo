@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-{{ $type == 'wedding' ? 'info' : 'primary' }} text-white">
                    <h4 class="mb-0">Advance Payment for {{ ucfirst($type) }}</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Sale ID: {{ $sale->id }}</h5>
                    <p class="card-text">Table: {{ $sale->table_name }}</p>
                    
                    <form method="POST" action="{{ route('cashier.submitAdvancePayment') }}">
                        @csrf
                        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                        <input type="hidden" name="payment_type" value="{{ $type }}">
                        
                        <div class="form-group">
                            <label for="amount"><strong>Amount (Rs)</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rs</span>
                                </div>
                                <input type="number" id="amount" name="amount" class="form-control form-control-lg @error('amount') is-invalid @enderror" required min="1">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description"><strong>Description (Optional)</strong></label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="E.g., Hall booking for {{ $type }}"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Add any specific details about this advance payment.</small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-check-circle mr-2"></i> Complete Payment
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <a href="{{ route('cashier.advancePaymentSelection', ['saleID' => $sale->id]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Selection
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection