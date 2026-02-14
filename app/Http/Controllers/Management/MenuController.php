<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Menu;
class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::with('category')->orderBy('category_id')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        // Kitchen items for recipe dropdown
        $kitchenItems = DB::table('items')
            ->where('is_kitchen_item', true)
            ->where('kitchen_is_active', true)
            ->orderBy('name')
            ->get();

        // Recipe counts per menu
        $recipeCounts = DB::table('menu_item_recipes')
            ->select('menu_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('menu_id')
            ->pluck('cnt', 'menu_id')
            ->toArray();

        // Popular ingredients (top 15)
        $popularItemIds = DB::table('menu_item_recipes')
            ->select('item_id', DB::raw('COUNT(DISTINCT menu_id) as menu_count'))
            ->groupBy('item_id')
            ->orderByDesc('menu_count')
            ->limit(15)
            ->pluck('menu_count', 'item_id')
            ->toArray();

        return view('management.menu', compact('menus', 'categories', 'kitchenItems', 'recipeCounts', 'popularItemIds'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories= Category::all();
        return view('management.createMenu')->with('categories',$categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|unique:menus|max:255',
            'price'=>'required|numeric',
             'category_id' =>  'required|numeric'

    ]);
    //if a user does not upload an image,use noimage.png for the menu
    $imageName= "noimage.png";
    //if a user upload a image
    if($request->image){
        $request->validate ([
            'image' => 'nullable|files|image|mimes:jpeg,png.jpg|max:5000'


        ]);
       $imageName= date('mdYHis').uniqid().'.'. $request->image->extension();
       $request->image->move(public_path('menu_images'),$imageName);
    }
    //save information to menu table
    $menu = new Menu();
    $menu->name = $request ->name;
    $menu->price = $request-> price;
    $menu->image = $imageName;
    $menu->description = $request->description;
    $menu->category_id =$request->category_id;
    $menu->save();
    $request->session()->flash('status',$request->name. ' is saved successfully');
    return redirect('/management/menu');
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
        $menu = Menu::find($id);
        $categories = Category::all();
        return view('management.editMenu')->with('menu',$menu)->with('categories',$categories);
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
        //information validation
        $request->validate([
            'name'=>'required|max:255',
            'price'=>'required|numeric',
            'category_id'=>'required|numeric'
        ]);
        $menu = Menu::find($id);
        //validate if a user upload a image
        if($request->image){
            $request->validate([
                'image'=>'nullable|file|image|mimes:jpeg,png,jpp|max:5000'
            ]);

            if($menu->image !="noimage.png"){
                $imageName=$menu->image;
                unlink(public_path('menu_images').'/'.$imageName);

            }
            $imageName =date('mdYHis').uniqid().'.'.$request->image->extension();
        $request->image->move(public_path(menu_images),$imageName);
        } else { $imageName = $menu->image;

        }
        $menu->name = $request->name;
        $menu->price = $request ->price;
        $menu->image=$imageName;
        $menu->description = $request->description;
        $menu->category_id = $request->category_id;
        $menu->save();
        $request -> session ()->flash('status',$request->name. ' is updated succesfully');
        return redirect('/management/menu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $menu=Menu::find($id);
        if ($menu->image !="noimage.png"){
            unlink(public_path('menu_images').'/'.$menu->image);

        }
        $menuName=$menu->name;
        $menu->delete();
        Session()->flash('status',$menuName. ' is deleted Successfully');
         return redirect('management/menu');
        
        ;
    }

    /**
     * Bulk delete selected menus
     */
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $menus = Menu::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($menus as $menu) {
            if ($menu->image != 'noimage.png' && file_exists(public_path('menu_images') . '/' . $menu->image)) {
                unlink(public_path('menu_images') . '/' . $menu->image);
            }
            $menu->delete();
            $count++;
        }

        return response()->json(['success' => true, 'message' => "$count menu(s) deleted successfully"]);
    }

    /**
     * Bulk move selected menus to a category
     */
    public function bulkMove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'category_id' => 'required|exists:categories,id'
        ]);

        $count = Menu::whereIn('id', $request->ids)->update(['category_id' => $request->category_id]);
        $category = Category::find($request->category_id);

        return response()->json(['success' => true, 'message' => "$count menu(s) moved to {$category->name}"]);
    }
}
