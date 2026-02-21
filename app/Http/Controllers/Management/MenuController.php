<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        // Recipe ingredients per menu (name list + count)
        $recipeData = DB::table('menu_item_recipes')
            ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
            ->select('menu_item_recipes.menu_id', 'items.name as item_name', 'menu_item_recipes.required_quantity', 'items.kitchen_unit')
            ->orderBy('items.name')
            ->get()
            ->groupBy('menu_id');

        $recipeCounts = [];
        $recipeIngredients = [];
        foreach ($recipeData as $menuId => $items) {
            $recipeCounts[$menuId] = $items->count();
            $recipeIngredients[$menuId] = $items->map(function($i) {
                return $i->item_name . ' (' . rtrim(rtrim(number_format($i->required_quantity, 3), '0'), '.') . ' ' . $i->kitchen_unit . ')';
            })->toArray();
        }

        // Popular ingredients (top 15)
        $popularItemIds = DB::table('menu_item_recipes')
            ->select('item_id', DB::raw('COUNT(DISTINCT menu_id) as menu_count'))
            ->groupBy('item_id')
            ->orderByDesc('menu_count')
            ->limit(15)
            ->pluck('menu_count', 'item_id')
            ->toArray();

        return view('management.menu', compact('menus', 'categories', 'kitchenItems', 'recipeCounts', 'recipeIngredients', 'popularItemIds'));
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
    $this->logActivity('created', $menu->id, $menu->name, "Created menu: {$menu->name} (Rs " . number_format($menu->price, 2) . ")");
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
        
        // Check if menu is locked and user is not admin
        if ($menu->is_locked && Auth::id() != 1) {
            return redirect('/management/menu')
                ->with('error', 'This menu item is locked. Only admin can edit it.');
        }
        
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
        
        // Security check: prevent non-admin from updating locked items
        if ($menu->is_locked && Auth::id() != 1) {
            return redirect('/management/menu')
                ->with('error', 'This menu item is locked. Only admin can edit it.');
        }
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
        $this->logActivity('updated', $menu->id, $menu->name, "Updated menu: {$menu->name} (Rs " . number_format($menu->price, 2) . ")");
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
        $this->logActivity('deleted', $id, $menuName, "Deleted menu: {$menuName}");
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

        $names = $menus->pluck('name')->implode(', ');
        $this->logActivity('bulk_deleted', null, null, "Bulk deleted {$count} menu(s): {$names}");

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

        $menuNames = Menu::whereIn('id', $request->ids)->pluck('name')->implode(', ');
        $count = Menu::whereIn('id', $request->ids)->update(['category_id' => $request->category_id]);
        $category = Category::find($request->category_id);

        $this->logActivity('bulk_moved', null, null, "Moved {$count} menu(s) to {$category->name}: {$menuNames}");

        return response()->json(['success' => true, 'message' => "$count menu(s) moved to {$category->name}"]);
    }

    /**
     * Get activity logs (AJAX)
     */
    public function getLogs(Request $request)
    {
        $logs = DB::table('menu_activity_logs')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return response()->json($logs);
    }

    /**
     * Bulk lock selected menus (Admin only)
     */
    public function bulkLock(Request $request)
    {
        if (Auth::id() != 1) {
            return response()->json(['success' => false, 'message' => 'Only admin can lock menu items'], 403);
        }

        $request->validate(['ids' => 'required|array']);
        
        $menus = Menu::whereIn('id', $request->ids)->get();
        $count = 0;
        
        foreach ($menus as $menu) {
            if (!$menu->is_locked) {
                $menu->is_locked = true;
                $menu->locked_by = Auth::id();
                $menu->locked_at = now();
                $menu->save();
                $count++;
            }
        }
        
        $menuNames = $menus->pluck('name')->implode(', ');
        $this->logActivity('bulk_locked', null, null, "Locked {$count} menu(s): {$menuNames}");
        
        return response()->json(['success' => true, 'message' => "$count menu(s) locked successfully"]);
    }

    /**
     * Bulk unlock selected menus (Admin only)
     */
    public function bulkUnlock(Request $request)
    {
        if (Auth::id() != 1) {
            return response()->json(['success' => false, 'message' => 'Only admin can unlock menu items'], 403);
        }

        $request->validate(['ids' => 'required|array']);
        
        $menus = Menu::whereIn('id', $request->ids)->get();
        $count = 0;
        
        foreach ($menus as $menu) {
            if ($menu->is_locked) {
                $menu->is_locked = false;
                $menu->locked_by = null;
                $menu->locked_at = null;
                $menu->save();
                $count++;
            }
        }
        
        $menuNames = $menus->pluck('name')->implode(', ');
        $this->logActivity('bulk_unlocked', null, null, "Unlocked {$count} menu(s): {$menuNames}");
        
        return response()->json(['success' => true, 'message' => "$count menu(s) unlocked successfully"]);
    }

    /**
     * Toggle lock status of a menu item (Admin only)
     */
    public function toggleLock($id)
    {
        // Only admin (user_id = 1) can lock/unlock menu items
        if (Auth::id() != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can lock/unlock menu items'
            ], 403);
        }

        $menu = Menu::findOrFail($id);
        
        if ($menu->is_locked) {
            // Unlock
            $menu->is_locked = false;
            $menu->locked_by = null;
            $menu->locked_at = null;
            $action = 'unlocked';
            $message = $menu->name . ' is now unlocked';
        } else {
            // Lock
            $menu->is_locked = true;
            $menu->locked_by = Auth::id();
            $menu->locked_at = now();
            $action = 'locked';
            $message = $menu->name . ' is now locked';
        }
        
        $menu->save();
        $this->logActivity($action, $menu->id, $menu->name, "{$action} menu: {$menu->name}");
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'is_locked' => $menu->is_locked
        ]);
    }

    /**
     * Log a menu activity
     */
    private function logActivity($action, $menuId, $menuName, $details)
    {
        try {
            DB::table('menu_activity_logs')->insert([
                'action' => $action,
                'menu_id' => $menuId,
                'menu_name' => $menuName,
                'user_id' => Auth::id(),
                'user_name' => Auth::user() ? Auth::user()->name : 'System',
                'details' => $details,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log menu activity: ' . $e->getMessage());
        }
    }
}
