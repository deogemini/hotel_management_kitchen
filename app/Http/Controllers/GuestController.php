<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::withCount('bookings')
            ->when(! $this->canSeeAllLodges(), fn ($query) => $query->where('lodge_id', auth()->user()?->lodge_id))
            ->latest()
            ->get();

        return view('guests.index', compact('guests'));
    }

    public function create()
    {
        return view('guests.create', ['guest' => new Guest()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['lodge_id'] = auth()->user()?->lodge_id;
        $data['created_by'] = auth()->id();
        $guest = Guest::create($data);
        AuditService::log('guest.create', $guest, $guest->getAttributes());

        return redirect()->route('guests.show', $guest)->with('success', 'Guest registered successfully.');
    }

    public function show(Guest $guest)
    {
        $guest->load([
            'bookings.room',
            'restaurantOrders.items.menuItem',
            'otherCharges.booking.room',
            'payments',
        ]);

        return view('guests.show', compact('guest'));
    }

    public function edit(Guest $guest)
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest)
    {
        $original = $guest->getOriginal();
        $guest->update($this->validated($request));
        AuditService::log('guest.update', $guest, ['from' => $original, 'to' => $guest->getAttributes()]);

        return redirect()->route('guests.index')->with('success', 'Guest updated successfully.');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->bookings()->whereIn('status', ['Confirmed', 'Checked In'])->exists()) {
            return back()->withErrors(['guest' => 'Guest has active bookings and cannot be deleted.']);
        }

        $guest->delete();
        AuditService::log('guest.delete', $guest, ['deleted' => true]);

        return redirect()->route('guests.index')->with('success', 'Guest deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'id_type' => ['nullable', 'in:NIDA,Driving Licence,Passport,Others'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'in:Tanzanian,Kenyan,Ugandan'],
        ]);
    }

    private function canSeeAllLodges(): bool
    {
        return auth()->user()?->hasRole('hotel_manager') ?? false;
    }
}
