<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergedProduct extends Model
{
    protected $fillable = ['parent_id', 'child_id', 'ml_value'];

    /**
     * Get the parent product.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the child product.
     */
    public function child()
    {
        return $this->belongsTo(Menu::class, 'child_id');
    }

    /**
     * Get all products in the same merge group
     */
    public static function getMergeGroup($menuId)
    {
        // Get parent if this is a child
        $asChild = self::where('child_id', $menuId)->first();
        
        if ($asChild) {
            $parentId = $asChild->parent_id;
        } else {
            // Get children if this is a parent
            $asParent = self::where('parent_id', $menuId)->first();
            if (!$asParent) {
                return null; // Not part of any merge group
            }
            $parentId = $menuId;
        }
        
        // Get all products in this merge group
        $mergeGroup = self::where('parent_id', $parentId)
            ->with(['parent', 'child'])
            ->get();
            
        if ($mergeGroup->isEmpty() && !$asChild) {
            return null;
        } elseif ($asChild) {
            $mergeGroup = collect([$asChild])->merge($mergeGroup);
        }
        
        return $mergeGroup;
    }

    /**
     * Calculate total ML for a merge group
     */
    public static function calculateTotalMl($menuId)
    {
        $mergeGroup = self::getMergeGroup($menuId);
        
        if (!$mergeGroup) {
            return null;
        }
        
        $totalMl = 0;
        
        // Calculate parent's contribution
        $parentId = $mergeGroup->first()->parent_id;
        $parent = Menu::find($parentId);
        if ($parent) {
            // Extract ml value from parent name
            preg_match('/\((\d+)\s*ml\)/i', $parent->name, $matches);
            if (isset($matches[1])) {
                $parentMl = (int)$matches[1] * $parent->stock;
                $totalMl += $parentMl;
            }
        }
        
        // Calculate children's contribution
        foreach ($mergeGroup as $item) {
            if ($item->child_id != $parentId) {
                $child = Menu::find($item->child_id);
                if ($child) {
                    // Extract ml value from child name
                    preg_match('/\((\d+)\s*ml\)/i', $child->name, $matches);
                    if (isset($matches[1])) {
                        $childMl = (int)$matches[1] * $child->stock;
                        $totalMl += $childMl;
                    }
                }
            }
        }
        
        return $totalMl;
    }
}