<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\Room;
use App\Models\WebsiteEnquiry;
use App\Models\WebsiteEntry;
use App\Models\WebsiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PublicWebsiteController extends Controller
{
    private const TIMEZONE = 'Africa/Dar_es_Salaam';

    public function home(): View
    {
        $site = $this->siteContext();
        $roomTypes = $this->roomTypes($site['lodge']?->id);
        $services = WebsiteEntry::where('category', 'service')->where('is_active', true)->orderBy('sort_order')->get();
        $offers = WebsiteEntry::where('category', 'offer')->where('is_active', true)->orderBy('sort_order')->get();
        $gallery = WebsiteEntry::where('category', 'gallery')->where('is_active', true)->orderBy('sort_order')->limit(3)->get();

        return view('website.home', compact('site', 'roomTypes', 'services', 'offers', 'gallery'));
    }

    public function rooms(Request $request): View
    {
        $site = $this->siteContext();
        $dates = $this->validatedDates($request);
        $roomTypes = $this->roomTypes($site['lodge']?->id, $dates);

        return view('website.rooms', compact('site', 'roomTypes', 'dates'));
    }

    public function room(Request $request, string $roomType): View
    {
        $site = $this->siteContext();
        $dates = $this->validatedDates($request);
        $rooms = $this->roomTypes($site['lodge']?->id, $dates);
        $room = $rooms->firstWhere('slug', Str::slug($roomType));
        abort_unless($room, 404);

        return view('website.room', compact('site', 'room', 'dates'));
    }

    public function facilities(): View
    {
        $site = $this->siteContext();
        $services = WebsiteEntry::where('category', 'service')->where('is_active', true)->orderBy('sort_order')->get();

        return view('website.facilities', compact('site', 'services'));
    }

    public function gallery(): View
    {
        $site = $this->siteContext();
        $gallery = WebsiteEntry::where('category', 'gallery')->where('is_active', true)->orderBy('sort_order')->get();
        $roomPhotos = collect();

        if ($site['lodge']) {
            $roomPhotos = Room::where('lodge_id', $site['lodge']->id)
                ->with('images')
                ->get()
                ->flatMap(fn (Room $room) => $room->images->map(fn ($image) => [
                    'title' => $room->room_type,
                    'category' => 'Rooms',
                    'url' => Storage::disk('public')->url($image->image_path),
                    'placeholder' => false,
                ]));
        }

        $galleryPhotos = $gallery->map(fn (WebsiteEntry $entry) => [
            'title' => $entry->title,
            'category' => $entry->image_category,
            'url' => $entry->image_path ? Storage::disk('public')->url($entry->image_path) : null,
            'placeholder' => false,
        ])->filter(fn (array $photo) => $photo['url']);

        return view('website.gallery', ['site' => $site, 'photos' => $roomPhotos->concat($galleryPhotos)]);
    }

    public function about(): View
    {
        $site = $this->siteContext();

        return view('website.about', compact('site'));
    }

    public function contact(): View
    {
        $site = $this->siteContext();

        return view('website.contact', compact('site'));
    }

    public function submitEnquiry(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('enquiry_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $site = $this->siteContext();
        $enquiry = WebsiteEnquiry::create($data + ['delivery_status' => 'saved', 'status' => 'New']);
        $mailDelivered = false;

        try {
            Mail::raw("Website enquiry #{$enquiry->id}\n\nName: {$data['name']}\nEmail: {$data['email']}\nPhone: ".($data['phone'] ?: 'Not provided')."\n\nMessage:\n{$data['message']}", function ($message) use ($site, $data): void {
                $message->to($this->contactEmail($site['settings']))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Website enquiry from '.$data['name']);
            });
            $mailDelivered = true;
        } catch (Throwable $exception) {
            Log::warning('Website enquiry email could not be sent.', ['enquiry_id' => $enquiry->id]);
        }

        $enquiry->update(['delivery_status' => $mailDelivered ? 'sent' : 'saved']);

        return back()->with('enquiry_sent', true);
    }

    public function policies(string $policy): View
    {
        $site = $this->siteContext();
        $policies = [
            'booking' => ['title' => 'Booking policy', 'content' => $site['settings']->booking_policy, 'approved' => $site['settings']->booking_policy_approved],
            'cancellation' => ['title' => 'Cancellation policy', 'content' => $site['settings']->cancellation_policy, 'approved' => $site['settings']->cancellation_policy_approved],
            'payment' => ['title' => 'Payment policy', 'content' => $site['settings']->payment_policy, 'approved' => $site['settings']->payment_policy_approved],
            'privacy' => ['title' => 'Privacy policy', 'content' => $site['settings']->privacy_policy, 'approved' => $site['settings']->privacy_policy_approved],
        ];
        abort_unless(isset($policies[$policy]), 404);
        $activePolicy = $policies[$policy];

        return view('website.policy', compact('site', 'activePolicy'));
    }

    public function sitemap()
    {
        $site = $this->siteContext();
        $urls = [
            route('public.home'), route('public.rooms'), route('public.facilities'), route('public.gallery'),
            route('public.about'), route('public.contact'), route('public.policy', 'booking'),
            route('public.policy', 'cancellation'), route('public.policy', 'payment'), route('public.policy', 'privacy'),
        ];
        foreach ($this->roomTypes($site['lodge']?->id) as $room) {
            $urls[] = route('public.room', $room->slug);
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.e($url).'</loc></url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function booking(Request $request): View
    {
        $site = $this->siteContext();
        $dates = $this->validatedDates($request);
        $roomTypes = $this->roomTypes($site['lodge']?->id, $dates);
        $selectedType = $request->query('room_type');

        return view('website.booking', [
            'site' => $site,
            'roomTypes' => $roomTypes,
            'dates' => $dates,
            'submissionToken' => (string) Str::uuid(),
            'selectedType' => $selectedType,
        ]);
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $today = CarbonImmutable::today(self::TIMEZONE)->toDateString();
        $data = $request->validate([
            'submission_token' => ['required', 'uuid'],
            'room_type' => ['required', 'string', 'max:100'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'number_of_adults' => ['required', 'integer', 'min:1', 'max:20'],
            'number_of_children' => ['required', 'integer', 'min:0', 'max:20'],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        $existingRequest = Booking::where('public_request_token', $data['submission_token'])->first();
        if ($existingRequest) {
            return $this->confirmationRedirect($existingRequest);
        }

        $site = $this->siteContext();
        if (! $site['lodge']) {
            throw ValidationException::withMessages(['room_type' => 'Online booking is not available yet. Please contact the lodge.']);
        }

        $checkIn = CarbonImmutable::createFromFormat('Y-m-d', $data['check_in_date'], self::TIMEZONE);
        $checkOut = CarbonImmutable::createFromFormat('Y-m-d', $data['check_out_date'], self::TIMEZONE);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestCount = (int) $data['number_of_adults'] + (int) $data['number_of_children'];
        $roomDetails = $this->roomTypes($site['lodge']->id)->firstWhere('type', $data['room_type']);
        if (! $roomDetails) {
            throw ValidationException::withMessages(['room_type' => 'That room type is not currently listed. Please choose another option.']);
        }
        if ($roomDetails->capacity && $guestCount > $roomDetails->capacity) {
            throw ValidationException::withMessages(['number_of_children' => 'This room has a maximum guest capacity of '.$roomDetails->capacity.'. Please choose another room type or adjust the guest count.']);
        }

        $created = true;
        try {
            $booking = DB::transaction(function () use ($data, $site, $checkIn, $checkOut, $nights, $guestCount): Booking {
                $inventory = Room::where('lodge_id', $site['lodge']->id)
                    ->where('room_type', $data['room_type'])
                    ->where('status', '!=', 'Maintenance')
                    ->lockForUpdate()
                    ->get();

                if ($inventory->isEmpty()) {
                    throw ValidationException::withMessages(['room_type' => 'That room type is not currently listed. Please choose another option.']);
                }

                $availableCount = $this->availableCount($site['lodge']->id, $data['room_type'], $checkIn, $checkOut, $inventory->pluck('id'));
                if ($availableCount < 1) {
                    throw ValidationException::withMessages(['room_type' => 'We have no remaining availability for those dates. Please choose different dates or contact the lodge.']);
                }

                $rate = (float) $inventory->min('price_per_night');
                $normalizedEmail = Str::lower($data['email']);
                $guest = Guest::where('lodge_id', $site['lodge']->id)
                    ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                    ->first();

                if (! $guest) {
                    $guest = Guest::create([
                        'lodge_id' => $site['lodge']->id,
                        'full_name' => $data['name'],
                        'email' => $data['email'],
                        'phone_number' => $data['phone'],
                        'created_by' => null,
                        'registration_source' => 'website',
                    ]);
                }

                return Booking::create([
                    'booking_number' => $this->bookingReference(),
                    'public_request_token' => $data['submission_token'],
                    'public_confirmation_token' => (string) Str::uuid(),
                    'lodge_id' => $site['lodge']->id,
                    'guest_id' => $guest->id,
                    'room_id' => null,
                    'room_type' => $data['room_type'],
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'number_of_nights' => $nights,
                    'number_of_guests' => $guestCount,
                    'number_of_adults' => (int) $data['number_of_adults'],
                    'number_of_children' => (int) $data['number_of_children'],
                    'special_requests' => $data['special_requests'] ?? null,
                    'status' => 'Pending',
                    'room_rate' => $rate,
                    'room_total' => $rate * $nights,
                    'deposit_amount' => 0,
                    'balance_amount' => $rate * $nights,
                    'created_by' => null,
                ]);
            });
        } catch (QueryException $exception) {
            $booking = Booking::where('public_request_token', $data['submission_token'])->first();
            if (! $booking) {
                throw $exception;
            }
            $created = false;
        }

        if ($created) {
            $this->sendBookingEmails($booking, $site['settings']);
        }

        return $this->confirmationRedirect($booking);
    }

    public function confirmation(string $bookingNumber, string $token): View
    {
        $booking = Booking::with('guest')
            ->where('booking_number', $bookingNumber)
            ->where('public_confirmation_token', $token)
            ->whereNotNull('public_request_token')
            ->firstOrFail();
        $site = $this->siteContext();

        return view('website.confirmation', compact('site', 'booking'));
    }

    private function sendBookingEmails(Booking $booking, WebsiteSetting $settings): void
    {
        $booking->loadMissing('guest');
        $mailer = $settings->smtp_enabled ? $this->bookingSmtpMailer($settings) : Mail::mailer();
        if ($settings->smtp_enabled && $settings->mail_from_address) {
            $mailer->alwaysFrom($settings->mail_from_address, $settings->mail_from_name ?: config('app.name'));
        }
        $body = "Your booking request has been received and is pending lodge confirmation.\n\nReference: {$booking->booking_number}\nRoom type: {$booking->room_type}\nCheck-in: {$booking->check_in_date->format('d M Y')}\nCheck-out: {$booking->check_out_date->format('d M Y')}\nGuests: {$booking->number_of_guests}\nEstimated room total: ".number_format((float) $booking->room_total, 2)." {$settings->currency_code}\n\nNo payment has been taken and this is not a confirmed reservation. The lodge will contact you to confirm availability and payment terms.";

        try {
            $mailer->raw($body, function ($message) use ($booking): void {
                $message->to($booking->guest->email)->subject('Booking request received — '.$booking->booking_number);
            });
            $mailer->raw($body, function ($message) use ($settings, $booking): void {
                $message->to($this->contactEmail($settings))->subject('Pending booking request — '.$booking->booking_number);
            });
        } catch (Throwable $exception) {
            Log::warning('Booking request email could not be sent.', ['booking_id' => $booking->id]);
        }
    }

    private function bookingSmtpMailer(WebsiteSetting $settings)
    {
        return Mail::build([
            'name' => 'website-booking-smtp',
            'transport' => 'smtp',
            'scheme' => $settings->smtp_encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => $settings->smtp_host,
            'port' => $settings->smtp_port,
            'username' => $settings->smtp_username ?: null,
            'password' => $settings->smtp_password ?: null,
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
    }

    private function confirmationRedirect(Booking $booking): RedirectResponse
    {
        return redirect()->route('public.confirmation', [
            'bookingNumber' => $booking->booking_number,
            'token' => $booking->public_confirmation_token,
        ]);
    }

    private function bookingReference(): string
    {
        return 'WEB-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    private function validatedDates(Request $request): ?array
    {
        if (! $request->filled('check_in_date') && ! $request->filled('check_out_date')) {
            return null;
        }

        $today = CarbonImmutable::today(self::TIMEZONE)->toDateString();
        return $request->validate([
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
        ]);
    }

    private function siteContext(): array
    {
        $settings = WebsiteSetting::firstOrCreate(['id' => 1], [
            'contact_email' => config('website.email'),
            'currency_code' => config('website.currency'),
            'hero_title' => config('website.name'),
        ]);

        $lodgeId = $settings->lodge_id ?: config('website.lodge_id');
        $lodge = $lodgeId ? Lodge::find($lodgeId) : null;
        $lodge ??= Lodge::where('name', config('website.name'))->first();
        if (! $lodge && Lodge::count() === 1) {
            $lodge = Lodge::query()->first();
        }
        $location = $settings->address ?: $lodge?->location;
        if ($location && in_array(Str::lower(trim($location)), ['main lodge', 'lodge one'], true)) {
            $location = null;
        }
        $phone = $settings->phone_number ?: $lodge?->phone_number;

        return compact('settings', 'lodge', 'location', 'phone');
    }

    private function contactEmail(WebsiteSetting $settings): string
    {
        return $settings->contact_email ?: config('website.email');
    }

    private function roomTypes(?int $lodgeId, ?array $dates = null)
    {
        if (! $lodgeId) {
            return collect();
        }

        return Room::where('lodge_id', $lodgeId)
            ->where('status', '!=', 'Maintenance')
            ->with('images')
            ->get()
            ->groupBy('room_type')
            ->map(function ($typeRooms, string $type) use ($lodgeId, $dates) {
                $featured = $typeRooms->sortBy('price_per_night')->first();
                $photoRoom = $typeRooms->first(fn (Room $room) => $room->images->isNotEmpty());
                $photo = $photoRoom?->images->firstWhere('is_primary', true) ?? $photoRoom?->images->first();
                $images = $typeRooms->flatMap(fn (Room $room) => $room->images->map(fn ($image) => Storage::disk('public')->url($image->image_path)))->unique()->values();
                $descriptionValues = $typeRooms->pluck('description')->filter(fn ($value) => filled($value));
                $descriptions = $descriptionValues->unique();
                $description = $descriptions->count() === 1 && $descriptionValues->count() === $typeRooms->count() ? $descriptions->first() : null;
                $bedTypeValues = $typeRooms->pluck('public_bed_type')->filter(fn ($value) => filled($value));
                $bedTypes = $bedTypeValues->unique();
                $bedType = $bedTypes->count() === 1 && $bedTypeValues->count() === $typeRooms->count() ? $bedTypes->first() : null;
                $capacityValues = $typeRooms->pluck('public_capacity');
                $definedCapacities = $capacityValues->filter(fn ($value) => $value !== null);
                $capacities = $definedCapacities->unique();
                $taxValues = $typeRooms->pluck('public_tax_included');
                $definedTaxOptions = $taxValues->filter(fn ($value) => $value !== null);
                $taxOptions = $definedTaxOptions->unique();
                $breakfastValues = $typeRooms->pluck('public_breakfast_included');
                $definedBreakfastOptions = $breakfastValues->filter(fn ($value) => $value !== null);
                $breakfastOptions = $definedBreakfastOptions->unique();
                $featureLists = $typeRooms->map(fn (Room $room) => $room->features ?? []);
                $features = collect($featureLists->first() ?? [])
                    ->filter(fn ($feature) => $featureLists->every(fn ($list) => in_array($feature, $list, true)))
                    ->values();
                $available = null;
                if ($dates) {
                    $available = $this->availableCount(
                        $lodgeId,
                        $type,
                        CarbonImmutable::createFromFormat('Y-m-d', $dates['check_in_date'], self::TIMEZONE),
                        CarbonImmutable::createFromFormat('Y-m-d', $dates['check_out_date'], self::TIMEZONE),
                        $typeRooms->pluck('id')
                    );
                }

                return (object) [
                    'type' => $type,
                    'slug' => Str::slug($type),
                    'rate' => (float) $typeRooms->min('price_per_night'),
                    'description' => $description,
                    'bed_type' => $bedType,
                    'capacity' => $capacities->count() === 1 && $definedCapacities->count() === $typeRooms->count() ? $capacities->first() : null,
                    'tax_included' => $taxOptions->count() === 1 && $definedTaxOptions->count() === $typeRooms->count() ? $taxOptions->first() : null,
                    'breakfast_included' => $breakfastOptions->count() === 1 && $definedBreakfastOptions->count() === $typeRooms->count() ? $breakfastOptions->first() : null,
                    'features' => $features,
                    'image_url' => $photo ? Storage::disk('public')->url($photo->image_path) : asset('img/rooms.jpg'),
                    'image_is_placeholder' => ! $photo,
                    'images' => $images,
                    'available' => $available,
                    'room_count' => $typeRooms->count(),
                    'featured_room' => $featured,
                ];
            })
            ->sortBy('rate')
            ->values();
    }

    private function availableCount(int $lodgeId, string $roomType, CarbonImmutable $checkIn, CarbonImmutable $checkOut, $roomIds): int
    {
        $roomIds = collect($roomIds)->values();
        if ($roomIds->isEmpty()) {
            return 0;
        }

        $occupiedIds = Booking::whereIn('room_id', $roomIds)
            ->whereIn('status', ['Pending', 'Confirmed', 'Checked In'])
            ->whereDate('check_in_date', '<', $checkOut->toDateString())
            ->whereDate('check_out_date', '>', $checkIn->toDateString())
            ->distinct()
            ->pluck('room_id');

        $unassignedRequests = Booking::where('lodge_id', $lodgeId)
            ->whereNull('room_id')
            ->where('room_type', $roomType)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->whereDate('check_in_date', '<', $checkOut->toDateString())
            ->whereDate('check_out_date', '>', $checkIn->toDateString())
            ->count();

        return max(0, $roomIds->count() - $occupiedIds->count() - $unassignedRequests);
    }
}
