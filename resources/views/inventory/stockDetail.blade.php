@extends('layouts.app')

@section('content')

    <style type="text/css">  
        i{  
            font-size: 20px !important;  
            padding: 10px;  
        }  
    </style>  
</link>
</link>

<div class="container">
<div class="row justify-content">
    @include('inventory.inc.sidebar')
 
<div class="col-md-8">
<i class="fa-solid fa-store"></i> Stock Detail
    <hr>
    
  
    <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <td> {{$menu->id}} </td>
        </tr>
        <tr>
            <th scope="col">Name</th>
            <td> {{$menu->name}} </td>
        </tr>
        <tr>
            <th scope="col">Category</th>
            <td> {{$menu->category->name}} </td>
        </tr>
        <tr>
            <th scope="col">Current Stock</th>
            <td> {{$menu->stock}} </td>
        </tr>  
        </thead>
    </table>

    <h4 class="text-center">History of {{$menu->name}}</h4>
    <div style="width:75%; margin:auto">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">User</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Date/time</th>
                    
                </tr>  
            </thead>
            <tbody> 
                @foreach($menu->inStock as $stock)
                <tr> 
                    <td> {{$stock->user->name}} ({{$stock->user->email}})</td>
                    <td> {{$stock->stock}} </td>
                    <td> {{$stock->created_at->format('d-M-Y')}}
{{$stock->created_at->format('h:i:s A')}}</td>
                    
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    
</div>
    </div>
    </div>
@endsection
   
