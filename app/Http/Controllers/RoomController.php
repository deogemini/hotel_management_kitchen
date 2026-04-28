<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomImage;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('images')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->room_type, fn ($query, $type) => $query->where('room_type', $type))
            ->orderBy('room_number')
            ->get();

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create', ['room' => new Room()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['features'] = $request->input('features', []);
        $data['created_by'] = auth()->id();

        $room = Room::create($data);
        $this->storeImages($request, $room);
        AuditService::log('room.create', $room, $room->getAttributes());

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load('images', 'bookings.guest');

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $this->validated($request, $room);
        $data['features'] = $request->input('features', []);
        $original = $room->getOriginal();

        $room->update($data);
        $this->storeImages($request, $room);
        AuditService::log('room.update', $room, ['from' => $original, 'to' => $room->getAttributes()]);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', ['Confirmed', 'Checked In'])->exists()) {
            return back()->withErrors(['room' => 'Room has active bookings and cannot be deleted.']);
        }

        $room->delete();
        AuditService::log('room.delete', $room, ['deleted' => true]);

        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'room_number' => ['required', 'string', 'max:50', 'unique:rooms,room_number,'.($room?->id ?? 'NULL')],
            'room_type' => ['required', 'in:'.implode(',', Room::TYPES)],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', Room::STATUSES)],
            'features' => ['nullable', 'array'],
            'features.*' => ['in:'.implode(',', Room::FEATURES)],
            'description' => ['nullable', 'string'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function storeImages(Request $request, Room $room): void
    {
        foreach ($request->file('images', []) as $image) {
            RoomImage::create([
                'room_id' => $room->id,
                'image_path' => $image->store('room-images', 'public'),
                'is_primary' => ! $room->images()->exists(),
            ]);
        }
    }
}
