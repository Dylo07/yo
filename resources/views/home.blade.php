@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row text-center">
                    @if(Auth::user()->checkAdmin())
                        


<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('management')}}">
        <h5>Management</h5>
        <a class="thumbnail" href="#">
            <img class="img-responsive" width="50px" src="{{asset('image/management.svg')}}"/> 
    </a>
</a>

</div>
@endif

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('cashier')}}">
    <h5>cashier</h5>
        <a class="thumbnail" href="#">
            <img class="img-responsive" width="50px" src="{{asset('image/cashier.svg')}}"/> 
    </a>
    </a>


</div>
@if(Auth::user()->checkAdmin())

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('report')}}">   
    <h5>report</h5>
        <a class="thumbnail" href="#">
            <img class="img-responsive"  width="50px" src="{{asset('image/report.svg')}}"/> 
    </a>
    </a>
</div>
@endif
<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('inventory')}}">   
    <h5>Inventory</h5>
        <a class="thumbnail" href="#">
            <img class="img-responsive"  width="50px" src="{{asset('image/stock.svg')}}"/> 
    </a>
    </a>
</div>



            </div>
        </div>
    </div>
</div>
@endsection
