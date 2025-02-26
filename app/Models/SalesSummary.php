<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class SalesSummary extends Model
{
    protected $table = 'sales';

    public static function getDailySummary($date)
    {
        try {
            // Convert date to start and end of day
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            $query = DB::table('sales')
                ->leftJoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->leftJoin('menus', 'sale_details.menu_id', '=', 'menus.id')
                ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
                ->select(
                    'sale_details.menu_id',
                    'menus.name as menu_name',
                    'categories.name as category_name',
                    DB::raw('COALESCE(SUM(sale_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(sale_details.quantity * sale_details.menu_price), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(sale_details.menu_price), 0) as average_price')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->where('sales.sale_status', 'paid')
                ->groupBy('sale_details.menu_id', 'menus.name', 'categories.name');

            $results = $query->get();
            Log::info('Daily Summary Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);
            return $results;

        } catch (\Exception $e) {
            Log::error('Error in getDailySummary: ' . $e->getMessage(), [
                'date' => $date,
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    public static function getMonthlySummary($year, $month)
    {
        try {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $query = DB::table('sales')
                ->leftJoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->leftJoin('menus', 'sale_details.menu_id', '=', 'menus.id')
                ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
                ->select(
                    'sale_details.menu_id',
                    'menus.name as menu_name',
                    'categories.name as category_name',
                    DB::raw('COALESCE(SUM(sale_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(sale_details.quantity * sale_details.menu_price), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(sale_details.menu_price), 0) as average_price'),
                    DB::raw('DATE(sales.created_at) as sale_date')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->where('sales.sale_status', 'paid')
                ->groupBy('sale_details.menu_id', 'menus.name', 'categories.name', 'sale_date');

            return $query->get();

        } catch (\Exception $e) {
            Log::error('Error in getMonthlySummary: ' . $e->getMessage());
            return collect([]);
        }
    }

    public static function getYearlySummary($year)
    {
        try {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();

            $query = DB::table('sales')
                ->leftJoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->leftJoin('menus', 'sale_details.menu_id', '=', 'menus.id')
                ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
                ->select(
                    'sale_details.menu_id',
                    'menus.name as menu_name',
                    'categories.name as category_name',
                    DB::raw('COALESCE(SUM(sale_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(sale_details.quantity * sale_details.menu_price), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(sale_details.menu_price), 0) as average_price'),
                    DB::raw('MONTH(sales.created_at) as sale_month')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->where('sales.sale_status', 'paid')
                ->groupBy('sale_details.menu_id', 'menus.name', 'categories.name', 'sale_month');

            return $query->get();

        } catch (\Exception $e) {
            Log::error('Error in getYearlySummary: ' . $e->getMessage());
            return collect([]);
        }
    }
}