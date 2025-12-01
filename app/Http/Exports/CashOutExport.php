<?php

namespace App\Exports;

use App\Models\CashOut;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashOutExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return CashOut::where('user_id', auth()->id())->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'User ID',
            'Amount',
            'Charges',
            'Payment Method',
            'Created At',
        ];
    }
}
