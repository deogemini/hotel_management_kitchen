<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
    }

    public function test_registration_rejects_each_duplicate_guest_identifier(): void
    {
        $identifiers = [
            'email' => 'guest@example.com',
            'phone_number' => '0712345678',
            'id_number' => 'ID-123456',
        ];
        Guest::create(['full_name' => 'Existing Guest', ...$identifiers]);

        foreach ($identifiers as $field => $value) {
            $this->post(route('guests.store'), ['full_name' => 'New Guest', $field => $value])
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('guests', 1);
    }

    public function test_editing_rejects_each_identifier_owned_by_another_guest(): void
    {
        $identifiers = [
            'email' => 'guest@example.com',
            'phone_number' => '0712345678',
            'id_number' => 'ID-123456',
        ];
        Guest::create(['full_name' => 'Existing Guest', ...$identifiers]);
        $guest = Guest::create(['full_name' => 'Other Guest']);

        foreach ($identifiers as $field => $value) {
            $this->put(route('guests.update', $guest), ['full_name' => 'Other Guest', $field => $value])
                ->assertSessionHasErrors($field);
            $this->assertNull($guest->fresh()->{$field});
        }
    }

    public function test_guest_can_keep_their_own_identifiers_when_edited(): void
    {
        $data = [
            'full_name' => 'Guest',
            'email' => 'guest@example.com',
            'phone_number' => '0712345678',
            'id_number' => 'ID-123456',
        ];
        $guest = Guest::create($data);
        $data['full_name'] = 'Updated Guest';

        $this->put(route('guests.update', $guest), $data)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('guests', ['id' => $guest->id, ...$data]);
    }

    public function test_distinct_identifiers_and_multiple_blank_identifiers_are_accepted(): void
    {
        foreach (range(1, 2) as $number) {
            $this->post(route('guests.store'), [
                'full_name' => 'Guest '.$number,
                'email' => 'guest'.$number.'@example.com',
                'phone_number' => '071234567'.$number,
                'id_number' => 'ID-'.$number,
            ])->assertSessionHasNoErrors();

            $this->post(route('guests.store'), [
                'full_name' => 'Guest without identifiers '.$number,
                'email' => '',
                'phone_number' => '',
                'id_number' => '',
            ])->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('guests', 4);
    }
}
