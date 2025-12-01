<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DailyReportExport;
use Carbon\Carbon;

class DailyRecordController extends Controller
{
    /**
     * List daily records (simple page with optional date/store filters).
     */
 public function index(Request $request)
{
    // Filters (all optional)
    $from   = $request->input('from');                   // YYYY-MM-DD
    $to     = $request->input('to');                     // YYYY-MM-DD
    $storeId = $request->input('store_id');              // store id
    $userId  = $request->input('user_id');               // user id
    $hasPurchases = $request->boolean('has_purchases');  // true/false
    $q      = trim((string) $request->input('q'));       // keyword in expense item

    // Base query
    $query = \App\Models\DailyRecord::with(['expenses', 'user', 'store']);

    // Date range
    if ($from) {
        $query->whereDate('date', '>=', $from);
    }
    if ($to) {
        $query->whereDate('date', '<=', $to);
    }

    // Store filter
    if ($storeId) {
        $query->where('store_id', $storeId);
    }

    // User filter
    if ($userId) {
        $query->where('user_id', $userId);
    }

    // Has purchases filter
    if ($hasPurchases) {
        $query->whereHas('expenses');
    }

    // Keyword search in expense items
    if ($q !== '') {
        $query->whereHas('expenses', function($sub) use ($q) {
            $sub->where('item', 'like', '%' . $q . '%');
        });
    }

    $records = $query->latest()->get();

    // Dropdown data
    $stores = \App\Models\Store::orderBy('name')->get(['id','name']);
    $users  = \App\Models\User::orderBy('name')->get(['id','name']);

    // Filtered summary (Cash + POS + Purchases)
    $totalCash = (float) $records->sum('cash');
    $totalPos  = (float) $records->sum('pos');
    $totalPurchases = (float) $records->flatMap->expenses->sum('amount');
    $totalAll = $totalCash + $totalPos + $totalPurchases;

    // Reuse this summary in the Blade (so the top summary always matches filters)
    $summary = [
        'totalCash'     => $totalCash,
        'totalPos'      => $totalPos,
        'totalExpenses' => $totalPurchases,
        'balance'       => $totalAll, // Cash + POS + Purchases
    ];

    return view('daily.index', compact(
        'records', 'stores', 'users',
        'from', 'to', 'storeId', 'userId', 'hasPurchases', 'q',
        'summary'
    ));
}


    /**
     * Store a new daily record with purchases/restock.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'cash' => 'required|numeric|min:0',
            'pos'  => 'required|numeric|min:0',
            'expenses.*.item'   => 'nullable|string',
            'expenses.*.amount' => 'nullable|numeric|min:0',
        ]);

        $record = DailyRecord::create([
            'user_id'  => Auth::id(),
            'store_id' => Auth::user()->store_id ?? null,
            'date'     => $request->date,
            'cash'     => $request->cash,
            'pos'      => $request->pos,
        ]);

        if ($request->has('expenses')) {
            foreach ($request->expenses as $exp) {
                if (!empty($exp['item']) && !empty($exp['amount'])) {
                    $record->expenses()->create([
                        'item'   => $exp['item'],
                        'amount' => $exp['amount'],
                    ]);
                }
            }
        }

        $record->load(['expenses', 'user', 'store']);

        // Global summary (NOT day-filtered).
        // Balance per your rule: Cash + POS + Expenses
        $summary = [
            'totalCash'     => DailyRecord::sum('cash'),
            'totalPos'      => DailyRecord::sum('pos'),
            'totalExpenses' => Expense::sum('amount'),
            'balance'       => (DailyRecord::sum('cash') + DailyRecord::sum('pos')) + Expense::sum('amount'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'record'  => $record,
                'summary' => $summary,
            ]);
        }

        return redirect()->route('daily.index')->with('success', 'Record added successfully!');
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $record = DailyRecord::with('expenses')->findOrFail($id);
        return view('daily.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'cash' => 'required|numeric|min:0',
            'pos'  => 'required|numeric|min:0',
            'expenses.*.item'   => 'nullable|string',
            'expenses.*.amount' => 'nullable|numeric|min:0',
        ]);

        $record = DailyRecord::findOrFail($id);

        $record->update([
            'date' => $request->date,
            'cash' => $request->cash,
            'pos'  => $request->pos,
        ]);

        $record->expenses()->delete();

        if ($request->has('expenses')) {
            foreach ($request->expenses as $exp) {
                if (!empty($exp['item']) && !empty($exp['amount'])) {
                    $record->expenses()->create([
                        'item'   => $exp['item'],
                        'amount' => $exp['amount'],
                    ]);
                }
            }
        }

        return redirect()->route('daily.index')->with('success', 'Record updated successfully!');
    }

    /**
     * Delete record.
     */
    public function destroy($id)
    {
        $record = DailyRecord::findOrFail($id);
        $record->delete();
        return back()->with('success', 'Record deleted successfully.');
    }

