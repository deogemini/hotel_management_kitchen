<?php

namespace App\Http\Controllers;

use App\Models\Lodge;
use App\Models\WebsiteEnquiry;
use App\Models\WebsiteEntry;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WebsiteAdminController extends Controller
{
    public function index(): View
    {
        $settings = $this->settings();
        $entries = WebsiteEntry::orderBy('category')->orderBy('sort_order')->orderBy('title')->get();
        $enquiries = WebsiteEnquiry::latest()->limit(8)->get();
        $newEnquiries = WebsiteEnquiry::where('status', 'New')->count();

        return view('website.admin.index', compact('settings', 'entries', 'enquiries', 'newEnquiries'));
    }

    public function editSettings(): View
    {
        $settings = $this->settings();
        $lodges = Lodge::orderBy('name')->get();
        $hasSmtpPassword = filled($settings->getRawOriginal('smtp_password'));

        return view('website.admin.settings', compact('settings', 'lodges', 'hasSmtpPassword'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $settings = $this->settings();
        $data = $request->validate([
            'lodge_id' => ['nullable', 'exists:lodges,id'],
            'hero_title' => ['nullable', 'string', 'max:180'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_image' => ['nullable', 'image', 'max:6144'],
            'introduction_title' => ['nullable', 'string', 'max:180'],
            'introduction' => ['nullable', 'string', 'max:5000'],
            'contact_email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'map_url' => ['nullable', 'url', 'max:1000'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'check_in_time' => ['nullable', 'string', 'max:50'],
            'check_out_time' => ['nullable', 'string', 'max:50'],
            'currency_code' => ['required', 'string', 'size:3'],
            'guest_policies' => ['nullable', 'string', 'max:4000'],
            'privacy_policy' => ['nullable', 'string', 'max:20000'],
            'booking_policy' => ['nullable', 'string', 'max:20000'],
            'cancellation_policy' => ['nullable', 'string', 'max:20000'],
            'payment_policy' => ['nullable', 'string', 'max:20000'],
            'privacy_policy_approved' => ['nullable', 'boolean'],
            'booking_policy_approved' => ['nullable', 'boolean'],
            'cancellation_policy_approved' => ['nullable', 'boolean'],
            'payment_policy_approved' => ['nullable', 'boolean'],
            'smtp_enabled' => ['required', 'boolean'],
            'smtp_host' => ['nullable', 'required_if:smtp_enabled,1', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'required_if:smtp_enabled,1', 'integer', 'between:1,65535'],
            'smtp_encryption' => ['nullable', 'required_if:smtp_enabled,1', 'in:tls,ssl'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:4096'],
            'clear_smtp_password' => ['nullable', 'boolean'],
            'mail_from_address' => ['nullable', 'required_if:smtp_enabled,1', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $smtpPassword = $data['smtp_password'] ?? null;
        unset($data['smtp_password'], $data['clear_smtp_password']);
        $data['smtp_enabled'] = $request->boolean('smtp_enabled');
        if ($request->boolean('clear_smtp_password')) {
            $data['smtp_password'] = null;
        } elseif (filled($smtpPassword)) {
            $data['smtp_password'] = $smtpPassword;
        }

        if ($request->hasFile('hero_image')) {
            if ($settings->hero_image_path) {
                Storage::disk('public')->delete($settings->hero_image_path);
            }
            $data['hero_image_path'] = $request->file('hero_image')->store('website', 'public');
        }
        unset($data['hero_image']);
        $settings->update($data);

        return redirect()->route('website.admin.index')->with('success', 'Website settings saved.');
    }

    public function createEntry(): View
    {
        return view('website.admin.entry', ['entry' => new WebsiteEntry(), 'categories' => WebsiteEntry::CATEGORIES]);
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $this->validatedEntry($request);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('website', 'public');
        }
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        WebsiteEntry::create($data);

        return redirect()->route('website.admin.index')->with('success', 'Website item added.');
    }

    public function editEntry(WebsiteEntry $entry): View
    {
        return view('website.admin.entry', ['entry' => $entry, 'categories' => WebsiteEntry::CATEGORIES]);
    }

    public function updateEntry(Request $request, WebsiteEntry $entry): RedirectResponse
    {
        $data = $this->validatedEntry($request);
        if ($request->hasFile('image')) {
            if ($entry->image_path) {
                Storage::disk('public')->delete($entry->image_path);
            }
            $data['image_path'] = $request->file('image')->store('website', 'public');
        }
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $entry->update($data);

        return redirect()->route('website.admin.index')->with('success', 'Website item updated.');
    }

    public function destroyEntry(WebsiteEntry $entry): RedirectResponse
    {
        if ($entry->image_path) {
            Storage::disk('public')->delete($entry->image_path);
        }
        $entry->delete();

        return redirect()->route('website.admin.index')->with('success', 'Website item deleted.');
    }

    public function enquiries(): View
    {
        $enquiries = WebsiteEnquiry::latest()->paginate(20);

        return view('website.admin.enquiries', compact('enquiries'));
    }

    public function updateEnquiryStatus(Request $request, WebsiteEnquiry $enquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:New,Reviewed']]);
        $enquiry->update($data);

        return back()->with('success', 'Enquiry status updated.');
    }

    private function settings(): WebsiteSetting
    {
        return WebsiteSetting::firstOrCreate(['id' => 1], [
            'contact_email' => config('website.email'),
            'currency_code' => config('website.currency'),
            'hero_title' => config('website.name'),
        ]);
    }

    private function validatedEntry(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'in:'.implode(',', WebsiteEntry::CATEGORIES)],
            'image_category' => ['required', 'in:Rooms,Exterior,Dining,Facilities'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:6144'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]) + ['sort_order' => (int) $request->input('sort_order', 0)];
    }
}
