<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MobileScannerController extends Controller
{
    // 1. Render the mobile scanning interface
    public function view($sessionId)
    {
        return view('mobile-scanner', ['sessionId' => $sessionId]);
    }

    // 2. Receive barcode from phone
    public function sendBarcode(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'barcode' => 'required|string'
        ]);

        $key = 'pos_scanner_' . $validated['session_id'];
        
        // Retrieve existing scans or empty array
        $scans = Cache::get($key, []);
        
        // Add new scan with timestamp
        $scans[] = [
            'code' => $validated['barcode'],
            'time' => microtime(true)
        ];
        
        // Store in cache for 2 minutes
        Cache::put($key, $scans, now()->addMinutes(2));

        return response()->json(['status' => 'success']);
    }

    // 3. PC polls this endpoint to get new items
    public function fetchBarcodes(Request $request)
    {
        $sessionId = $request->query('session_id');
        $key = 'pos_scanner_' . $sessionId;
        
        $scans = Cache::get($key, []);
        
        if (!empty($scans)) {
            // Clear cache immediately so the PC doesn't add the item twice
            Cache::forget($key); 
            return response()->json(['scans' => $scans]);
        }

        return response()->json(['scans' => []]);
    }
}