<?php

namespace App\Http\Controllers\inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InStock;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{   
    // Define the specific category IDs we want to show
    $categoryIds = [4, 5, 28, 29];
    
    // Get categories and their menus
    $categories = Category::whereIn('id', $categoryIds)->get();
    $menus = Menu::whereIn('category_id', $categoryIds)
        ->with('category') // Eager load the category relationship
        ->orderBy('name') // Order items alphabetically by name
        ->get()
        ->groupBy('category_id'); // Group items by category
    
    $data = array('menus' => $menus, 'categories' => $categories);
    return view('inventory.stock')->with('data', $data);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //echo  $request ->itemid;
        //dd($request->all());
        //$stock = InStock::create($request->all());
        //save information to stock table
        $user = Auth::user();
        $stock = new InStock();
        $stock->menu_id  = $request ->itemid;
        $stock->stock = $request->stock;
        $stock->user_id = $user->id;
        
        $stock->save();
        $menu = Menu::find($request ->itemid);
        $menu->stock = intval($menu->stock)+($request->stock);
        $menu->save();
        $request->session()->flash('status','Stock saved successfully');
        return redirect('/inventory/stock');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menu = Menu::find($id);
        return view ('inventory.stockDetail')->with('menu',$menu);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $user = Auth::user();
        $stock = new InStock();
        $stock->menu_id  = $request ->itemid;
        $stock->stock = -intval($request->stock);
        $stock->user_id = $user->id;
        $stock->save();
    
        $menu = Menu::find($request ->itemid);
        $menu->stock = intval($menu->stock)-($request->stock);
        $menu->save();
        $request->session()->flash('warning','Stock has been removed successfully');
    
        return redirect('/inventory/stock');
    }
}
