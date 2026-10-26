<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = SystemSetting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_name' => ['required', 'string', 'max:150'],
            'max_gps_accuracy' => ['required', 'integer', 'min:10', 'max:200'],
            'late_threshold_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'default_geofence_radius' => ['required', 'integer', 'min:10', 'max:1000'],
        ]);

        foreach ($validated as $key => $val) {
            SystemSetting::set($key, $val);
        }

        AuditLog::log(
            action: 'SYSTEM_SETTINGS_UPDATED',
            entityType: 'SystemSetting',
            entityId: null,
            oldValues: null,
            newValues: $validated,
            reason: 'Administrator memperbarui konfigurasi sistem'
        );

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }
}
