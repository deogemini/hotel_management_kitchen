<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlexSmsService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $senderId;
    protected $isEnabled;

    public function __construct()
    {
        $setting = SmsSetting::first();

        if ($setting) {
            $this->baseUrl = $setting->base_url;
            $this->clientId = $setting->client_id;
            $this->clientSecret = $setting->client_secret;
            $this->senderId = $setting->sender_id;
            $this->isEnabled = $setting->is_enabled;
        } else {
            // Fallback to config if no setting in DB
            $this->baseUrl = config('services.flex_sms.base_url');
            $this->clientId = config('services.flex_sms.client_id');
            $this->clientSecret = config('services.flex_sms.client_secret');
            $this->senderId = config('services.flex_sms.sender_id');
            $this->isEnabled = true;
        }
    }

    /**
     * Send SMS via Flex SMS Gateway.
     *
     * @param string $recipient
     * @param string $message
     * @return bool
     */
    public function sendSms(string $recipient, string $message): bool
    {
        if (!$this->isEnabled) {
            Log::info('SMS Sending is disabled in settings.');
            return false;
        }

        if (empty($this->clientId) || empty($this->clientSecret)) {
            Log::error('SMS Client ID or Secret is missing.');
            return false;
        }
        // Clean phone number: remove '+' and ensure it starts with 255
        $recipient = preg_replace('/[^0-9]/', '', $recipient);
        if (str_starts_with($recipient, '0')) {
            $recipient = '255' . substr($recipient, 1);
        } elseif (!str_starts_with($recipient, '255')) {
            $recipient = '255' . $recipient;
        }

        try {
            $response = Http::withHeaders([
                'X-Client-Id' => $this->clientId,
                'X-Client-Secret' => $this->clientSecret,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/sms/send', [
                'senderId' => $this->senderId,
                'recipient' => $recipient,
                'contents' => $message,
                'schedule' => null,
                'schedule_type' => 'once',
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Flex SMS API Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Flex SMS Exception: ' . $e->getMessage());
            return false;
        }
    }
}
