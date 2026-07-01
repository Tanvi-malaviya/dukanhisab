<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_version' => AppSetting::get('app_version', '1.0.0'),
            'min_required_version' => AppSetting::get('min_required_version', '1.0.0'),
            'force_update' => AppSetting::get('force_update', 'no'),
            'maintenance_mode' => AppSetting::get('maintenance_mode', 'no'),
            'announcement_message' => AppSetting::get('announcement_message', ''),
            'feature_flags' => AppSetting::get('feature_flags', '{"billing_enabled": true, "backup_enabled": true}'),
        ];

        return view('admin.settings.app', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_version' => 'required|string',
            'min_required_version' => 'required|string',
            'force_update' => 'required|in:yes,no',
            'maintenance_mode' => 'required|in:yes,no',
            'announcement_message' => 'nullable|string',
            'feature_flags' => 'required|json',
        ]);

        $keys = ['app_version', 'min_required_version', 'force_update', 'maintenance_mode', 'announcement_message', 'feature_flags'];

        foreach ($keys as $key) {
            AppSetting::set($key, $request->input($key));
        }

        AuditLog::log('Updated platform application settings', $request->only($keys));

        return back()->with('success', 'Application configuration updated successfully.');
    }

    // Public API route to expose config to app clients
    public function getPublicConfig()
    {
        return response()->json([
            'app_version' => AppSetting::get('app_version', '1.0.0'),
            'min_required_version' => AppSetting::get('min_required_version', '1.0.0'),
            'force_update' => AppSetting::get('force_update', 'no') === 'yes',
            'maintenance_mode' => AppSetting::get('maintenance_mode', 'no') === 'yes',
            'announcement_message' => AppSetting::get('announcement_message', ''),
            'feature_flags' => json_decode(AppSetting::get('feature_flags', '{"billing_enabled": true}'), true),
        ]);
    }
}
