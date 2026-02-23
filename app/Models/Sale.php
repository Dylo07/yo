<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id', 'user_id', 'table_name', 'total_price', 
        'total_recieved', 'change', 'payment_type', 'sale_status',
        'included_service_charge'
    ];

    public function saleDetails(){
        return $this->hasMany(SaleDetail::class);
    }
}
