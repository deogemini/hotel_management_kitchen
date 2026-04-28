<?php

namespace App\Http\Controllers;

use App\Models\SmsSetting;
use Illuminate\Http\Request;
use App\Services\AuditService;

class SmsSettingController extends Controller
{
    public function index()
    {
        $setting = SmsSetting::firstOrCreate([], [
            'base_url' => 'https://sms.flex.co.tz',
            'sender_id' => 'Flex',
            'is_enabled' => true,
        ]);
        return view('settings.sms', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'sender_id' => 'required|string',
            'is_enabled' => 'boolean',
        ]);

        $setting = SmsSetting::first();
        $original = $setting->getOriginal();
        
        $data = $request->all();
        $data['is_enabled'] = $request->has('is_enabled');

        $setting->update($data);

        $changes = [];
        foreach ($setting->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('sms_setting.update', $setting, $changes);

        return redirect()->back()->with('success', 'SMS Settings updated successfully.');
    }
}
