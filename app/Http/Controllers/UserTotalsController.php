<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserTotal; // Make sure this model exists
use Illuminate\Support\Facades\Response;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class UserTotalsController extends Controller
{
    public function index(Request $request)
    {
        // Assuming the User model has a 'subscription_expired' method or attribute
        $isSubscriptionExpired = Auth::user()->subscription_expired; 

        return view('user-totals', compact('isSubscriptionExpired'));
    }

    public function export(Request $request)
    {
        $date = $request->input('date');
        $userTotals = UserTotal::whereDate('created_at', $date)->get(); // Adjust as needed

        $csvFileName = "user_totals_{$date}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['User Name', 'Store Name', 'Total Orders', 'Cash Total', 'POS Total', 'Bank Total', 'Total Amount']);

        foreach ($userTotals as $total) {
            fputcsv($handle, [
                $total->user->name ?? 'N/A',
                $total->store->name ?? 'N/A',
                $total->total_orders,
                number_format($total->totalCash, 2),
                number_format($total->totalPOS, 2),
                number_format($total->totalBank, 2),
                number_format($total->totalCash + $total->totalPOS + $total->totalBank, 2),
            ]);
        }

        fclose($handle);
        return response()->stream(function() use ($handle) {
            fclose($handle);
        }, 200, $headers);
    }
}
