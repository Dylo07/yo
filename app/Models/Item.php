<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items'; // Explicitly set the table name
    
    protected $fillable = [
        'name', 
        'group_id',
        // Kitchen inventory fields
        'is_kitchen_item',
        'kitchen_unit',
        'kitchen_current_stock',
        'kitchen_minimum_stock',
        'kitchen_cost_per_unit',
        'kitchen_description',
        'kitchen_is_active'
    ];

    protected $casts = [
        'is_kitchen_item' => 'boolean',
        'kitchen_current_stock' => 'decimal:2',
        'kitchen_minimum_stock' => 'decimal:2',
        'kitchen_cost_per_unit' => 'decimal:2',
        'kitchen_is_active' => 'boolean'
    ];

    // Existing relationships
    public function group()
    {
        return $this->belongsTo(ProductGroup::class, 'group_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    public function logs()
    {
        return $this->hasMany(StockLog::class, 'item_id');
    }

    // New kitchen inventory relationships
    public function kitchenStockLogs()
    {
        return $this->hasMany(KitchenStockLog::class, 'item_id');
    }

    public function menuRecipes()
    {
        return $this->hasMany(MenuItemRecipe::class, 'item_id');
    }

    public function kitchenTransfers()
    {
        return $this->hasMany(KitchenItemTransfer::class, 'item_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_item_recipes', 'item_id', 'menu_id')
                    ->withPivot(['required_quantity', 'preparation_notes', 'is_optional'])
                    ->withTimestamps();
    }

    // Kitchen inventory helper methods
    public function isKitchenItem()
    {
        return $this->is_kitchen_item && $this->kitchen_is_active;
    }

    public function isLowKitchenStock()
    {
        return $this->is_kitchen_item && 
               $this->kitchen_is_active && 
               $this->kitchen_current_stock <= $this->kitchen_minimum_stock;
    }

    public function isOutOfKitchenStock()
    {
        return $this->is_kitchen_item && 
               $this->kitchen_is_active && 
               $this->kitchen_current_stock <= 0;
    }

    public function getKitchenStockStatus()
    {
        if (!$this->is_kitchen_item || !$this->kitchen_is_active) {
            return 'Not Kitchen Item';
        }

        if ($this->kitchen_current_stock <= 0) {
            return 'Out of Stock';
        }

        if ($this->kitchen_current_stock <= $this->kitchen_minimum_stock) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function getKitchenStockStatusClass()
    {
        $status = $this->getKitchenStockStatus();
        
        switch ($status) {
            case 'Out of Stock':
                return 'danger';
            case 'Low Stock':
                return 'warning';
            case 'In Stock':
                return 'success';
            default:
                return 'secondary';
        }
    }

    public function getKitchenTotalValue()
    {
        if (!$this->is_kitchen_item) {
            return 0;
        }
        
        return $this->kitchen_current_stock * $this->kitchen_cost_per_unit;
    }

    public function getFormattedKitchenTotalValue()
    {
        return 'Rs ' . number_format($this->getKitchenTotalValue(), 2);
    }

    // Update kitchen stock with logging
    public function updateKitchenStock($quantity, $action, $description = null, $userId = null, $metadata = null)
    {
        if (!$this->is_kitchen_item) {
            throw new \Exception('This item is not set up as a kitchen item');
        }

        $previousStock = $this->kitchen_current_stock;
        $newStock = $previousStock + $quantity;

        // Prevent negative stock
        if ($newStock < 0) {
            throw new \Exception('Insufficient kitchen stock. Available: ' . $previousStock . ' ' . $this->kitchen_unit);
        }

        $this->kitchen_current_stock = $newStock;
        $this->save();

        // Log the stock movement
        KitchenStockLog::create([
            'item_id' => $this->id,
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

    // Scopes for kitchen items
    public function scopeKitchenItems($query)
    {
        return $query->where('is_kitchen_item', true)
                    ->where('kitchen_is_active', true);
    }

    public function scopeLowKitchenStock($query)
    {
        return $query->kitchenItems()
                    ->whereRaw('kitchen_current_stock <= kitchen_minimum_stock');
    }

    public function scopeOutOfKitchenStock($query)
    {
        return $query->kitchenItems()
                    ->where('kitchen_current_stock', '<=', 0);
    }

    public function scopeInKitchenStock($query)
    {
        return $query->kitchenItems()
                    ->whereRaw('kitchen_current_stock > kitchen_minimum_stock');
    }

    // Get kitchen stock value for a specific date range
    public function getKitchenStockMovements($startDate, $endDate)
    {
        return $this->kitchenStockLogs()
                   ->whereBetween('created_at', [$startDate, $endDate])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    // Calculate kitchen stock usage for a period
    public function getKitchenStockUsage($startDate, $endDate)
    {
        $movements = $this->getKitchenStockMovements($startDate, $endDate);
        
        return [
            'received' => $movements->where('action', 'receive_from_store')->sum('quantity'),
            'consumed' => abs($movements->where('action', 'menu_consumption')->sum('quantity')),
            'wasted' => abs($movements->where('action', 'waste')->sum('quantity')),
            'adjusted' => $movements->where('action', 'manual_adjustment')->sum('quantity'),
            'transferred' => $movements->where('action', 'transfer')->sum('quantity')
        ];
    }

    // Check if item can fulfill a specific quantity requirement
    public function canFulfillKitchenQuantity($requiredQuantity)
    {
        return $this->is_kitchen_item && 
               $this->kitchen_is_active && 
               $this->kitchen_current_stock >= $requiredQuantity;
    }

    // Get shortage amount if any
    public function getKitchenShortage($requiredQuantity)
    {
        if (!$this->is_kitchen_item) {
            return $requiredQuantity;
        }

        $shortage = $requiredQuantity - $this->kitchen_current_stock;
        return $shortage > 0 ? $shortage : 0;
    }

    // Format kitchen stock display
    public function getFormattedKitchenStock()
    {
        if (!$this->is_kitchen_item) {
            return 'N/A';
        }

        return $this->kitchen_current_stock . ' ' . $this->kitchen_unit;
    }

    // Get recent kitchen stock activities
    public function getRecentKitchenActivities($limit = 5)
    {
        return $this->kitchenStockLogs()
                   ->with('user')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // Check if item needs restocking based on usage pattern
    public function needsKitchenRestocking($days = 7)
    {
        if (!$this->is_kitchen_item) {
            return false;
        }

        // Already low stock
        if ($this->isLowKitchenStock()) {
            return true;
        }

        // Calculate average daily usage
        $endDate = now();
        $startDate = now()->subDays($days);
        
        $totalUsage = abs($this->kitchenStockLogs()
                              ->whereBetween('created_at', [$startDate, $endDate])
                              ->whereIn('action', ['menu_consumption', 'waste'])
                              ->sum('quantity'));

        $averageDailyUsage = $totalUsage / $days;
        
        // Check if current stock will last less than 3 days at current usage rate
        if ($averageDailyUsage > 0) {
            $daysOfStock = $this->kitchen_current_stock / $averageDailyUsage;
            return $daysOfStock < 3;
        }

        return false;
    }

    // Get suggested reorder quantity
    public function getSuggestedKitchenReorderQuantity($days = 7)
    {
        if (!$this->is_kitchen_item) {
            return 0;
        }

        $endDate = now();
        $startDate = now()->subDays($days);
        
        $totalUsage = abs($this->kitchenStockLogs()
                              ->whereBetween('created_at', [$startDate, $endDate])
                              ->whereIn('action', ['menu_consumption', 'waste'])
                              ->sum('quantity'));

        $averageDailyUsage = $totalUsage / $days;
        
        // Suggest quantity for 2 weeks + minimum stock
        $suggestedQuantity = ($averageDailyUsage * 14) + $this->kitchen_minimum_stock - $this->kitchen_current_stock;
        
        return $suggestedQuantity > 0 ? round($suggestedQuantity, 2) : 0;
    }

    // Convert regular inventory item to kitchen item
    public function convertToKitchenItem($unit, $currentStock, $minimumStock, $costPerUnit, $description = null)
    {
        if ($this->is_kitchen_item) {
            throw new \Exception('Item is already a kitchen item');
        }

        $this->update([
            'is_kitchen_item' => true,
            'kitchen_unit' => $unit,
            'kitchen_current_stock' => $currentStock,
            'kitchen_minimum_stock' => $minimumStock,
            'kitchen_cost_per_unit' => $costPerUnit,
            'kitchen_description' => $description,
            'kitchen_is_active' => true
        ]);

        // Log the initial stock if any
        if ($currentStock > 0) {
            $this->updateKitchenStock(
                $currentStock,
                'manual_adjustment',
                'Initial kitchen stock setup',
                auth()->id()
            );
        }

        return $this;
    }

    // Remove from kitchen inventory
    public function removeFromKitchen()
    {
        if (!$this->is_kitchen_item) {
            return false;
        }

        $this->update([
            'is_kitchen_item' => false,
            'kitchen_is_active' => false
        ]);

        return true;
    }
}