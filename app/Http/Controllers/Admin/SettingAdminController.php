<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingAdminController extends Controller
{
    public function edit()
    {
        $settings = [
            'default_commission_percent' => Setting::getValue('default_commission_percent', '12'),
            'company_name' => Setting::getValue('company_name', config('app.name')),
            'site_logo_url' => Setting::getValue('site_logo_url', ''),
            'company_gst' => Setting::getValue('company_gst', 'GSTIN-NA'),
            'company_phone' => Setting::getValue('company_phone', ''),
            'company_email' => Setting::getValue('company_email', ''),
            'company_address' => Setting::getValue('company_address', ''),
        ];

        return view('admin.settings', $settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'default_commission_percent' => 'required|numeric|min:0|max:90',
            'company_name' => 'nullable|string|max:255',
            'site_logo_url' => 'nullable|url|max:500',
            'company_gst' => 'nullable|string|max:50',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:100',
            'company_address' => 'nullable|string|max:1000',
        ]);

        Setting::setValue('default_commission_percent', (string) $request->default_commission_percent);
        Setting::setValue('company_name', $request->company_name);
        Setting::setValue('site_logo_url', $request->site_logo_url);
        Setting::setValue('company_gst', $request->company_gst);
        Setting::setValue('company_phone', $request->company_phone);
        Setting::setValue('company_email', $request->company_email);
        Setting::setValue('company_address', $request->company_address);

        return back()->with('status', 'Settings saved.');
    }
}
