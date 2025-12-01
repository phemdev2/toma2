<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_sales' => Sale::sum('amount'),
            'total_products' => Product::count(),
            'low_stock' => Product::where('quantity', '<', 10)->get(),
            'top_products' => Product::withCount('sales')
                ->orderBy('sales_count', 'desc')
                ->take(5)
                ->get(),
        ]);
    }
}
