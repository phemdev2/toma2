<?php

namespace App\Exports;

use App\Models\DailyRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyRecordsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DailyRecord::with(['expenses', 'user', 'store'])->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Cash',
            'POS',
            'Total Expenses',
            'Balance',
            'User',
            'Store',
        ];
    }

    public function map($record): array
    {
        $expensesTotal = $record->expenses->sum('amount');
        $balance = ($record->cash + $record->pos) - $expensesTotal;

        return [
            $record->date,
            $record->cash,
            $record->pos,
            $expensesTotal,
            $balance,
            $record->user->name ?? 'N/A',
            $record->store->name ?? 'N/A',
        ];
    }
}
