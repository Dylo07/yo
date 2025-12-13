<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryType extends Model
{
    use HasFactory;

    protected $table = 'category_types';

    protected $fillable = [
        'slug',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active categories ordered by sort_order
     */
    public static function getActiveCategories()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get categories as key-value pairs for dropdowns
     */
    public static function getCategoriesForDropdown()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * Get category slugs for validation
     */
    public static function getCategorySlugs()
    {
        return self::where('is_active', true)
            ->pluck('slug')
            ->toArray();
    }
}
