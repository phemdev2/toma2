<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDailyRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date|before_or_equal:today',
            'cash' => 'required|numeric|min:0',
            'pos'  => 'required|numeric|min:0',
            'expenses.*.item'   => 'nullable|string|max:255',
            'expenses.*.amount' => 'nullable|numeric|min:0',
        ];
    }
}