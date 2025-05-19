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
}