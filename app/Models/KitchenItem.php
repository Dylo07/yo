<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit',
        'current_stock',
        'minimum_stock',
        'cost_per_unit',
        'category_id',
        'is_active'
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(KitchenStockLog::class);
    }

    public function menuRecipes()
    {
        return $this->hasMany(MenuItemRecipe::class);
    }

    public function transfers()
    {
        return $this->hasMany(KitchenItemTransfer::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_item_recipes')
                    ->withPivot(['required_quantity', 'preparation_notes', 'is_optional'])
                    ->withTimestamps();
    }

    // Check if item is low stock
    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    // Update stock level
    public function updateStock($quantity, $action, $description = null, $userId = null, $metadata = null)
    {
        $previousStock = $this->current_stock;
        $newStock = $previousStock + $quantity;

        // Prevent negative stock
        if ($newStock < 0) {
            throw new \Exception('Insufficient stock. Available: ' . $previousStock);
        }

        $this->current_stock = $newStock;
        $this->save();

        // Log the stock movement
        KitchenStockLog::create([
            'kitchen_item_id' => $this->id,
            'action' => $action,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'description' => $description,
            'user_id' => $userId,
            'metadata' => $metadata
        ]);

        return $this;
    }
}
