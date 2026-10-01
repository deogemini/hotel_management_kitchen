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
        $rules = [];
        foreach (array_keys(InvoiceSetting::details()) as $key) {
            $rules[$key] = 'nullable|string|max:255';
        }
        $rules['name'] = 'required|string|max:255';
        $rules['currency'] = 'required|string|size:3|regex:/^[A-Z]{3}$/';
        $rules['email'] = 'nullable|email|max:255';
        $rules['address'] = $rules['terms'] = 'nullable|string|max:2000';
        InvoiceSetting::updateOrCreate(['id' => 1], ['details' => $request->validate($rules)]);

        return back()->with('success', 'Invoice settings saved. New invoices will use these details.');
    }
}
