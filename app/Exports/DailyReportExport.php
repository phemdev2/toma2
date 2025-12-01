<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DailyReportExport implements FromView
{
    protected $reportData;
    protected $grand;
    protected $from;
    protected $to;

    public function __construct($reportData, $grand, $from, $to)
    {
        $this->reportData = $reportData;
        $this->grand      = $grand;
        $this->from       = $from;
        $this->to         = $to;
    }

    public function view(): View
    {
        return view('daily.report_export', [
            'reportData' => $this->reportData,
            'grand'      => $this->grand,
            'from'       => $this->from,
            'to'         => $this->to,
            'export'     => true,  // so the view knows it's an export
        ]);
    }
}
