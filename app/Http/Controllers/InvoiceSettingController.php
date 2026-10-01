<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSetting;
use Illuminate\Http\Request;

class InvoiceSettingController extends Controller
{
    public function edit()
    {
        return view('settings.invoice', ['details' => InvoiceSetting::details()]);
    }

    public function update(Request $request)
    {
        $current = InvoiceSetting::details();
        $rules = [];
        foreach (array_keys($current) as $key) {
            if ($key === 'logo_path') {
                continue;
            }
            $rules[$key] = 'nullable|string|max:255';
        }
        $rules['name'] = 'required|string|max:255';
        $rules['currency'] = 'required|string|size:3|regex:/^[A-Z]{3}$/';
        $rules['email'] = 'nullable|email|max:255';
        $rules['address'] = $rules['terms'] = 'nullable|string|max:2000';
        $rules['logo'] = 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=4000,max_height=4000';
        $rules['remove_logo'] = 'nullable|boolean';
        $details = $request->validate($rules);
        unset($details['logo'], $details['remove_logo']);
        $details['logo_path'] = $request->boolean('remove_logo') ? null : ($current['logo_path'] ?? null);
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('invoice-logos', 'local');
            if (! $path) {
                return back()->withInput()->withErrors(['logo' => 'The logo could not be saved. Please try again.']);
            }
            $details['logo_path'] = $path;
        }
        // Retain previous files because issued invoices reference their original logo.
        InvoiceSetting::updateOrCreate(['id' => 1], ['details' => $details]);

        return back()->with('success', 'Invoice settings saved. New invoices will use these details.');
    }
}