    /**
     * Delete a single expense (AJAX).
     */
    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return response()->json(['success' => true, 'message' => 'Expense deleted successfully.']);
    }

    /**
     * Report page: default to "yesterday" (Africa/Lagos). Accepts ?date=YYYY-MM-DD.
     * Per-store Total = Cash + POS. Grand Balance = Cash + POS + Expenses.
     */
    public function report(Request $request)
    {
        $targetDate = $request->input('date') ?: now('Africa/Lagos')->subDay()->toDateString();
        $data = $this->prepareReportData($targetDate);

        return view('daily.report', [
            'reportData' => $data['reportData'],
            'grand'      => $data['grand'],
            'export'     => false,
            'latestDate' => $data['targetDate'], // Carbon instance for Blade
        ]);
    }

    /**
     * Export Excel for selected date (default yesterday).
     */
    public function exportExcel(Request $request)
    {
        $targetDate = $request->input('date') ?: now('Africa/Lagos')->subDay()->toDateString();
        $data = $this->prepareReportData($targetDate);

        return Excel::download(
            new DailyReportExport($data['reportData'], $data['grand']),
            'daily_report_' . $data['targetDate']->format('Ymd') . '.xlsx'
        );
    }

    /**
     * Export PDF for selected date (default yesterday).
     */
    public function exportPdf(Request $request)
    {
        $targetDate = $request->input('date') ?: now('Africa/Lagos')->subDay()->toDateString();
        $data = $this->prepareReportData($targetDate);
        $data['export'] = true;

        $pdf = Pdf::loadView('daily.report', $data)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->download('daily_report_' . $data['targetDate']->format('Ymd') . '.pdf');
    }

    /**
     * Build per-store aggregates for the given date (YYYY-MM-DD).
     * Defaults to "yesterday" Africa/Lagos if null/empty.
     * Per-store Total = Cash + POS. Grand Balance = Cash + POS + Expenses.
     *
     * @return array{reportData:\Illuminate\Support\Collection, grand:array, targetDate:\Carbon\Carbon}
     */
    private function prepareReportData(?string $date = null)
    {
        $targetDate = $date
            ? Carbon::parse($date, 'Africa/Lagos')->startOfDay()
            : now('Africa/Lagos')->subDay()->startOfDay();

        $records = DailyRecord::with(['store', 'user', 'expenses'])
            ->whereDate('date', $targetDate->toDateString())
            ->get();

        $grouped = $records->groupBy(fn($r) => $r->store?->name ?? 'Unassigned');

        $reportData = $grouped->map(function ($storeRecords, $storeName) {
            $cash = (float) $storeRecords->sum('cash');
            $pos  = (float) $storeRecords->sum('pos');
            $expensesSum = (float) $storeRecords->flatMap->expenses->sum('amount');

            return [
                'store'    => $storeName,
                'cash'     => $cash,
                'pos'      => $pos,
                'expenses' => $expensesSum,
                'balance'  => ($cash + $pos + $expensesSum), // per-store total (no minus, no plus expenses)
                'records'  => $storeRecords,
            ];
        });

        $grand = [
            'cash'     => (float) $reportData->sum('cash'),
            'pos'      => (float) $reportData->sum('pos'),
            'expenses' => (float) $reportData->sum('expenses'),
            // Grand Balance = Cash + POS + Expenses (purchases/restock)
            'balance'  => (float) $reportData->sum('cash')
                        + (float) $reportData->sum('pos')
                        + (float) $reportData->sum('expenses'),
        ];

        return compact('reportData', 'grand') + ['targetDate' => $targetDate];
    }
}
