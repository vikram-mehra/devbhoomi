<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingAdminController extends Controller
{
    public function edit()
    {
        $commission = Setting::getValue('default_commission_percent', '12');

        return view('admin.settings', compact('commission'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'default_commission_percent' => 'required|numeric|min:0|max:90',
        ]);
        Setting::setValue('default_commission_percent', (string) $request->default_commission_percent);

        return back()->with('status', 'Settings saved.');
    }
}
