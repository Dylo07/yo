@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Advance Payment Selection</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Sale ID: {{ $sale->id }}</h5>
                    <p class="card-text">Table: {{ $sale->table_name }}</p>
                    <p class="card-text">Please select the type of advance payment:</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('cashier.advancePaymentForm', ['saleID' => $sale->id, 'type' => 'function']) }}" 
                               class="btn btn-primary btn-lg btn-block p-4">
                                <i class="fas fa-calendar-alt mr-2"></i> Function
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('cashier.advancePaymentForm', ['saleID' => $sale->id, 'type' => 'wedding']) }}" 
                               class="btn btn-info btn-lg btn-block p-4">
                                <i class="fas fa-heart mr-2"></i> Wedding
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ url('/cashier') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Cashier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection