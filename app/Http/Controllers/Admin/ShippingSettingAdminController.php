<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;

class ShippingSettingAdminController extends Controller
{
    public function edit()
    {
        $settings = ShippingSetting::current();

        return view('admin.shipping-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'free_shipping_amount' => 'required|numeric|min:0',
            'shipping_charge' => 'required|numeric|min:0',
            'base_weight_grams' => 'required|integer|min:1|max:20000',
            'additional_500g_charge' => 'nullable|numeric|min:0',
        ]);

        $settings = ShippingSetting::current();
        $settings->update([
            'free_shipping_amount' => $data['free_shipping_amount'],
            'shipping_charge' => $data['shipping_charge'],
            'base_weight_grams' => (int) $data['base_weight_grams'],
            'additional_500g_charge' => $data['additional_500g_charge'] === null || $data['additional_500g_charge'] === ''
                ? null
                : $data['additional_500g_charge'],
        ]);

        return back()->with('status', __('Shipping settings saved.'));
    }
}
