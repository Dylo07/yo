<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Category;
use App\Models\MergedProduct;
use Illuminate\Support\Facades\DB;

class MergedProductController extends Controller
{
    /**
     * Display a listing of the merged products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all products in category 29 (liquor)
        $liquorProducts = Menu::where('category_id', 29)
            ->orderBy('name')
            ->get();
            
        // Group products by base name (without ml size)
        $groupedProducts = [];
        foreach ($liquorProducts as $product) {
            // Extract the base name (without ml size)
            $baseName = preg_replace('/\(\d+\s*ml\)/', '', $product->name);
            $baseName = trim($baseName);
            
            if (!isset($groupedProducts[$baseName])) {
                $groupedProducts[$baseName] = [];
            }
            
            $groupedProducts[$baseName][] = $product;
        }
        
        // Get existing merged products
        $mergedProducts = MergedProduct::with(['parent', 'child'])->get();
        $mergedGroups = [];
        
        foreach ($mergedProducts as $mergedProduct) {
            $parentId = $mergedProduct->parent_id;
            
            if (!isset($mergedGroups[$parentId])) {
                $mergedGroups[$parentId] = [
                    'parent' => $mergedProduct->parent,
                    'children' => []
                ];
            }
            
            $mergedGroups[$parentId]['children'][] = $mergedProduct->child;
        }
        
        return view('inventory.merged-products', [
            'groupedProducts' => $groupedProducts,
            'mergedGroups' => $mergedGroups
        ]);
    }
    
    /**
     * Merge products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function merge(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:menus,id',
            'child_ids' => 'required|array',
            'child_ids.*' => 'exists:menus,id'
        ]);
        
        $parentId = $request->parent_id;
        $childIds = $request->child_ids;
        
        // Check if the parent menu is already a child in another merge group
        $parentAsChild = MergedProduct::where('child_id', $parentId)->first();
        if ($parentAsChild) {
            return redirect()->back()->with('error', 'The selected parent product is already a child in another merge group.');
        }
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Remove any existing merge relationships for these products
            MergedProduct::where('parent_id', $parentId)->delete();
            MergedProduct::whereIn('child_id', $childIds)->delete();
            
            // Create new merge relationships
            foreach ($childIds as $childId) {
                if ($childId != $parentId) { // Don't merge a product with itself
                    MergedProduct::create([
                        'parent_id' => $parentId,
                        'child_id' => $childId
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->back()->with('status', 'Products have been merged successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while merging products: ' . $e->getMessage());
        }
    }
    
    /**
     * Unmerge products.
     *
     * @param  int  $parentId
     * @return \Illuminate\Http\Response
     */
    public function unmerge($parentId)
    {
        // Delete all merge relationships for this parent
        MergedProduct::where('parent_id', $parentId)->delete();
        
        return redirect()->back()->with('status', 'Products have been unmerged successfully.');
    }

    /**
     * Consolidate product stock.
     *
     * @param  int  $parentId
     * @return \Illuminate\Http\Response
     */
    public function consolidate($parentId)
    {
        $mergeGroup = MergedProduct::where('parent_id', $parentId)->with(['parent', 'child'])->get();
        
        if ($mergeGroup->isEmpty()) {
            return redirect()->back()->with('error', 'No merge group found for the selected product.');
        }
        
        $parent = Menu::find($parentId);
        
        // Extract ml value from parent name
        preg_match('/\((\d+)\s*ml\)/i', $parent->name, $matches);
        if (!isset($matches[1])) {
            return redirect()->back()->with('error', 'Could not determine ml value for parent product.');
        }
        
        $parentMl = (int)$matches[1];
        $totalMl = $parentMl * $parent->stock;
        
        // Add ml from children
        foreach ($mergeGroup as $item) {
            $child = $item->child;
            
            // Extract ml value from child name
            preg_match('/\((\d+)\s*ml\)/i', $child->name, $matches);
            if (isset($matches[1])) {
                $childMl = (int)$matches[1];
                $totalMl += $childMl * $child->stock;
                
                // Reset child stock to 0
                $child->stock = 0;
                $child->save();
            }
        }
        
        // Calculate new parent stock
        $newParentStock = floor($totalMl / $parentMl);
        $parent->stock = $newParentStock;
        $parent->save();
        
        return redirect()->back()->with('status', 'Stock has been consolidated successfully.');
    }
    
    /**
     * Redistribute ml to specified products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function redistribute(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:menus,id',
            'distribution' => 'required|array',
            'distribution.*.menu_id' => 'required|exists:menus,id',
            'distribution.*.ml_amount' => 'required|numeric|min:0'
        ]);
        
        $parentId = $request->parent_id;
        $distributions = $request->distribution;
        
        $parent = Menu::find($parentId);
        
        // Extract ml value from parent name
        preg_match('/\((\d+)\s*ml\)/i', $parent->name, $matches);
        if (!isset($matches[1])) {
            return redirect()->back()->with('error', 'Could not determine ml value for parent product.');
        }
        
        $parentMl = (int)$matches[1];
        $totalMlAvailable = $parentMl * $parent->stock;
        
        $totalMlRequested = 0;
        foreach ($distributions as $dist) {
            $totalMlRequested += $dist['ml_amount'];
        }
        
        if ($totalMlRequested > $totalMlAvailable) {
            return redirect()->back()->with('error', 'Cannot distribute more ml than available.');
        }
        
        DB::beginTransaction();
        
        try {
            // Update stock for each product
            foreach ($distributions as $dist) {
                $menu = Menu::find($dist['menu_id']);
                
                // Extract ml value from menu name
                preg_match('/\((\d+)\s*ml\)/i', $menu->name, $matches);
                if (isset($matches[1])) {
                    $menuMl = (int)$matches[1];
                    $newStock = floor($dist['ml_amount'] / $menuMl);
                    
                    $menu->stock = $newStock;
                    $menu->save();
                }
            }
            
            // Update parent stock
            $remainingMl = $totalMlAvailable - $totalMlRequested;
            $parent->stock = floor($remainingMl / $parentMl);
            $parent->save();
            
            DB::commit();
            return redirect()->back()->with('status', 'Stock has been redistributed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while redistributing stock: ' . $e->getMessage());
        }
    }
}