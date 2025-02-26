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
        @if($menu->category_id == 29)
            @php
                preg_match('/\((\d+)\s*ml\)/i', $menu->name, $matches);
                $menuMl = isset($matches[1]) ? $matches[1] : 'N/A';
            @endphp
            <tr>
                <th scope="col">ML Per Unit</th>
                <td> {{$menuMl}} ml</td>
            </tr>
            <tr>
                <th scope="col">Total ML</th>
                <td> {{$menuMl * $menu->stock}} ml</td>
            </tr>
            @if(isset($menu->is_merge_parent) && $menu->is_merge_parent)
                <tr>
                    <th scope="col">Merged Group Total ML</th>
                    <td> {{$menu->total_ml}} ml</td>
                </tr>
            @elseif(isset($menu->merge_parent))
                <tr>
                    <th scope="col">Part of Merged Group</th>
                    <td> Parent: {{$menu->merge_parent->name}}</td>
                </tr>
            @endif
        @endif
        </thead>
    </table>

    @if(isset($menu->is_merge_parent) && $menu->is_merge_parent)
        <h4 class="text-center">Merged Products in Group</h4>
        <div style="width:75%; margin:auto">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Current Stock</th>
                        <th scope="col">ML Per Unit</th>
                        <th scope="col">Total ML</th>
                    </tr>  
                </thead>
                <tbody> 
                    @foreach($menu->merged_children as $child)
                        @php
                            preg_match('/\((\d+)\s*ml\)/i', $child->name, $matches);
                            $childMl = isset($matches[1]) ? $matches[1] : 'N/A';
                        @endphp
                        <tr> 
                            <td>{{$child->name}}</td>
                            <td>{{$child->stock}}</td>
                            <td>{{$childMl}} ml</td>
                            <td>{{$childMl * $child->stock}} ml</td>
                        </tr>
                    @endforeach
                    <tr class="table-info">
                        <td colspan="3"><strong>Total ML</strong></td>
                        <td><strong>{{$menu->total_ml}} ml</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <h4 class="text-center">History of {{$menu->name}}</h4>
    <div style="width:75%; margin:auto">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">User</th>
                    <th scope="col">Product</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Date/time</th>
                </tr>  
            </thead>
            <tbody> 
                @if(isset($menu->all_in_stock))
                    @foreach($menu->all_in_stock as $stock)
                        <tr @if($stock->menu_id != $menu->id) class="table-secondary" @endif> 
                            <td> {{$stock->user->name}} ({{$stock->user->email}})</td>
                            <td> {{$stock->menu->name}} </td>
                            <td> {{$stock->stock}} </td>
                            <td> {{$stock->created_at->format('d-M-Y')}}
{{$stock->created_at->format('h:i:s A')}}</td>
                        </tr>
                    @endforeach
                @else
                    @foreach($menu->inStock as $stock)
                        <tr> 
                            <td> {{$stock->user->name}} ({{$stock->user->email}})</td>
                            <td> {{$menu->name}} </td>
                            <td> {{$stock->stock}} </td>
                            <td> {{$stock->created_at->format('d-M-Y')}}
{{$stock->created_at->format('h:i:s A')}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
</div>
    </div>
    </div>
@endsection