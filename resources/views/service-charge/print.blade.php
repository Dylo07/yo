@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Service Charge Receipt</h3>
            <button onclick="window.print()" class="btn btn-light">Print Receipt</button>
        </div>
        
        <div class="card-body">
            <!-- Employee Details Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="border-bottom pb-2">Employee Details</h4>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Name:</th>
                                    <td>{{ $serviceCharge->person->name }}</td>
                                </tr>
                                <tr>
                                    <th>Month/Year:</th>
                                    <td>{{ Carbon\Carbon::create()->month($serviceCharge->month)->format('F') }} {{ $serviceCharge->year }}</td>
                                </tr>
                                <tr>
                                    <th>Generated Date:</th>
                                    <td>{{ $serviceCharge->created_at->format('d/m/Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Charge Details Section -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th class="bg-light" width="200">Total Service Charge Pool</th>
                            <td>Rs. {{ number_format($serviceCharge->total_sc, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Points Ratio</th>
                            <td>{{ number_format($serviceCharge->points_ratio * 100, 2) }}%</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Remarks</th>
                            <td>{{ $serviceCharge->remarks }}</td>
                        </tr>
                        <tr class="table-dark">
                            <th>Final Amount</th>
                            <td class="fw-bold">Rs. {{ number_format($serviceCharge->final_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section -->
            <div class="mt-5 pt-5">
                <div class="row">
                    <div class="col-md-4">
                        <div class="border-top border-dark"></div>
                        <p class="text-center mt-2">Employee Signature</p>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <div class="border-top border-dark"></div>
                        <p class="text-center mt-2">Authorized Signature</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn { 
            display: none !important; 
        }
        .card { 
            border: none !important; 
        }
        .card-header { 
            background-color: #333 !important; 
            color: white !important;
            -webkit-print-color-adjust: exact; 
        }
        .table-dark {
            background-color: #333 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
        }
        .bg-light {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
        .border-top {
            border-top: 1px solid #000 !important;
        }
        .border-bottom {
            border-bottom: 1px solid #000 !important;
        }
    }

    .table-borderless th {
        font-weight: 600;
        color: #666;
    }
    
    .table-bordered th {
        background-color: #f8f9fa;
    }
</style>
@endsection