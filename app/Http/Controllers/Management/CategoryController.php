<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = category::paginate(5);
        return view ('management.category')->with('categories',$categories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('management.createCategory');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['name'=>'required|unique:categories|max:255']);

        $category = new Category;
        $name = $request->name;
        $category->name =$name;
        $category->save();
        Cache::forget('cashier_categories');
        $request->session()->flash('status', $request->name." is save successfully");
        return(redirect('/management/category')); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::find($id);
        return view('management.editCategory')->with('category',$category);
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
        $request->validate(['name'=> 'required|unique:categories|max:255']);
        $category= Category::find($id);
        $category->name= $request->name;
        $category->save();
        Cache::forget('cashier_categories');
        $request->session()->flash('status', $request->name." is updated successfully");
        return(redirect('/management/category')); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->checkAdmin()) {
            Session()->flash('status', 'Only admins can delete categories.');
            return redirect('/management/category');
        }

        Category::destroy($id);
        Cache::forget('cashier_categories');
        Session()->flash('status','The category is deleted successfully');
        return redirect ('/management/category');

    }
}
