<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDailyRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dailyRecord = $this->route('daily_record');
        return $dailyRecord && $dailyRecord->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'cash' => 'required|numeric|min:0',
            'pos'  => 'required|numeric|min:0',
        ];
    }
}