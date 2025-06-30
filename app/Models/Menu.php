<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'description',
        'category_id',
        'image',
        'stock'
    ];
    
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function inStock(){
        return $this->hasmany(InStock::class);
    }

    // This relationship should use Item model, not KitchenItem
    public function kitchenItems()
    {
        return $this->belongsToMany(Item::class, 'menu_item_recipes', 'menu_id', 'item_id')
                    ->wherePivot('is_kitchen_item', true)
                    ->withPivot(['required_quantity', 'preparation_notes', 'is_optional'])
                    ->withTimestamps();
    }

    public function recipes()
    {
        return $this->hasMany(MenuItemRecipe::class);
    }

    // Check if menu can be prepared with current stock
    public function canBePrepared($quantity = 1)
    {
        $recipes = MenuItemRecipe::where('menu_id', $this->id)
            ->with('item')
            ->get();
            
        foreach ($recipes as $recipe) {
            if (!$recipe->item || !$recipe->item->is_kitchen_item) {
                continue;
            }
            
            $requiredQuantity = $recipe->required_quantity * $quantity;
            
            // Skip optional items
            if ($recipe->is_optional) {
                continue;
            }
            
            if ($recipe->item->kitchen_current_stock < $requiredQuantity) {
                return false;
            }
        }
        
        return true;
    }

    // Get missing ingredients for a menu
    public function getMissingIngredients($quantity = 1)
    {
        $missing = [];
        
        $recipes = MenuItemRecipe::where('menu_id', $this->id)
            ->with('item')
            ->get();
            
        foreach ($recipes as $recipe) {
            if (!$recipe->item || !$recipe->item->is_kitchen_item) {
                continue;
            }
            
            $requiredQuantity = $recipe->required_quantity * $quantity;
            
            if (!$recipe->is_optional && $recipe->item->kitchen_current_stock < $requiredQuantity) {
                $missing[] = [
                    'item' => $recipe->item,
                    'required' => $requiredQuantity,
                    'available' => $recipe->item->kitchen_current_stock,
                    'shortage' => $requiredQuantity - $recipe->item->kitchen_current_stock
                ];
            }
        }
        
        return $missing;
    }
}