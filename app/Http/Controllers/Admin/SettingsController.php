<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Ensure this line is present
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Show the current settings
    public function index()
    {
        // Retrieve the current value of 'allow_overselling' setting
        $allowOverselling = Setting::getValue('allow_overselling');
        return view('admin.settings.index', compact('allowOverselling'));
    }

    // Update the settings
    public function update(Request $request)
    {
        // Validate the request input
        $request->validate([
            'allow_overselling' => 'required|boolean',  // Ensuring it's a boolean value
        ]);

        // Store or update the setting in the database
        Setting::updateOrCreate(
            ['key' => 'allow_overselling'],  // Search for this key
            ['value' => $request->input('allow_overselling') ? 'true' : 'false']  // Store 'true' or 'false' as string
        );

        // Redirect back with a success message
        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}
