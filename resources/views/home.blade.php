@extends('layouts.app')

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
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
                    
                        


<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('management')}}">
        <h5>Management</h5>
        <a class="thumbnail" href="{{route('management')}}">
            <img class="img-responsive" width="50px" src="{{asset('image/management.svg')}}"/> 
            
    </a>
</a>

</div>


<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('cashier')}}">
    <h5>cashier</h5>
        <a class="thumbnail" href="{{route('cashier')}}">
            <img class="img-responsive" width="50px" src="{{asset('image/cashier.svg')}}"/> 
            
    </a>
    </a>


</div>

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('inventory')}}">   
    <h5>Beer & Soft Drink stock  </h5>
        <a class="thumbnail" href="{{route('inventory')}}">
            <img class="img-responsive"  width="50px" src="{{asset('image/bottle.svg')}}"/> 
           
    </a>
    </a>
</div>

@if(Auth::user()->checkAdmin())

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('report')}}">   
    <h5>report</h5>
        <a class="thumbnail" href="{{route('report')}}">
            <img class="img-responsive"  width="50px" src="{{asset('image/report.svg')}}"/> 
            
    </a>
    </a>
</div>


@endif
<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{route('pettycash')}}">   
    <h5>Petty Cash</h5>
        <a class="thumbnail" href="{{route('pettycash')}}">
            <img class="img-responsive"  width="50px" src="{{asset('image/Pettycash.svg')}}"/> 
           
    </a>
    </a>
</div>

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{ url('/calendar') }}">   
    <h5>Booking Calendar</h5>
        <a class="thumbnail" href="{{ url('/calendar') }}">
            <img class="img-responsive"  width="50px" src="{{asset('image/calendar.svg')}}"/> 
           
    </a>
    </a>
</div>

<div class="col-lg-4 col-md-4 col-xs-4 thumb">
<a href="{{ url('/stock') }}">   
    <h5>Inventory</h5>
        <a class="thumbnail" href="{{ url('/stock') }}">
            <img class="img-responsive"  width="50px" src="{{asset('image/inv.svg')}}"/> 
           
    </a>
    </a>
</div>



            </div>
        </div>
    </div>
</div>
@endsection
