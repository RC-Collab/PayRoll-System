<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        // Default settings
        $defaults = [
            'company_name' => 'Nepal Payroll System',
            'company_email' => '',
            'company_address' => 'Kathmandu, Nepal',
            'company_phone' => '',
            'company_reg_number' => '',
            'fiscal_year_start' => 1,
            'working_days_per_month' => 26,
            'provident_fund_percentage' => 10,
            'tds_percentage' => 1.5,
            'sick_leave_days' => 15,
            'casual_leave_days' => 12,
            'annual_leave_days' => 18,
        ];

        // Load saved settings from database
        $settings = [];
        foreach ($defaults as $key => $default) {
            $setting = SystemSetting::where('key', $key)->first();
            $settings[$key] = $setting ? $setting->value : $default;
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:100',
            'company_email' => 'nullable|email|max:100',
            'company_address' => 'required|string|max:200',
            'company_phone' => 'nullable|string|max:20',
            'company_reg_number' => 'nullable|string|max:50',
            'fiscal_year_start' => 'nullable|integer|in:1,4,7',
            'working_days_per_month' => 'required|integer|min:20|max:31',
            'provident_fund_percentage' => 'required|numeric|min:0|max:100',
            'tds_percentage' => 'required|numeric|min:0|max:100',
            'sick_leave_days' => 'required|integer|min:0|max:365',
            'casual_leave_days' => 'required|integer|min:0|max:365',
            'annual_leave_days' => 'required|integer|min:0|max:365',
        ]);

        // Save each setting to database
        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->getGroup($key),
                    'type' => 'text'
                ]
            );
        }

        // Clear cache if using cache
        \Illuminate\Support\Facades\Cache::forget('system_settings');

        return redirect()->route('settings.index')
            ->with('success', 'Settings saved successfully!');
    }

    private function getGroup($key)
    {
        if (strpos($key, 'company_') === 0) {
            return 'company';
        } elseif (strpos($key, 'fiscal_') === 0) {
            return 'company';
        } elseif (in_array($key, ['working_days_per_month', 'provident_fund_percentage', 'tds_percentage'])) {
            return 'payroll';
        } else {
            return 'leave';
        }
    }
}
