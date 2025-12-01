<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Ensure this line is present
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $allowOverselling = Setting::getValue('allow_overselling');
        return view('admin.settings.index', compact('allowOverselling'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'allow_overselling' => 'required|boolean',
        ]);

        Setting::updateOrCreate(
            ['key' => 'allow_overselling'],
            ['value' => $request->input('allow_overselling') ? 'true' : 'false']
        );

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}