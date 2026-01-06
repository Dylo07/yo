<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelfareFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'type',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentBalance()
    {
        $added = self::where('type', 'add')->sum('amount');
        $deducted = self::where('type', 'deduct')->sum('amount');
        return $added - $deducted;
    }

    public static function getMonthlyTotal($year, $month, $type = null)
    {
        $query = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->sum('amount');
    }
}
