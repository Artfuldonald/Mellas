<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Show the form for editing application settings.
     */
    public function edit()
    {
        $settingsGrouped = Setting::getAllGrouped();
        return view('admin.settings.edit', compact('settingsGrouped'));
    }

    /**
     * Update the application settings in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.store_name' => 'required|string|max:255',
            'settings.store_email' => 'required|email|max:255',
            'settings.store_phone' => 'nullable|string|max:50',
            'settings.store_address' => 'nullable|string|max:1000',
            'settings.currency_symbol' => 'required|string|max:5',
            'settings.currency_code' => 'required|string|size:3',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            // Get admin ID safely
            $adminId = Auth::check() ? Auth::id() : '[System/Unknown]'; // <-- Use Auth facade
            Log::info('Application settings updated by admin ID: ' . $adminId); // <-- Use variable

            // Optional: Clear config cache if needed
            // Artisan::call('config:clear');

            return redirect()->route('admin.settings.edit')
                             ->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update settings: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.settings.edit')
                             ->with('error', 'Failed to update settings. Please check logs.');
        }
    }
}