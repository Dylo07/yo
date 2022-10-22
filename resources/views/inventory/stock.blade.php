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
<i class="fa-solid fa-store"></i> Stock 
    <hr>
    @if(Session()->has('status'))
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert"></button>
        {{Session()->get('status')}}
        
    </div>
    @endif

    @if(Session()->has('warning'))
    <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert"></button>
        {{Session()->get('warning')}}
        
    </div>
    @endif
    
    <form action="/inventory/stockFilterByCategory" name="category" id="categoryForm" method="post">
        @csrf
        @method('POST')
      
        <div class="pull-right">
                <label>Search by category</lebel>
                <select name="category_id" id="category_id" class="form-control">
                @foreach($data['categories'] as $category)                
                    <option value="{{$category->id}}" @if($data['selectedCategory'] == $category->id ) selected @endif>{{$category->name}}</option>
                @endforeach    
                </select>
        </div>
    </form>
    <br>
    <table class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Current Stock</th>
            <th scope="col">Add Stock</th>
            <th scope="col">Remove Stock</th>
            <th scope="col">View</th>
        </tr>  
    </thead>
    <tbody> 

    </tbody>
    @foreach($data['menus'] as $menu)
    <tr> 
        <td> {{$menu->id}} </td>
        <td> {{$menu->name}} </td>
        <td> {{$menu->category->name}} </td>

        <td> {{$menu->stock}} </td>
        
        <td>
        <form action="/inventory/storestock/{{$menu->id}}" method="post">
            @csrf
            @method('POST')
            <div style="width:130px">
                <input type="number" name="stock"  style="width:80px" min="1">            
                <input type="submit" value="Add" class="btn btn-danger btn-sm">
            </div>
        </form>
        </td>

        <td>
        <form action="/inventory/removeStock/{{$menu->id}}" method="post">
            @csrf
            @method('DELETE')
            <div style="width:170px">
                <input type="number" name="stock" style="width:80px" min="1">            
                <input type="submit" value="Remove" class="btn btn-danger btn-sm">
            </div>
        </form>
        </td>
        
        
        <td> <a href="/inventory/stock/{{$menu->id}}" class="btn btn-primary">View</a></td>

    </tr>

    @endforeach

    
    </table>
    
</div>
    </div>
    </div>
    <script>
$(document).on('change', '#category_id', function () {
    $( "#categoryForm").submit();
});   
</script>
@endsection
